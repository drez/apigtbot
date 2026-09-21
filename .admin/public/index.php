<?php
require '../vendor/autoload.php';
require __DIR__ . '/../config/Built/config.php';
require __DIR__ . '/../config/legacy.php';

// Behind a trusted proxy or an SSR front end that calls this API for the
// visitor, REMOTE_ADDR is the front end's address for EVERY request — which
// silently merges every per-IP limit (registration cap, login lockout, reset
// throttle) into one bucket for the whole site. Restore the real visitor
// address here, before anything reads it. No-op unless TRUSTED_PROXY_IPS
// lists the caller, so the header can never be spoofed past a limit.
\ApiGoat\Http\ClientIp::normalize($_SERVER, (string) env('TRUSTED_PROXY_IPS'));

use DI\ContainerBuilder;
use Slim\App;

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '/../config/container.php');

// Production: compile the container so each request skips reflection-based
// autowiring. Gate matches settings.defaults.php's $gcDebug derivation.
// gc build clears tmp/di-cache, so the compiled class never goes stale.
$gcDebug = in_array(strtolower((string) env('APP_DEBUG')), ['1', 'true', 'on', 'yes'], true);
if (!$gcDebug) {
    $diCache = __DIR__ . '/../tmp/di-cache';
    if (!is_dir($diCache)) {
        @mkdir($diCache, 0775, true);
    }
    if (is_dir($diCache) && is_writable($diCache)) {
        $containerBuilder->enableCompilation($diCache);
        $containerBuilder->writeProxiesToFile(true, __DIR__ . '/../tmp/di-proxies');
    }
}

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Create App instance
$app = $container->get(App::class);

require __DIR__ . '/../config/dependencies.php';

(require __DIR__ . '/../config/routes.php')($app);
(require __DIR__ . '/../config/middlewares.php')($app);

$app->run();
