<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// Configure defaults for the whole application.

// Error reporting. Detailed error output is OFF by default (production-safe);
// set APP_DEBUG=true in a project's .env to turn it on for local development.
// (review T1 — shipping display_error_details=true leaked stack traces + SQL.)
$gcDebug = in_array(strtolower((string) env('APP_DEBUG')), ['1', 'true', 'on', 'yes'], true);
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
ini_set('display_errors', $gcDebug ? '1' : '0');


// Settings
$settings = [
    'displayErrorDetails'    => $gcDebug,

    // App Settings
    'app'                    => [
        'api_version'  => 1,
    ],
    'locale' => [
        'path' =>  '/locale',
        'cache' =>  '/locale/cache',
        'locale' => 'en_US',
        'domain' => 'messages',
        // Should be set to false in production
        'debug' => false,
        'supported_locale' => ['en_US', 'fr_CA'],
    ],
    // CORS is enforced by ApiGoat\Middlewares\CorsMiddleware (hardened, fixed
    // allow-list, credentials always off, no Origin reflection). It does NOT read
    // this block — the only surviving consumer is the runtime ExceptionHandler,
    // which stamps `defaults.origin` as Access-Control-Allow-Origin on error
    // responses. Keep it a wildcard unless you also lock the middleware down.
    // (The old tuupola/cors-middleware config — methods/headers.allow/credentials/
    // cache/error/paths — was removed with that dependency. SECURITY review T2:
    // never pair a non-wildcard origin here with credentials anywhere.)
    'cors' => [
        'defaults' => [
            "origin" => "*",
        ],
    ],
    // jwt settings
    'jwt_middleware'  => [
        'secret' => (function () {
            $s = env('JWT_SECRET');
            if (empty($s)) {
                throw new \RuntimeException(
                    'JWT_SECRET is not set in .env. Generate one '
                    . '(e.g. `openssl rand -hex 32`) and add JWT_SECRET="..." to the project .env.'
                );
            }
            return $s;
        })(),
        'algorithm' => 'HS256',
        // HTTPS-only by default. Local dev over plain HTTP must explicitly
        // override this to false in the project's settings.defaults.php.
        // The deploy script re-asserts true on every deploy.
        'secure' => true,
        'path' => _SUB_DIR_URL . 'api/',
        "ignore" => [
            _SUB_DIR_URL . 'api/v[0-9]/Authy/auth',
            _SUB_DIR_URL . 'api/v[0-9]/Authy/refresh',
            _SUB_DIR_URL . 'api/v[0-9]/oauth/[a-z]+',
            _SUB_DIR_URL . 'api/v[0-9]/mcp',
        ],
        'expire'               => "now +15 minutes",
        'refresh_expire'        => 'now +30 days',
        'refresh_family_expire' => 'now +90 days',
    ],
    // Oauth strategies
    'oauth' => [
        'path' => _SUB_DIR_URL . "oauth/",
        'host' => "https://" . ($_SERVER['SERVER_NAME'] ?? 'localhost'),
        'debug' => $gcDebug,
        'callback_url' => _SITE_URL . 'oauth/callback',
        'callback_transport' => 'post',
        'security_salt' => '',
        'security_iteration ' => '300',
        'security_timeout' => '2 minutes',
        'Strategy' => [
            'Github' => [
                'client_id' => '',
                'client_secret' => '',

            ],
            'Facebook' => [
                'app_id' => '',
                'app_secret' => ''
            ],
        ],
        'auto_register' => false
    ],
    // OAuth 2.1 Authorization Server — signing keypair + encryption key.
    // Keys are minted once by `gc build` (ensureOauthKeys) and stored in .env
    // as \n-escaped single-line values (adhocore/env uses INI_SCANNER_RAW and
    // does NOT interpret escape sequences, so the literal \n pair survives).
    // str_replace un-escapes them here so CryptKey receives real-newline PEM.
    'oauth_server' => [
        'private_key'    => str_replace('\\n', "\n", (string) env('OAUTH_PRIVATE_KEY')),
        'public_key'     => str_replace('\\n', "\n", (string) env('OAUTH_PUBLIC_KEY')),
        'encryption_key' => (string) env('OAUTH_ENCRYPTION_KEY'),
        'issuer'         => defined('_SITE_URL') ? _SITE_URL : '',
    ],
    // API acl config
    'rbac' => [
        // Ignore request bodies when matching api_rbac rules: authorization is
        // (model, action, method); bodies are validated downstream (ACL, field
        // denylists). Without this, every new body shape (a child list's
        // filter+limit, a PATCH's field set) skips the empty-body rules and
        // auto-learns a fail-closed Deny row on prod — mobile child lists
        // arrived as {"filter":"*","limit":"*"} and were denied. Requires
        // empty-body rows for the tuples that only had body-ful Allow rows
        // (Authy/auth Public, Account/list PATCH) — seeded by gc mobile /
        // gc deploy (Cli\Mobile::prodSeedStatements).
        'excludes' => [
            ['method' => '*', 'model' => '*', 'action' => '*'],
        ],
        // Per-request bookkeeping writes. Both default true (unchanged behavior).
        // Set to false on high-traffic deployments to shed a synchronous write per
        // API request:
        //   hit_counter — the api_rbac.count UPDATE (pure metrics; on the private
        //                 path this also skips the row SELECT it loads to bump).
        //   audit_log   — the api_log INSERT (per-call audit trail).
        'hit_counter' => true,
        'audit_log' => true,
    ],
    // Error Handling Middleware settings
    'error_handler_middleware' => [
        // Should be set to false in production
        'display_error_details' => $gcDebug,
        // Parameter is passed to the default ErrorHandler
        // View in rendered output by enabling the "displayErrorDetails" setting.
        // For the console and unit tests we also disable it
        'log_errors' => true,
        // Display error details in error log
        'log_error_details' => true,
    ],
    'assets' => [
        'public_dir' => "",
        'css_dir' => "public/css",
        'js_dir' => "public/js",
        'packages_dir' => "packages",
        'composer_dir' => "",
        'pipeline_dir' => "min"
    ],
    'email' => [
        'host' => '',
        'smptauth' => false,
        'username' => '',
        'password' => '',
        'smtp_secure' => PHPMailer::ENCRYPTION_STARTTLS,
        'port' => '587',
        'smtp_debug' => $gcDebug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF,
        'default_from' => _PROJECT_NAME . '@apigoat.com'
    ],
    'admin_panel' => [
        // top nav icons ['url', 'caption', 'title' ]
        'top_nav' => [
            'profil' => [],
            'support' => [],
            'dashboard' => [],
        ]

    ]
];

$settings['log'] = [
    'facility' => "Variable"
];

// Path settings
$settings['root'] = dirname(__DIR__) . "/..";
$settings['temp'] = $settings['root'] . '/tmp';
$settings['public'] = $settings['root'] . '/public';

return $settings;
