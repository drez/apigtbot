<?php

namespace App;


/**
 * Backend default routes
 */

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Utility\BuilderLayout;
use ApiGoat\Utility\BuilderMenus;
use ApiGoat\Services\GuiManager;
use ApiGoat\Routes\RouteHelper;

use ApiGoat\Services\EmailService;
use ApiGoat\Services\AccountService;
use ApiGoat\Services\OauthService;

const API_VERSION = '1';
$builderRoutes = require _BASE_DIR . '/config/Built/settings.routes.php';

# Home route
$app->get(_SUB_DIR_URL . '[admin]', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);
    if (file_exists(_BASE_DIR . 'public/view/welcome.html')) {
        $Layout = new BuilderLayout(new BuilderMenus($RouteHelper->getArgs()));
        $response->getBody()->write($Layout->render(['html' => swheader() . file_get_contents(_BASE_DIR . 'public/view/welcome.html')]));
    } else {
        $Service = new \ApiGoat\Services\WelcomeService($request, $response, $RouteHelper->getArgs());
        $response->getBody()->write($Service->getResponse());
        return $response;
    }

    return $response;
})->setName('Home');

# Keep track of users history
$app->post(_SUB_DIR_URL . 'GuiManager', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);
    $Service = new GuiManager($request, $response, $RouteHelper->getArgs());
    return $Service->getApiResponse();
})->setName('GuiMgr');

# Realtime handshake ticket: mint a short-lived HMAC ticket the browser hands to
# the OpenSwoole sidecar when opening its WebSocket. A WS handshake cannot carry
# an Authorization header, and the sidecar must never read session files, so the
# app — which already knows the caller — signs identity into a 30s ticket instead.
# The ticket grants NO read access: the sidecar pushes table names, never rows.
$app->get(_SUB_DIR_URL . 'rt/ticket', function (Request $request, Response $response, $args) {
    $response = $response->withHeader('Content-Type', 'application/json');

    if (!\ApiGoat\Realtime\Signal::enabled()) {
        $response->getBody()->write(json_encode(['enabled' => false]));
        return $response;
    }

    // Same guard the push/subscribe routes use: a real user id, not just an
    // instantiated session object (legacy.php always creates one).
    $idAuthy = isset($_SESSION[_AUTH_VAR]) && is_object($_SESSION[_AUTH_VAR])
        ? (int) $_SESSION[_AUTH_VAR]->get('id') : 0;
    if (!$idAuthy || $_SESSION[_AUTH_VAR]->get('connected') !== 'YES') {
        $response->getBody()->write(json_encode(['status' => 'unauthenticated']));
        return $response->withStatus(401);
    }

    $ticket = \ApiGoat\Realtime\Ticket::mint();
    if ($ticket === '') {
        $response->getBody()->write(json_encode(['enabled' => false]));
        return $response;
    }

    $response->getBody()->write(json_encode([
        'enabled' => true,
        'ticket'  => $ticket,
        // Path of the Apache ws-tunnel. Sits under the app's own prefix so one
        // vhost rule per project is all that is needed; ProxyPass in the vhost
        // wins over the .htaccess rewrite, so it never reaches the front controller.
        'path'    => (string) (env('GC_RT_WS_PATH') ?: _SUB_DIR_URL . 'rt/ws'),
        'ttl'     => \ApiGoat\Realtime\Ticket::TTL,
    ]));
    return $response;
})->setName('rt/ticket');

