<?php

namespace App;

use ApiGoat\Services\Service;
use ApiGoat\Api\ApiResponse;

class AccountServiceWrapper extends Service
{
    public function __construct($request, $response, $args)
    {
        parent::__construct($request, $response, $args);
    }

    public function getApiResponse()
    {
        $this->body = ['status' => 'failure', 'data' => null, 'errors' => ['Unknown method']];
        $userId = (int)($_SESSION[_AUTH_VAR]->get('id') ?? 0);

        if (!$userId) {
            $this->body = ['status' => 'failure', 'errors' => ['Not authenticated']];
            $ApiResponse = new ApiResponse($this->args, $this->response, $this->body);
            return $ApiResponse->getResponse();
        }

        switch ($this->args['method']) {

            case 'GET':
                $authy = \App\AuthyQuery::create()->findPk($userId);
                if ($authy) {
                    $this->body = [
                        'status' => 'success',
                        'data' => [
                            'email'    => $authy->getEmail(),
                            'fullname' => $authy->getFullname(),
                            'language' => $authy->getLanguage(),
                            'theme'    => method_exists($authy, 'getTheme') ? $authy->getTheme() : 'mint',
                            'google_linked' => method_exists($authy, 'getGoogleSub') ? (bool) $authy->getGoogleSub() : false,
                            'google_email'  => method_exists($authy, 'getGoogleEmail') ? (string) $authy->getGoogleEmail() : '',
                            'location_supported' => method_exists($authy, 'getLocationAddress'),
                            'location_address'   => method_exists($authy, 'getLocationAddress') ? (string) $authy->getLocationAddress() : '',
                            'location_lat'       => method_exists($authy, 'getLocationLat') && $authy->getLocationLat() !== null ? (float) $authy->getLocationLat() : null,
                            'location_lng'       => method_exists($authy, 'getLocationLng') && $authy->getLocationLng() !== null ? (float) $authy->getLocationLng() : null,
                        ]
                    ];
                }
                break;

            case 'PATCH':
            case 'POST':
                $data  = $this->args['data'] ?? [];
                $authy = \App\AuthyQuery::create()->findPk($userId);
                if (!$authy) {
                    $this->body = ['status' => 'failure', 'errors' => ['User not found']];
                    break;
                }

                $errors = [];

                // Update email
                if (isset($data['email']) && $data['email'] !== '') {
                    $email = trim($data['email']);
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = _('Invalid email address');
                    } else {
                        $existing = \App\AuthyQuery::create()
                            ->filterByEmail($email)
                            ->filterByIdAuthy($userId, \Criteria::NOT_EQUAL)
                            ->findOne();
                        if ($existing) {
                            $errors[] = _('Email already in use');
                        } else {
                            $authy->setEmail($email);
                        }
                    }
                }

                // Update language
                if (isset($data['language']) && $data['language'] !== '') {
                    $lang = $data['language'];
                    if (in_array($lang, ['en_US', 'fr_CA'])) {
                        $authy->setLanguage($lang);
                    } else {
                        $errors[] = _('Invalid language selection');
                    }
                }

                // Update theme
                if (isset($data['theme']) && $data['theme'] !== '') {
                    $theme = $data['theme'];
                    // Valid themes come straight from the generated ENUM valueSet,
                    // so themes added to the schema are accepted automatically —
                    // no hardcoded list to drift out of sync.
                    $validThemes = method_exists('\App\AuthyPeer', 'getValueSet')
                        ? \App\AuthyPeer::getValueSet(\App\AuthyPeer::THEME)
                        : ['mint', 'ink', 'indigo', 'terracotta', 'graphite'];
                    if (! in_array($theme, $validThemes, true)) {
                        $errors[] = _('Invalid theme selection');
                    } elseif (method_exists($authy, 'setTheme')) {
                        $authy->setTheme($theme);
                    }
                }

                // Update profile location. Coords come from the client's geocode
                // widget — same trust model as the generated Authy form; an empty
                // address clears the coords with it.
                if (array_key_exists('location_address', $data)) {
                    if (!method_exists($authy, 'setLocationAddress')) {
                        $errors[] = _('Location columns missing — rebuild this project');
                    } else {
                        $address = trim((string) $data['location_address']);
                        $lat = $data['location_lat'] ?? null;
                        $lng = $data['location_lng'] ?? null;
                        $latOk = $lat === null || $lat === '' || (is_numeric($lat) && $lat >= -90 && $lat <= 90);
                        $lngOk = $lng === null || $lng === '' || (is_numeric($lng) && $lng >= -180 && $lng <= 180);
                        if (mb_strlen($address) > 500) {
                            $errors[] = _('Address is too long');
                        } elseif (!$latOk || !$lngOk) {
                            $errors[] = _('Invalid coordinates');
                        } elseif ($address === '') {
                            $authy->setLocationAddress(null);
                            $authy->setLocationLat(null);
                            $authy->setLocationLng(null);
                        } else {
                            $authy->setLocationAddress($address);
                            $authy->setLocationLat(($lat === null || $lat === '') ? null : (float) $lat);
                            $authy->setLocationLng(($lng === null || $lng === '') ? null : (float) $lng);
                        }
                    }
                }

                // Update password (only if current_password provided)
                if (!empty($data['current_password'])) {
                    $currentPassword = $data['current_password'];
                    $newPassword     = $data['new_password'] ?? '';
                    $confirmPassword = $data['confirm_password'] ?? '';

                    if (!password_verify($currentPassword, $authy->getPasswdHash())) {
                        $errors[] = _('Current password is incorrect');
                    } elseif (empty($newPassword)) {
                        $errors[] = _('New password is required');
                    } elseif (strlen($newPassword) < 8) {
                        $errors[] = _('Password must be at least 8 characters');
                    } elseif ($newPassword !== $confirmPassword) {
                        $errors[] = _('Passwords do not match');
                    } else {
                        $authy->setPasswdHash(password_hash($newPassword, PASSWORD_DEFAULT));
                    }
                }

                // Link Google account (credential = GIS ID token)
                if (!empty($data['google_credential'])) {
                    $clientId = (string) ($_ENV['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: '');
                    if (!method_exists($authy, 'setGoogleSub')) {
                        $errors[] = _('Google columns missing — rebuild this project');
                    } else {
                        try {
                            $claims = (new \ApiGoat\Auth\GoogleIdToken())->verify((string) $data['google_credential'], $clientId);
                            $other = \App\AuthyQuery::create()
                                ->filterByGoogleSub($claims['sub'])
                                ->filterByIdAuthy($userId, \Criteria::NOT_EQUAL)
                                ->findOne();
                            if ($other) {
                                $errors[] = _('This Google account is already linked to another user');
                            } else {
                                $authy->setGoogleSub($claims['sub']);
                                $authy->setGoogleEmail($claims['email']);
                            }
                        } catch (\Exception $e) {
                            $errors[] = $e->getMessage();
                        }
                    }
                } elseif (!empty($data['google_unlink']) && method_exists($authy, 'setGoogleSub')) {
                    // Unlink Google account (password login always remains)
                    if ($authy->getGoogleSub() !== null) {
                        $authy->setGoogleSub(null);
                        $authy->setGoogleEmail(null);
                    }
                }

                if (!empty($errors)) {
                    $this->body = ['status' => 'failure', 'errors' => $errors];
                } else {
                    $authy->save();
                    if (isset($data['language'])) {
                        $_SESSION[_AUTH_VAR]->sessVar['Language'] = $authy->getLanguage();
                    }
                    if (isset($data['theme']) && method_exists($authy, 'getTheme')) {
                        $_SESSION[_AUTH_VAR]->sessVar['Theme'] = $authy->getTheme();
                    }
                    $this->body = ['status' => 'success', 'messages' => [_('Account updated successfully')]];
                }
                break;
        }

        $ApiResponse = new ApiResponse($this->args, $this->response, $this->body);
        return $ApiResponse->getResponse();
    }
}
