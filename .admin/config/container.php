<?php

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\CallableResolverInterface;
use Slim\CallableResolver;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;

use Psr\Container\ContainerInterface;
use Selective\Config\Configuration;
use ApiGoat\Middlewares\RecallMiddleware;
use ApiGoat\Middlewares\AuthyMiddleware;
use ApiGoat\Middlewares\RouteParser;
use ApiGoat\Middlewares\RbacMiddleware;
use ApiGoat\Handlers\ExceptionHandler;
use ApiGoat\Handlers\HtmlErrorRenderer;
use ApiGoat\Handlers\JsonErrorRenderer;

use JimTools\JwtAuth\Middleware\JwtAuthentication;
use JimTools\JwtAuth\Decoder\FirebaseDecoder;
use JimTools\JwtAuth\Options;
use JimTools\JwtAuth\Rules\RequestMethodRule;
use JimTools\JwtAuth\Rules\RequestPathRule;
use JimTools\JwtAuth\Secret;

use ApiGoat\Middlewares\CorsMiddleware;
use Northwoods\Middleware\ConditionalMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;

use Analog\Logger;

return [
    Configuration::class => function () {
        return new Configuration(require __DIR__ . '/../config/settings.php');
    },
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        $config = $container->get(Configuration::class);

        $_SESSION[_AUTH_VAR]->config['jwt'] = $config->getArray('jwt_middleware');
        $_SESSION[_AUTH_VAR]->config['locale'] = $config->getArray('locale');
        return $app;
    },
    RecallMiddleware::class => function ($container) {
        $standard_actions = $container->get(Configuration::class)->getArray('route.standard_actions');

        return new RecallMiddleware($standard_actions);
    },
    CorsMiddleware::class => function ($container) {
        // ApiGoat\Middlewares\CorsMiddleware enforces a fixed, hardened policy
        // (wildcard origin, credentials off, no Origin reflection) and ignores
        // the `cors` settings block — it only needs a response factory to mint
        // the 204 preflight short-circuit.
        return new CorsMiddleware($container->get(ResponseFactoryInterface::class));
    },
    JwtAuthentication::class => function ($container) {
        $jwt_settings = $container->get(Configuration::class)->getArray('jwt_middleware');

        $beforeHandler = new \ApiGoat\Handlers\JwtBeforeHandler($container);

        $pathSetting = $jwt_settings['path'] ?? '/';
        $paths       = is_array($pathSetting) ? $pathSetting : [$pathSetting];
        $ignore      = (isset($jwt_settings['ignore']) && is_array($jwt_settings['ignore']))
            ? $jwt_settings['ignore']
            : [];
        // Always include '/' so a mis-typed jwt `path` (vs real URI) cannot disable JWT entirely; ignores still skip Authy/auth, etc.
        if (!in_array('/', $paths, true)) {
            $paths[] = '/';
        }

        // Bearer auth accepts TWO token families:
        //   1. app HS256 tokens (RefreshTokenService::mintForLogin, verified with JWT_SECRET)
        //   2. OAuth 2.1 RS256 access tokens (league AS, verified with the OAuth public key)
        // Mobile/MCP clients present OAuth tokens; the admin/refresh flow uses HS256. A single
        // FirebaseDecoder is one algorithm, and Firebase multi-key mode needs matching `kid`
        // headers (ours have none), so wrap both in a decoder that tries HS256 then RS256.
        $primaryDecoder = new FirebaseDecoder(new Secret($jwt_settings['secret'], $jwt_settings['algorithm']));
        $oauthCfg       = $container->get(Configuration::class)->getArray('oauth_server');
        $oauthPublicKey = (string) ($oauthCfg['public_key'] ?? '');
        $oauthDecoder   = $oauthPublicKey !== '' ? new FirebaseDecoder(new Secret($oauthPublicKey, 'RS256')) : null;
        $decoder = new class($primaryDecoder, $oauthDecoder) implements \JimTools\JwtAuth\Decoder\DecoderInterface {
            public function __construct(private FirebaseDecoder $primary, private ?FirebaseDecoder $oauth) {}
            public function decode(string $jwt): array
            {
                try {
                    return $this->primary->decode($jwt);
                } catch (
                    \JimTools\JwtAuth\Exceptions\SignatureInvalidException
                    | \JimTools\JwtAuth\Exceptions\UnexpectedValueException
                    | \JimTools\JwtAuth\Exceptions\DomainException
                    | \JimTools\JwtAuth\Exceptions\InvalidArgumentException $e
                ) {
                    // HS256 rejected it (wrong algorithm / signature / malformed) — it may be an
                    // OAuth RS256 access token. Try the OAuth public key. Expired / not-yet-valid
                    // are deliberately NOT caught, so a genuinely expired token still fails.
                    if ($this->oauth === null) {
                        throw $e;
                    }
                    return $this->oauth->decode($jwt);
                }
            }
        };

        $jwtAuth = new JwtAuthentication(
            new Options(
                isSecure: (bool) ($jwt_settings['secure'] ?? true),
                before: $beforeHandler,
            ),
            $decoder,
            [
                new RequestMethodRule(),
                new RequestPathRule(paths: $paths, ignore: $ignore),
            ]
        );

        return new ConditionalMiddleware($jwtAuth, function (Request $request): bool {
            $parsed = $request->getAttribute('parsed_args');
            if (!is_array($parsed) || ($parsed['is_api'] ?? false) != true) {
                return false;
            }

            if ($_SESSION[_AUTH_VAR]->get('connected') === 'YES') {
                return false;
            }

            // ApiRbac "Public" sets rbac_public = passed → AuthyMiddleware skips, but SchedulingServiceWrapper
            // still enforces assertSchedulingAuthyAcl() (session). Without JWT here, Bearer requests never
            // open $_SESSION — createProspect returns the same "Authentication required" as AuthyMiddleware.
            $authLine = $request->getHeaderLine('Authorization');
            if ($authLine === '') {
                $authLine = $request->getHeaderLine('X-Authorization');
            }
            if ($authLine !== '' && preg_match('/Bearer\s+\S/i', $authLine)) {
                return true;
            }

            if ($request->getAttribute('rbac_public') === 'passed') {
                return false;
            }

            return true;
        });
    },
    Logger::class => function ($container) {
        $log_settings = $container->get(Configuration::class)->getArray('log');
        $logger = new Logger;
        $logger->handler($log_settings['facility']::init());
        return $logger;
    },
    RouteParser::class => function ($container) {
        return new RouteParser();
    },
    Authy::class => function ($container) {
        return new AuthyMiddleware();
    },
    RbacMiddleware::class => function ($container) {
        return new RbacMiddleware();
    },
    CallableResolverInterface::class => static function (ContainerInterface $container): CallableResolverInterface {
        return new CallableResolver($container);
    },
    ResponseFactoryInterface::class => static function (): ResponseFactoryInterface {
        return new ResponseFactory();
    },
    ExceptionHandler::class => function ($container) {
        $errorHandler =  new ExceptionHandler(
            $container->get(CallableResolverInterface::class),
            $container->get(ResponseFactoryInterface::class),
            null,
            $container
        );
        $errorHandler->registerErrorRenderer('text/html', HtmlErrorRenderer::class);
        $errorHandler->registerErrorRenderer('application/json', JsonErrorRenderer::class);
        return $errorHandler;
    }
];