# Foreach defined builder routes
# Add post and get
foreach ($builderRoutes['html']['GET'] as $route => $params) {
    $app->get(_SUB_DIR_URL . $route . '[/{a}[/{params:.*}]]', function (Request $request, Response $response, $args) {
        try {
        $RouteHelper = new RouteHelper($request, $args);
        $Service = $RouteHelper->getService($response);
        $response->getBody()->write($Service->getResponse());

        if ($Service->request['a'] == 'file' || $Service->request['a'] == 'open') {
            // File could not be resolved (missing record/path, not on disk, or
            // unreadable): getFileContent() returns '' and leaves contentType null.
            // Passing null to withHeader() throws (PSR-7 RFC 7230) and 500s the page.
            // Respond 404 instead of emitting an invalid header.
            if (empty($Service->contentType)) {
                return $response->withStatus(404);
            }
            // Video/audio: getFileContent() left the body empty on purpose — the
            // bytes are streamed from the confined $Service->filePath here instead
            // of transiting PHP memory, and 'open' gains HTTP Range support
            // (Safari/iOS refuse to play media without 206 partial responses;
            // seeking needs them in every browser).
            $gcMedia = !empty($Service->filePath)
                && preg_match('#^(video|audio)/#', $Service->contentType);
            // Uploaded bytes are user content: never let a browser second-guess
            // the declared type (SecurityHeadersMiddleware sets this too, but the
            // middleware list is per-project and not drift-synced).
            $response = $response->withHeader('X-Content-Type-Options', 'nosniff');
            if ($Service->request['a'] == 'file') {
                $response = $response->withHeader('Content-Type', 'application/force-download')
                    ->withHeader('Content-Description', 'File Transfer')
                    ->withHeader('Content-Transfer-Encoding', 'binary')
                    ->withHeader('Content-Disposition', 'attachment; filename="' . $Service->Name . '"')
                    ->withHeader('Expires', '0')
                    ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                    ->withHeader('Content-Length', $Service->length);
                if ($gcMedia) {
                    $gcIn = fopen($Service->filePath, 'rb');
                    if ($gcIn === false) { return $response->withStatus(404); }
                    $response = $response->withBody(new \Slim\Psr7\Stream($gcIn));
                }
                return $response;
            }
            $response = $response->withHeader('Content-Type', $Service->contentType);
            if (!$gcMedia) {
                // getFileContent() maps every type outside its inline-safe
                // allowlist (html, svg, xml, office files, …) to
                // application/octet-stream: 'open' then agrees with 'file' and
                // downloads it instead of rendering it on the app origin.
                if ($Service->contentType === 'application/octet-stream') {
                    $response = $response->withHeader('Content-Disposition', 'attachment; filename="'
                        . str_replace(['"', '\\', "\r", "\n"], '', (string) $Service->Name) . '"');
                }
                return $response; // images/PDF/plain text: legacy in-memory body
            }
            $gcSize  = (int) $Service->length;
            $gcStart = 0;
            $gcEnd   = $gcSize - 1;
            $gcRange = trim($request->getHeaderLine('Range'));
            if (preg_match('/^bytes=(\d*)-(\d*)$/', $gcRange, $gcR) && ($gcR[1] !== '' || $gcR[2] !== '')) {
                if ($gcR[1] === '') {
                    $gcStart = max(0, $gcSize - (int) $gcR[2]); // suffix: last N bytes
                } else {
                    $gcStart = (int) $gcR[1];
                    if ($gcR[2] !== '') { $gcEnd = min((int) $gcR[2], $gcEnd); }
                }
                if ($gcSize === 0 || $gcStart > $gcEnd) {
                    return $response->withStatus(416)
                        ->withHeader('Content-Range', 'bytes */' . $gcSize);
                }
                $response = $response->withStatus(206)
                    ->withHeader('Content-Range', 'bytes ' . $gcStart . '-' . $gcEnd . '/' . $gcSize);
            }
            $response = $response->withHeader('Accept-Ranges', 'bytes');
            $gcIn = fopen($Service->filePath, 'rb');
            if ($gcIn === false) { return $response->withStatus(404); }
            if ($gcStart === 0 && $gcEnd === $gcSize - 1) {
                // Full body: hand the file handle to PSR-7 directly (reads to EOF).
                return $response->withBody(new \Slim\Psr7\Stream($gcIn))
                    ->withHeader('Content-Length', (string) $gcSize);
            }
            // Partial body: copy just the slice through php://temp (spools past
            // 2MB to disk, so a large range never blows PHP memory).
            $gcLen = $gcEnd - $gcStart + 1;
            $gcOut = fopen('php://temp', 'w+b');
            stream_copy_to_stream($gcIn, $gcOut, $gcLen, $gcStart);
            fclose($gcIn);
            rewind($gcOut);
            return $response->withBody(new \Slim\Psr7\Stream($gcOut))
                ->withHeader('Content-Length', (string) $gcLen);
        } else {
            if($Service->contentType){
                $response = $response->withHeader('Content-Type', $Service->contentType);
            }
            if ($Service->headers) {
                foreach($Service->headers as $headers)
                $response = $response->withHeader($headers[0], $headers[1]);
            }
            return $response;
        }
        } catch (\ApiGoat\Http\HaltResponse $gcHalt) {
            // A40/C7: a service halted (delete refusal, access denied, PDF or
            // mass-action payload). It used to die() here, which skipped every
            // middleware on the way out; the response is rebuilt instead so
            // CORS / security headers / server timing still apply.
            //
            // I-1: HaltResponseMiddleware (registered innermost in
            // config/middlewares.php) is now the AUTHORITY for this — it covers
            // every route, including the hand-written closures below and every
            // route a project registers in config/routes.php. This per-closure
            // catch is kept only so a project that has not yet picked up the
            // middlewares.php line still behaves; where both exist this one
            // simply wins and the middleware never fires.
            return $gcHalt->applyTo($response);
        }
    })->setName($route);
}

