<?php

namespace App;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

/**
 * Manages Web Push subscriptions and sends push notifications to users.
 * Subscriptions are stored in a JSON file keyed by id_authy.
 */
class PushNotificationService
{
    private static string $storePath = '';

    private static function getStorePath(): string
    {
        if (!self::$storePath) {
            self::$storePath = _BASE_DIR . 'tmp' . DIRECTORY_SEPARATOR . 'push_subscriptions.json';
        }
        return self::$storePath;
    }

    private static function load(): array
    {
        $path = self::getStorePath();
        if (!file_exists($path)) {
            return [];
        }
        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : [];
    }

    private static function save(array $data): void
    {
        file_put_contents(self::getStorePath(), json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Store a push subscription for a user.
     * @param int   $idAuthy      The authenticated user's ID
     * @param array $subscription Push subscription JSON (endpoint, keys.p256dh, keys.auth)
     */
    public static function subscribe(int $idAuthy, array $subscription): bool
    {
        if (empty($subscription['endpoint'])) {
            return false;
        }

        $data    = self::load();
        $key     = (string)$idAuthy;
        $subs    = $data[$key] ?? [];

        // Replace if same endpoint already exists
        foreach ($subs as $i => $existing) {
            if (($existing['endpoint'] ?? '') === $subscription['endpoint']) {
                $subs[$i] = $subscription;
                $data[$key] = $subs;
                self::save($data);
                return true;
            }
        }

        $subs[]     = $subscription;
        $data[$key] = $subs;
        self::save($data);
        return true;
    }

    /**
     * Remove a push subscription by its endpoint URL, scoped to one user's own
     * bucket (review T3 — removing by endpoint across all buckets let any user
     * unsubscribe another user's device).
     */
    public static function unsubscribe(int $idAuthy, string $endpoint): void
    {
        if (!$idAuthy || !$endpoint) return;

        $data = self::load();
        $key  = (string)$idAuthy;
        if (!isset($data[$key])) return;

        $filtered = array_values(array_filter($data[$key], fn($s) => ($s['endpoint'] ?? '') !== $endpoint));
        if (count($filtered) !== count($data[$key])) {
            $data[$key] = $filtered;
            self::save($data);
        }
    }

    /**
     * Send a push notification to all subscriptions for a given user.
     */
    public static function notifyUser(int $idAuthy, string $title, string $body): void
    {
        if (!$idAuthy) return;

        $data = self::load();
        $key  = (string)$idAuthy;
        $subs = $data[$key] ?? [];

        if (empty($subs)) return;

        $vapidSubject    = env('VAPID_SUBJECT')    ?: '';
        $vapidPublicKey  = env('VAPID_PUBLIC_KEY')  ?: '';
        $vapidPrivateKey = env('VAPID_PRIVATE_KEY') ?: '';

        if (!$vapidSubject || !$vapidPublicKey || !$vapidPrivateKey) {
            return;
        }

        // Degrade gracefully if the Web Push library isn't installed yet (it is a
        // composer dependency pulled on `gc build`). Prevents a fatal once VAPID
        // keys are configured but `composer install` hasn't run.
        if (!class_exists(WebPush::class)) {
            error_log('PushNotificationService: minishlink/web-push not installed; skipping push send');
            return;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject'    => $vapidSubject,
                'publicKey'  => $vapidPublicKey,
                'privateKey' => $vapidPrivateKey,
            ],
        ]);

        $payload = json_encode(['title' => $title, 'body' => $body]);

        $staleEndpoints = [];

        foreach ($subs as $sub) {
            if (empty($sub['endpoint'])) continue;

            try {
                $subscription = Subscription::create([
                    'endpoint'        => $sub['endpoint'],
                    'contentEncoding' => $sub['contentEncoding'] ?? 'aesgcm',
                    'keys'            => [
                        'p256dh' => $sub['keys']['p256dh'] ?? '',
                        'auth'   => $sub['keys']['auth']   ?? '',
                    ],
                ]);

                $report = $webPush->sendOneNotification($subscription, $payload);

                if (!$report->isSuccess()) {
                    $statusCode = $report->getResponse() ? $report->getResponse()->getStatusCode() : 0;
                    // 404/410 means the subscription is gone
                    if (in_array($statusCode, [404, 410])) {
                        $staleEndpoints[] = $sub['endpoint'];
                    }
                }
            } catch (\Throwable) {
                // Push is optional — never break the request
            }
        }

        // Clean up expired subscriptions (scoped to this user — review T3)
        foreach ($staleEndpoints as $endpoint) {
            self::unsubscribe($idAuthy, $endpoint);
        }
    }
}
