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

# Foreach defined builder routes
# Add post and get
foreach ($builderRoutes['html']['GET'] as $route => $params) {
    $app->get(_SUB_DIR_URL . $route . '[/{a}[/{params:.*}]]', function (Request $request, Response $response, $args) {
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
                return $response; // images/PDF: legacy in-memory body, unchanged
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
    })->setName($route);
}

foreach ($builderRoutes['html']['POST'] as $route => $params) {
    $app->post(_SUB_DIR_URL . $route . '[/{a}[/{params:.*}]]', function (Request $request, Response $response, $args) {
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
        $RouteHelper = new RouteHelper($request, $args);
        $Service = $RouteHelper->getService($response);
        return $Service->getApiResponse();
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