foreach ($builderRoutes['html']['POST'] as $route => $params) {
    $app->post(_SUB_DIR_URL . $route . '[/{a}[/{params:.*}]]', function (Request $request, Response $response, $args) {
        try {
        $RouteHelper = new RouteHelper($request, $args);
        $Service = $RouteHelper->getService($response);
        $response->getBody()->write($Service->getResponse());
        if($Service->contentType){
            $response = $response->withHeader('Content-Type', $Service->contentType);
        }
        if ($Service->headers) {
            foreach($Service->headers as $headers)
            $response = $response->withHeader($headers[0], $headers[1]);
        }
        return $response;
        } catch (\ApiGoat\Http\HaltResponse $gcHalt) {
            // A40/C7: a service halted (delete refusal, access denied, PDF or
            // mass-action payload). It used to die() here, which skipped every
            // middleware on the way out; the response is rebuilt instead so
            // CORS / security headers / server timing still apply.
            //
            // I-1: HaltResponseMiddleware (registered innermost in
            // config/middlewares.php) is now the AUTHORITY for this — it covers
            // every route, including the hand-written closures below and every
            // route a project registers in config/routes.php. This per-closure
            // catch is kept only so a project that has not yet picked up the
            // middlewares.php line still behaves; where both exist this one
            // simply wins and the middleware never fires.
            return $gcHalt->applyTo($response);
        }
    })->setName($route);
}
# API
$app->options('/api/v' . API_VERSION . '/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->post(_SUB_DIR_URL . 'api/v' . API_VERSION . '/Authy/{a:auth|renew|refresh}', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);
    $RouteHelper->setArgs('method', 'AUTH');
    // Route is named 'api/Auth' (and refresh 'api/AuthRefresh'), so RouteHelper::getService()
    // would resolve the wrong service name (Auth/AuthRefresh). Instantiate AuthyService explicitly.
    $Service = new \App\AuthyService($request, $response, $RouteHelper->getArgs());
    return $Service->getApiResponse();
})->setName('api/Auth');

foreach ($builderRoutes['json']['GET'] as $route => $params) {
    $app->map(['GET', 'DELETE', 'PATCH', 'PUT', 'POST'], _SUB_DIR_URL . "api/v" . API_VERSION . "/{$route}[/{a}[/{params:.*}]]", function ($request, $response, $args) {
        // add_audit: every write on this request is an API write. Guarded so a
        // project pinned to a runtime without ApiGoat\Audit still serves the route.
        if (class_exists('\ApiGoat\Audit\AuditContext')) { \ApiGoat\Audit\AuditContext::$source = 'api'; }
        try {
        $RouteHelper = new RouteHelper($request, $args);
        $Service = $RouteHelper->getService($response);
        return $Service->getApiResponse();
        } catch (\ApiGoat\Http\HaltResponse $gcHalt) {
            // A40/C7: a service halted (delete refusal, access denied, PDF or
            // mass-action payload). It used to die() here, which skipped every
            // middleware on the way out; the response is rebuilt instead so
            // CORS / security headers / server timing still apply.
            //
            // I-1: HaltResponseMiddleware (registered innermost in
            // config/middlewares.php) is now the AUTHORITY for this — it covers
            // every route, including the hand-written closures below and every
            // route a project registers in config/routes.php. This per-closure
            // catch is kept only so a project that has not yet picked up the
            // middlewares.php line still behaves; where both exist this one
            // simply wins and the middleware never fires.
            return $gcHalt->applyTo($response);
        }
    })->setName('api/' . $route);
}

$app->map(['GET', 'POST'], _SUB_DIR_URL . 'api/v' . API_VERSION . '/ApiGoat/sendEmail[/{i}]', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);
    $Service = new EmailService($request, $response, $RouteHelper->getArgs());
    return $Service->getApiResponse();
})->setName('sendEmail');

$app->get(_SUB_DIR_URL . 'api/v' . API_VERSION . '/_meta', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);
    $Service = new \ApiGoat\Services\MetaService($request, $response, $RouteHelper->getArgs());
    return $Service->getApiResponse();
})->setName('api/Meta');

// The legacy ApiGoat/reset endpoint (PasswordService) was retired: it stored a
// client-supplied passwd_hash with no server-side hashing or throttle. Password
// reset now goes exclusively through the secure Authy/reset -> Authy/resetConfirm
// token flow (server-hashed single-use token, 1h expiry, rate-limited,
// anti-enumeration) implemented in the Authy login controller.

$app->map(['GET', 'POST'], _SUB_DIR_URL . 'api/v' . API_VERSION . '/ApiGoat/account[/{i}]', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);
    $Service = new AccountService($request, $response, $RouteHelper->getArgs());
    return $Service->getApiResponse();
})->setName('resetPassword');

