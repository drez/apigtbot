<?php

namespace App;

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Routes\RouteHelper;

/**
 * Routes definitions
 */

return function (App $app) {
    /*
     * Refresh token — POST a refresh_token → validate + rotate → new JWT + new refresh_token.
     * Exempt from JWT middleware (jwt_middleware.ignore). MUST be before Built/routes.php
     * so the static route is not shadowed by the generated variable Authy/{a} route.
     */
    $app->post(_SUB_DIR_URL . 'api/v' . API_VERSION . '/Authy/refresh', function (Request $request, Response $response, $args) {
        $RouteHelper = new RouteHelper($request, $args);
        $RouteHelper->setArgs('method', 'AUTH');
        $RouteHelper->setArgs('a', 'refresh');
        // Route is named 'api/AuthRefresh', so RouteHelper::getService() would resolve the wrong
        // service name (AuthRefresh). Instantiate AuthyService explicitly.
        $Service = new \App\AuthyService($request, $response, $RouteHelper->getArgs());
        return $Service->getApiResponse();
    })->setName('api/AuthRefresh');

    include 'Built/routes.php';

    // Push notification subscription routes
    $app->post(_SUB_DIR_URL . 'push/subscribe', function (Request $request, Response $response, $args) {
        require_once _BASE_DIR . 'src/App/Services/PushNotificationService.php';
        $result = ['status' => 'error'];

        if (!isset($_SESSION[_AUTH_VAR])) {
            $response->getBody()->write(json_encode(['status' => 'unauthenticated']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $idAuthy = (int)$_SESSION[_AUTH_VAR]->get('id');
        $body    = json_decode($request->getBody()->getContents(), true);

        if ($idAuthy && !empty($body['endpoint'])) {
            PushNotificationService::subscribe($idAuthy, $body);
            $result = ['status' => 'ok'];
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    })->setName('push/subscribe');

    $app->post(_SUB_DIR_URL . 'push/unsubscribe', function (Request $request, Response $response, $args) {
        require_once _BASE_DIR . 'src/App/Services/PushNotificationService.php';

        // SECURITY (review T3): legacy.php always instantiates AuthySession, so
        // an isset() guard is dead — check a real user id. And scope the removal
        // to the caller's own subscriptions (unsubscribe by endpoint alone let
        // any user drop another user's subscription — IDOR).
        $idAuthy = (int)$_SESSION[_AUTH_VAR]->get('id');
        if (!$idAuthy) {
            $response->getBody()->write(json_encode(['status' => 'unauthenticated']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $body     = json_decode($request->getBody()->getContents(), true);
        $endpoint = $body['endpoint'] ?? '';

        if ($endpoint) {
            PushNotificationService::unsubscribe($idAuthy, $endpoint);
        }

        $response->getBody()->write(json_encode(['status' => 'ok']));
        return $response->withHeader('Content-Type', 'application/json');
    })->setName('push/unsubscribe');

    // PWA routes
    $app->get(_SUB_DIR_URL . 'sw.js', function (Request $request, Response $response, $args) {
        $swPath = _BASE_DIR . 'sw.js';
        if (file_exists($swPath)) {
            $pwaPath = _BASE_DIR . 'public/js/pwa.js';
            $version = file_exists($pwaPath) ? filemtime($pwaPath) : filemtime($swPath);
            $content = '// v' . $version . "\n" . file_get_contents($swPath);
            $response->getBody()->write($content);
            return $response->withHeader('Content-Type', 'application/javascript')
                            ->withHeader('Cache-Control', 'no-store');
        }
        return $response->withStatus(404);
    })->setName('ServiceWorker');

    $app->get(_SUB_DIR_URL . 'manifest.webmanifest', function (Request $request, Response $response, $args) {
        $manifestPath = _BASE_DIR . 'manifest.webmanifest';
        if (file_exists($manifestPath)) {
            $raw = file_get_contents($manifestPath);
            // The manifest file is template-canonical (hardcoded "ApiGoat
            // Admin"). Override name/short_name per-project so a desktop /
            // home-screen shortcut is labelled with the project name. Same
            // fallback chain as BuilderLayout's pwaTitle; empty = leave the
            // manifest's own name untouched.
            $pwaName = '';
            if (defined('_SITE_TITLE') && _SITE_TITLE !== '') {
                $pwaName = (string) _SITE_TITLE;
            } elseif (defined('_PROJECT_NAME') && _PROJECT_NAME !== '') {
                $pwaName = ucfirst((string) _PROJECT_NAME);
            }
            $manifest = json_decode($raw, true);
            if ($pwaName !== '' && is_array($manifest)) {
                $manifest['name'] = $pwaName;
                $manifest['short_name'] = $pwaName;
                $raw = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
            $response->getBody()->write($raw);
            return $response->withHeader('Content-Type', 'application/manifest+json');
        }
        return $response->withStatus(404);
    })->setName('Manifest');

    // Account page (HTML) — renders public/view/account.php inside the app layout.
    $app->get(_SUB_DIR_URL . 'Account', function (Request $request, Response $response, $args) {
        $RouteHelper = new RouteHelper($request, $args);
        $Layout = new \ApiGoat\Utility\BuilderLayout(new \ApiGoat\Utility\BuilderMenus($RouteHelper->getArgs()));

        ob_start();
        include _BASE_DIR . 'public/view/account.php';
        $accountHtml = ob_get_clean();

        $response->getBody()->write($Layout->render(['html' => swheader() . $accountHtml]));
        return $response;
    })->setName('Account');

    // Account update API — GET account info; PATCH/POST email/language/password.
    $app->map(['GET', 'PATCH', 'POST'], _SUB_DIR_URL . 'api/v' . API_VERSION . '/Account[/{a}[/{params:.*}]]', function (Request $request, Response $response, $args) {
        $RouteHelper = new RouteHelper($request, $args);
        require_once _BASE_DIR . 'src/App/Services/AccountServiceWrapper.php';
        $Service = new \App\AccountServiceWrapper($request, $response, $RouteHelper->getArgs());
        return $Service->getApiResponse();
    })->setName('api/Account');

    // Push notifications API — POST register {token, platform}; POST /test self-send.
    $app->map(['POST'], _SUB_DIR_URL . 'api/v' . API_VERSION . '/Push[/{a}]', function (Request $request, Response $response, $args) {
        $RouteHelper = new RouteHelper($request, $args);
        $Service = new \ApiGoat\Services\PushService($request, $response, $RouteHelper->getArgs());
        return $Service->getApiResponse();
    })->setName('api/Push');

    // Geocode proxy (location input widget) — Nominatim passthrough, authenticated,
    // read-only. GET /ApiGoat/geocode?q=&country=&limit= and
    // GET /ApiGoat/reverseGeocode?lat=&lng=. Session route for the browser widget
    // (_SITE_URL + "ApiGoat/geocode"); the api/v1 mount below serves bearer
    // clients (mobile). AuthyMiddleware exempts these two actions from the
    // model-RBAC matrix (ApiGoat isn't a model) but still requires auth, and
    // RbacMiddleware skips api_rbac body-matching for them (free-text q would
    // mint fail-closed Deny rules on prod) — GeoService re-checks the session.
    $app->get(_SUB_DIR_URL . 'ApiGoat/{a:geocode|reverseGeocode}', function (Request $request, Response $response, $args) {
        $RouteHelper = new RouteHelper($request, $args);
        $Service = new \ApiGoat\Services\GeoService($request, $response, $RouteHelper->getArgs());
        return $Service->getApiResponse();
    })->setName('ApiGoat/geocode');

    $app->get(_SUB_DIR_URL . 'api/v' . API_VERSION . '/ApiGoat/{a:geocode|reverseGeocode}', function (Request $request, Response $response, $args) {
        $RouteHelper = new RouteHelper($request, $args);
        $Service = new \ApiGoat\Services\GeoService($request, $response, $RouteHelper->getArgs());
        return $Service->getApiResponse();
    })->setName('api/ApiGoat/geocode');

    // Dashboard "Restart trading" button — clears the kill switch on a run
    // (same action as the GridRun edit checkbox, one click from the alert).
    // Auth: AuthyMiddleware infers right 'w' for POSTs; "Dashboard" is not an
    // RBAC model so only admins pass — plus an explicit GridRun-write check
    // here (mirrors the MCP tools' requiredRight). CSRF is enforced by
    // AuthyMiddleware::checkCsrf; the client fetch patch attaches the token.
    $app->post(_SUB_DIR_URL . 'Dashboard/restart', function (Request $request, Response $response, $args) {
        $json = static function (array $out, int $status) use ($response) {
            $response->getBody()->write(json_encode($out));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
        };
        $session = $_SESSION[_AUTH_VAR] ?? null;
        if (!$session || $session->get('connected') !== 'YES') {
            return $json(['status' => 'unauthenticated'], 401);
        }
        if (!$session->isAdmin() && $session->hasRights('GridRun', 'w') === false) {
            return $json(['status' => 'forbidden'], 403);
        }
        $body = json_decode((string) $request->getBody(), true) ?: [];
        $run = \App\GridRunQuery::create()->findPk((int) ($body['run'] ?? 0));
        if (!$run) {
            return $json(['status' => 'unknown_run'], 404);
        }
        $run->setKillSwitch(false);
        $run->save();
        // no bot_event write here: web PHP runs in the user's session timezone,
        // which skews the stamp — the daemon logs 'restart' when it notices the
        // cleared switch (single clock for the whole event stream)
        return $json(['status' => 'ok'], 200);
    })->setName('Dashboard/restart');

    // Per-run daemon controls from the dashboard: start (Resume), stop
    // (Pause — working orders stay on the exchange), reload (daemon exits
    // cleanly; systemd or the watchdog supervisor relaunches it on fresh
    // code). All three just enqueue a bot_command row the daemon consumes
    // next tick — same auth/CSRF posture as Dashboard/restart above.
    $app->post(_SUB_DIR_URL . 'Dashboard/command', function (Request $request, Response $response, $args) {
        $json = static function (array $out, int $status) use ($response) {
            $response->getBody()->write(json_encode($out));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
        };
        $session = $_SESSION[_AUTH_VAR] ?? null;
        if (!$session || $session->get('connected') !== 'YES') {
            return $json(['status' => 'unauthenticated'], 401);
        }
        if (!$session->isAdmin() && $session->hasRights('GridRun', 'w') === false) {
            return $json(['status' => 'forbidden'], 403);
        }
        $body = json_decode((string) $request->getBody(), true) ?: [];
        $map = ['start' => 'Resume', 'stop' => 'Pause', 'reload' => 'Reload'];
        $action = (string) ($body['action'] ?? '');
        if (!isset($map[$action])) {
            return $json(['status' => 'unknown_action'], 400);
        }
        $run = \App\GridRunQuery::create()->findPk((int) ($body['run'] ?? 0));
        if (!$run) {
            return $json(['status' => 'unknown_run'], 404);
        }
        $cmd = new \App\BotCommand();
        $cmd->setIdGridRun((int) $run->getIdGridRun());
        $cmd->setCommand($map[$action]);
        $cmd->setCmdStatus('Pending');
        $cmd->setNote('dashboard');
        $cmd->save();
        return $json(['status' => 'ok', 'command' => $map[$action]], 200);
    })->setName('Dashboard/command');

    // Hold/Release funds: park a run in Halted (its slice leaves the shared
    // pool) or bring a held run back to Live (BudgetGuard re-checked — the
    // freed slice may have been taken meanwhile). Logic lives in
    // App\Domains\Bot\FundsHold; same auth/CSRF posture as Dashboard/command.
    $app->post(_SUB_DIR_URL . 'Dashboard/funds', function (Request $request, Response $response, $args) {
        $json = static function (array $out, int $status) use ($response) {
            $response->getBody()->write(json_encode($out));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
        };
        $session = $_SESSION[_AUTH_VAR] ?? null;
        if (!$session || $session->get('connected') !== 'YES') {
            return $json(['status' => 'unauthenticated'], 401);
        }
        if (!$session->isAdmin() && $session->hasRights('GridRun', 'w') === false) {
            return $json(['status' => 'forbidden'], 403);
        }
        $body = json_decode((string) $request->getBody(), true) ?: [];
        $action = (string) ($body['action'] ?? '');
        if (!in_array($action, ['hold', 'release'], true)) {
            return $json(['status' => 'unknown_action'], 400);
        }
        $run = \App\GridRunQuery::create()->findPk((int) ($body['run'] ?? 0));
        if (!$run) {
            return $json(['status' => 'unknown_run'], 404);
        }
        $res = $action === 'hold'
            ? \App\Domains\Bot\FundsHold::hold($run)
            : \App\Domains\Bot\FundsHold::release($run);
        if (!$res['ok']) {
            return $json(['status' => 'refused', 'message' => $res['message']], 409);
        }
        return $json(['status' => 'ok', 'message' => $res['message']], 200);
    })->setName('Dashboard/funds');

    // Edit the shared budget from the dashboard Budget tile: writes
    // gtbot_shared_budget_quote and Reloads every active run (paper wallet
    // re-seeds at boot). Slices resize at the next refit, not here. Logic in
    // App\Domains\Bot\BudgetPool; same auth/CSRF posture as Dashboard/funds.
    $app->post(_SUB_DIR_URL . 'Dashboard/budget', function (Request $request, Response $response, $args) {
        $json = static function (array $out, int $status) use ($response) {
            $response->getBody()->write(json_encode($out));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
        };
        $session = $_SESSION[_AUTH_VAR] ?? null;
        if (!$session || $session->get('connected') !== 'YES') {
            return $json(['status' => 'unauthenticated'], 401);
        }
        if (!$session->isAdmin() && $session->hasRights('GridRun', 'w') === false) {
            return $json(['status' => 'forbidden'], 403);
        }
        if (\App\Domains\Bot\BudgetPool::useAllFunds()) {
            return $json(['status' => 'refused', 'message' => 'the budget follows the wallet while gtbot_use_all_funds is on — turn it off in Settings › Config first'], 409);
        }
        $body = json_decode((string) $request->getBody(), true) ?: [];
        $res = \App\Domains\Bot\BudgetPool::set((string) ($body['budget'] ?? ''));
        if (!$res['ok']) {
            return $json(['status' => 'refused', 'message' => $res['message']], 400);
        }
        return $json(['status' => 'ok'] + $res, 200);
    })->setName('Dashboard/budget');

    // Global simulated/real switch — the system trades ONE shared wallet, so
    // the mode flips for ALL non-Done runs at once (mixed modes would
    // double-commit capital). Same auth/CSRF posture as Dashboard/command.
    $app->post(_SUB_DIR_URL . 'Dashboard/mode', function (Request $request, Response $response, $args) {
        $json = static function (array $out, int $status) use ($response) {
            $response->getBody()->write(json_encode($out));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
        };
        $session = $_SESSION[_AUTH_VAR] ?? null;
        if (!$session || $session->get('connected') !== 'YES') {
            return $json(['status' => 'unauthenticated'], 401);
        }
        if (!$session->isAdmin() && $session->hasRights('GridRun', 'w') === false) {
            return $json(['status' => 'forbidden'], 403);
        }
        $body = json_decode((string) $request->getBody(), true) ?: [];
        $mode = (string) ($body['mode'] ?? '');
        if (!in_array($mode, ['simulated', 'real'], true)) {
            return $json(['status' => 'unknown_mode'], 400);
        }
        $res = \App\Domains\Dashboard\ModeSwitch::flipAll($mode === 'simulated');
        return $json(['status' => 'ok'] + $res, 200);
    })->setName('Dashboard/mode');

    // Dashboard trading chart feed: candles for the run's symbol + timeframe
    // (CandleStore, collector-fed — no exchange call) with the run's fills,
    // working ladder, grid geometry, trend lines and lifecycle markers.
    // Read-only GET: session + GridRun read right, no CSRF (AuthyMiddleware
    // only enforces it on writes). no-store: the newest bar changes every
    // minute and the client polls.
    $app->get(_SUB_DIR_URL . 'Dashboard/chart', function (Request $request, Response $response, $args) {
        $json = static function (array $out, int $status) use ($response) {
            $response->getBody()->write(json_encode($out));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Cache-Control', 'no-store')
                ->withStatus($status);
        };
        $session = $_SESSION[_AUTH_VAR] ?? null;
        if (!$session || $session->get('connected') !== 'YES') {
            return $json(['status' => 'unauthenticated'], 401);
        }
        if (!$session->isAdmin() && $session->hasRights('GridRun', 'r') === false) {
            return $json(['status' => 'forbidden'], 403);
        }
        $q = $request->getQueryParams();
        $tf = (string) ($q['tf'] ?? '1h');
        if (!in_array($tf, \App\Domains\Dashboard\DashboardData::CHART_TFS, true)) {
            return $json(['status' => 'unknown_tf', 'allowed' => \App\Domains\Dashboard\DashboardData::CHART_TFS], 400);
        }
        $run = \App\GridRunQuery::create()->findPk((int) ($q['run'] ?? 0));
        if (!$run) {
            return $json(['status' => 'unknown_run'], 404);
        }
        return $json(['status' => 'ok'] + (new \App\Domains\Dashboard\DashboardData($run))->chartModel($tf), 200);
    })->setName('Dashboard/chart');

    /* declare new routes here */
    /*$app->map(['POST'], _SUB_DIR_URL . 'RefreshStats', function (Request $request, Response $response, array $args) {
        $RouteHelper = new RouteHelper($request, $args);
        $Service = new StatsService($request, $response, $RouteHelper->getArgs());
        return $Service->getApiResponse();
    })->setName('RefreshStats');*/
};
