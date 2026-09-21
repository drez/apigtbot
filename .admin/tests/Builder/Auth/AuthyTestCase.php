<?php

namespace Tests\Builder\Auth;

use Tests\Builder\Support\DbTestCase;

/**
 * Base class for tests that need Authy / AuthyQuery against a real DB.
 *
 * Same contract as DbTestCase (read its header before writing a DB-backed
 * test) with one difference: the source database is the parallel
 * `apigtbot_test` project's, never the dev database — these tests
 * exercise login and account state, so they must not read or write the data
 * you develop against. Under paratest each worker still gets its own clone,
 * `gc_apigtbot_test_t<TOKEN>`.
 *
 * Provision the test DB via `./gc create apigtbot_test` from the
 * gc root, then load schema with mysqldump from gc_apigtbot. Without it these
 * tests skip.
 */
abstract class AuthyTestCase extends DbTestCase
{
    protected static function sourceConnection(): array
    {
        $testEnvPath = __DIR__ . '/../../../../../apigtbot_test/.env';
        if (! file_exists($testEnvPath)) {
            self::markTestSkipped(
                "Test DB env not found at: {$testEnvPath}\n"
                . "Provision via './gc create apigtbot_test' from the gc root, "
                . 'then mysqldump --no-data gc_apigtbot | mysql gc_apigtbot_test.'
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

        return [
            'host' => $env['MY_DB_HOST'] ?? 'localhost',
            'name' => $env['MY_DB_NAME'] ?? '',
            'user' => $env['MY_DB_USER'] ?? '',
            'pass' => $env['MY_DB_PASSWORD'] ?? '',
        ];
    }
}
