<?php

namespace Tests\Builder\Auth;

use ApiGoat\Sessions\AuthySession;
use PHPUnit\Framework\TestCase;
use Propel;

/**
 * Base class for tests that need Authy / AuthyQuery against a real DB.
 *
 * Boots the project runtime constants and points Propel at the parallel
 * `apigtbot_test` project's database. Each test runs inside a
 * transaction that's rolled back in tearDown, so fixtures don't leak.
 *
 * Provision the test DB via `./gc create apigtbot_test` from the
 * gc root, then load schema with mysqldump from gc_apigtbot.
 */
abstract class AuthyTestCase extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!defined('_AUTH_VAR')) {
            require __DIR__ . '/../../../config/Built/config.php';
        }

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION[_AUTH_VAR] = new AuthySession();

        if (Propel::isInit()) {
            return;
        }

        $testEnvPath = __DIR__ . '/../../../../../apigtbot_test/.env';
        if (!file_exists($testEnvPath)) {
            self::markTestSkipped(
                "Test DB env not found at: {$testEnvPath}\n"
                . "Provision via './gc create apigtbot_test' from the gc root, "
                . "then mysqldump --no-data gc_apigtbot | mysql gc_apigtbot_test."
            );
        }

        // Not parse_ini_file(): the generated .env carries '#' comment lines
        // with characters (parentheses) that abort INI parsing entirely.
        $env = [];
        foreach (file($testEnvPath) as $line) {
            if (preg_match('/^\s*([A-Z0-9_]+)\s*=\s*"?([^"\r\n]*)"?\s*$/', $line, $m)) {
                $env[$m[1]] = $m[2];
            }
        }

        set_include_path(_BASE_DIR . 'src/' . PATH_SEPARATOR . get_include_path());

        Propel::setConfiguration([
            'datasources' => [
                // Datasource id must be the project's actual Propel datasource
                // (_DATA_SRC in config/Built/config.php) so the generated models
                // resolve their connection. For git-cloned projects this differs
                // from the project directory name (which only names the
                // apigtbot_test test-DB dir above), so DO NOT hardcode it.
                _DATA_SRC => [
                    'adapter'    => 'mysql',
                    'connection' => [
                        'dsn'      => sprintf(
                            'mysql:host=%s;dbname=%s;charset=utf8;',
                            $env['MY_DB_HOST'],
                            $env['MY_DB_NAME']
                        ),
                        'user'     => $env['MY_DB_USER'],
                        'password' => $env['MY_DB_PASSWORD'],
                    ],
                ],
                'default' => _DATA_SRC,
            ],
        ]);
        Propel::initialize();
    }

    protected function setUp(): void
    {
        Propel::getConnection(_DATA_SRC)->beginTransaction();
        $_SESSION[_AUTH_VAR] = new AuthySession();
    }

    protected function tearDown(): void
    {
        $con = Propel::getConnection(_DATA_SRC);
        if ($con->isInTransaction()) {
            $con->rollBack();
        }
    }
}
