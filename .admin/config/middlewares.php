<?php

use Slim\App;
use ApiGoat\Handlers\HtmlErrorRenderer;
use ApiGoat\Handlers\JsonErrorRenderer;
use Selective\Config\Configuration;
use ApiGoat\Middlewares\RecallMiddleware;
use ApiGoat\Middlewares\RbacMiddleware;
use ApiGoat\Middlewares\AuthyMiddleware;
use ApiGoat\Middlewares\OAuthResourceMiddleware;
use ApiGoat\Middlewares\RouteParser;
use ApiGoat\Handlers\ExceptionHandler;
use JimTools\JwtAuth\Middleware\JwtAuthentication;
use ApiGoat\Middlewares\CorsMiddleware;
use ApiGoat\Middlewares\SecurityHeadersMiddleware;
use Slim\Interfaces\CallableResolverInterface;

return function (App $app) {
    // Production: cache the compiled FastRoute map (~255 routes) so it is
    // not recompiled every request. gc build clears tmp/route-cache.php.
    $gcDebug = in_array(strtolower((string) env('APP_DEBUG')), ['1', 'true', 'on', 'yes'], true);
    if (!$gcDebug) {
        $routeCache = __DIR__ . '/../tmp/route-cache.php';
        if (is_writable(dirname($routeCache)) || is_writable($routeCache)) {
            $app->getRouteCollector()->setCacheFile($routeCache);
        }
    }

    $container = $app->getContainer();


    // Keep track of current user backend options
    //$app->add(RecallMiddleware::class);

    //$app->add(Logger::class);

    // INNERMOST (first add() = runs last on the way in, first on the way out):
    // convert a HaltResponse that escaped the route handler into a normal
    // response HERE, so CORS, the security headers, server-timing and the
    // session release all still stamp it. The error middleware's HaltResponse
    // branch is a last resort that sits OUTSIDE those middlewares and misses
    // every one of them. See HaltResponseMiddleware.
    $app->add(\ApiGoat\Middlewares\HaltResponseMiddleware::class);

    // Release the file-session lock for Bearer requests so parallel mobile
    // calls don't serialize server-side. See SessionReleaseMiddleware.
    $app->add(\ApiGoat\Middlewares\SessionReleaseMiddleware::class);

    $app->addRoutingMiddleware();
    $app->add(RbacMiddleware::class);
    // Standard authentication
    $app->add(AuthyMiddleware::class);
    // Authentication for the API
    $app->add(JwtAuthentication::class);
    // Bearer token (OAuth2 RS256) → Authy session hydration. MUST be added AFTER
    // JwtAuthentication so that (Slim add() is LIFO) it EXECUTES BEFORE it: it
    // hydrates the Authy session, then JwtAuthentication's ConditionalMiddleware
    // short-circuits on connected==='YES' instead of trying to HS256-decode the
    // RS256 token (which would 401 the request before this middleware ever ran).
    $app->add(OAuthResourceMiddleware::class);
    $app->add(RbacMiddleware::class);
    // Anonymous public-GET response cache (APCu). Registered between the
    // public-pass RbacMiddleware and RouteParser so it EXECUTES right after
    // RouteParser (needs parsed_args) and a HIT never reaches RBAC / JWT /
    // Authy / routing / the session. Inert unless GC_HTTPCACHE_TTL > 0 and
    // config/Built/cache.map.php (from .gc-meta.json "cache") lists the route.
    $app->add(\ApiGoat\Middlewares\PublicResponseCacheMiddleware::class);
    $app->add(RouteParser::class);

    $app->add(CorsMiddleware::class);
    // Baseline security headers (X-Frame-Options, nosniff, referrer policy)
    $app->add(SecurityHeadersMiddleware::class);

    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    $settings = $container->get(Configuration::class)->getArray('error_handler_middleware');
    $displayErrorDetails = (bool) $settings['display_error_details'];
    $logErrors = (bool) $settings['log_errors'];
    $logErrorDetails = (bool) $settings['log_error_details'];


    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);
    $errorMiddleware->setErrorHandler(['IOException','RuntimeException','DirectoryNotFoundException','PropelException', 'ExitStatusException','BuildException','ConfigurationException','Exception','Throwable','HJSON\HJSONException', 'Error'], ExceptionHandler::class);
    // Route JimTools JWT auth failures (AuthorizationException + its subclasses:
    // missing / expired / bad-signature token) to ExceptionHandler so they render as
    // a 401 JSON envelope instead of Slim's default 500. The `true` enables subclass
    // matching — the list above is exact-match only, so a namespaced subclass would
    // otherwise fall through to the default handler.
    $errorMiddleware->setErrorHandler(\JimTools\JwtAuth\Exceptions\AuthorizationException::class, ExceptionHandler::class, true);
    // TRULY outermost — added AFTER addErrorMiddleware so it WRAPS it. A
    // thrown exception (every 401 from JwtAuthentication, every 500) unwinds
    // past any middleware sitting inside the error middleware, so stamping
    // from in there left exactly the responses worth timing with no header
    // at all. See ServerTimingMiddlewareTest.
    $app->add(\ApiGoat\Middlewares\ServerTimingMiddleware::class);

    //$errorHandler = $errorMiddleware->getDefaultErrorHandler();
    //$errorHandler->registerErrorRenderer('text/html', HtmlErrorRenderer::class);
    //$errorHandler->registerErrorRenderer('application/json', JsonErrorRenderer::class);
};
