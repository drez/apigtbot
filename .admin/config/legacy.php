<?php

use ApiGoat\Sessions\AuthySession;

ini_set('default_charset', 'utf-8');
//require __DIR__ . '/Built/config.php';
set_include_path(_BASE_DIR . "/src/" . PATH_SEPARATOR . get_include_path());
require __DIR__ . '/Built/propel.php';
require _VENDOR_DIR . 'apigoat/runtime/src/Utility/Legacy/html_helper.php';
require _VENDOR_DIR . 'apigoat/runtime/src//Utility/Legacy/std_function.php';

if (session_status() === PHP_SESSION_NONE) {
    // Session boot (name, hardened cookie attrs, lifetime, save path) lives in
    // the shared runtime so the policy propagates via composer, not drift-sync.
    // Knob: GC_SESSION_GUI_DAYS in the project .env (default 30, max 90 days).
    if (class_exists(\ApiGoat\Auth\SessionLifetime::class)) {
        \ApiGoat\Auth\SessionLifetime::startGuiSession();
    } else {
        // Pre-knob runtime fallback: session-scoped cookie, same hardened attrs.
        session_name('ApiGoat');
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            # Mark the cookie secure whenever the request came in over TLS
            # (directly or via a reverse proxy).
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
        ]);
        session_start();
    }
}

if (!isset($_SESSION[_AUTH_VAR]) || !is_object($_SESSION[_AUTH_VAR]) || (get_class($_SESSION[_AUTH_VAR]) != 'ApiGoat\Sessions\AuthySession')) {
    unset($_SESSION[_AUTH_VAR]);
    $_SESSION[_AUTH_VAR] = new AuthySession();
}

if (!empty($_SESSION[_AUTH_VAR]->sessVar['Timezone'])) {
    date_default_timezone_set($_SESSION[_AUTH_VAR]->sessVar['Timezone']);
}

require _BASE_DIR . 'config/Built/config.db.php';

setlocale(LC_NUMERIC, 'en_CA');
setlocale(LC_ALL, "en_US");
putenv('LC_ALL=en_US');
define('_LOCAL_LC', 'en_US');
bindtextdomain("messages", _BASE_DIR . "locale");
textdomain("messages");

// Register this project's translation dictionary (merged over the apigoat/runtime base).
if (class_exists(\ApiGoat\I18n\Translator::class)) {
    \ApiGoat\I18n\Translator::registerDictionaryPath(__DIR__ . '/../src/App/Domains/I18n/lang');
}