$app->map(['GET', 'POST'], _SUB_DIR_URL . 'api/v' . API_VERSION . '/oauth/{p}[/{c}]', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);

    $Service = new OauthService($request, $response, $RouteHelper->getArgs());
    return $Service->getApiResponse();
})->setName('oAuth-api');

# --- OAuth 2.1 Authorization Server (with_mcp) ---
$app->get(_SUB_DIR_URL . '.well-known/oauth-authorization-server', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);
    $a = $RouteHelper->getArgs(); $a['meta'] = 'as';
    return (new \ApiGoat\Services\OAuthMetadataService($request, $response, $a))->getApiResponse();
})->setName('oauthMetaAs');

$app->get(_SUB_DIR_URL . '.well-known/oauth-protected-resource', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);
    $a = $RouteHelper->getArgs(); $a['meta'] = 'pr';
    return (new \ApiGoat\Services\OAuthMetadataService($request, $response, $a))->getApiResponse();
})->setName('oauthMetaPr');

# Origin-level aliases for the two discovery documents. An app mounted under a
# dot sub-directory (/.admin/) can never serve /.admin/.well-known/... : Apache
# resolves the hidden directory during the authz walk and denies it
# (authz_core AH01630) before mod_rewrite's per-directory fixup ever runs, so no
# .htaccess rule inside .admin/ can rescue it. The project-root .htaccess routes
# /.well-known/oauth-* into this app instead (with_mcp::patchHtaccess), and
# OAuthMetadataService::issuer() advertises the origin to match. Registered only
# when that service says we own the origin — for a root install these paths ARE
# the routes above and a second registration is a FastRoute duplicate fatal.
# method_exists guard: a project pinned to a runtime older than the predicate
# must still boot (it simply keeps serving discovery from its own prefix).
if (method_exists('\ApiGoat\Services\OAuthMetadataService', 'servesOriginDiscovery')
    && \ApiGoat\Services\OAuthMetadataService::servesOriginDiscovery()) {
    $app->get('/.well-known/oauth-authorization-server', function (Request $request, Response $response, $args) {
        $RouteHelper = new RouteHelper($request, $args);
        $a = $RouteHelper->getArgs(); $a['meta'] = 'as';
        return (new \ApiGoat\Services\OAuthMetadataService($request, $response, $a))->getApiResponse();
    })->setName('oauthMetaAsOrigin');

    $app->get('/.well-known/oauth-protected-resource', function (Request $request, Response $response, $args) {
        $RouteHelper = new RouteHelper($request, $args);
        $a = $RouteHelper->getArgs(); $a['meta'] = 'pr';
        return (new \ApiGoat\Services\OAuthMetadataService($request, $response, $a))->getApiResponse();
    })->setName('oauthMetaPrOrigin');
}

$app->post(_SUB_DIR_URL . 'oauth/register', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);
    return (new \ApiGoat\Services\OAuthRegisterService($request, $response, $RouteHelper->getArgs()))->getApiResponse();
})->setName('oauthRegister');

$app->map(['GET', 'POST'], _SUB_DIR_URL . 'oauth/authorize', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);
    return (new \ApiGoat\Services\OAuthAuthorizeService($request, $response, $RouteHelper->getArgs()))->getApiResponse();
})->setName('oauthAuthorize');

$app->post(_SUB_DIR_URL . 'oauth/token', function (Request $request, Response $response, $args) {
    $RouteHelper = new RouteHelper($request, $args);
    return (new \ApiGoat\Services\OAuthTokenService($request, $response, $RouteHelper->getArgs()))->getApiResponse();
})->setName('oauthToken');

$app->post(_SUB_DIR_URL . 'api/v' . API_VERSION . '/mcp', function (Request $request, Response $response, $args) {
    // add_audit: every write on this request is an MCP tool call. Guarded so a
    // project pinned to a runtime without ApiGoat\Audit still serves the route.
    if (class_exists('\ApiGoat\Audit\AuditContext')) { \ApiGoat\Audit\AuditContext::$source = 'mcp'; }
    $RouteHelper = new RouteHelper($request, $args);
    $Service = new \ApiGoat\Mcp\McpEndpoint($request, $response, $RouteHelper->getArgs());
    return $Service->handle();
})->setName('api/Mcp');

$app->map(['GET', 'POST'], _SUB_DIR_URL . 'oauth/{p}[/{c}]', function (Request $request, Response $response, $args) {
        $RouteHelper = new RouteHelper($request, $args);

        $Service = new OauthService($request, $response, $RouteHelper->getArgs());
        $result = $Service->getResponse();
        if ($result['error']) {
            $response->getBody()->write($result['error']);
        } else {
            return $response->withHeader('Location', _SUB_DIR_URL)->withStatus(301);
        }

        return $response;
    })->setName('oAuth');
