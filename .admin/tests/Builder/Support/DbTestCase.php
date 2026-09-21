<?php

namespace Tests\Builder\Support;

use PDO;
use PHPUnit\Framework\TestCase;
use Propel;

/**
 * Base class for every test that needs a real database.
 *
 * WRITING A DB-BACKED TEST — the rules the parallel runner imposes:
 *
 *   1. Extend this class. Never call Propel::setConfiguration() in a test:
 *      a test that boots its own connection targets the shared dev database
 *      and will corrupt (or be corrupted by) the tests running beside it.
 *      `gc build` reports any test that does.
 *   2. Never assume another test ran first. Each test class may run in its
 *      own process, in any order. Constants and the Propel connection are
 *      booted here; fixtures your test needs, your test creates.
 *   3. Never assume a row you did not create. Look catalog rows up (or make
 *      them); do not hardcode ids, and do not INSERT a fixed unique value —
 *      it collides with the same row in a sibling worker's database and with
 *      the seed data. Derive unique values from static::uniq('prefix').
 *   4. Everything a test writes is rolled back in tearDown. Do not commit,
 *      and if you must, clean up in your own tearDown before parent::.
 *
 * PARALLELISM. `gc build`'s "Run tests?" runs paratest, one worker process
 * per test class. Workers cannot share one database: uncommitted rows from a
 * sibling's open transaction collide on unique keys and block on row locks.
 * So each worker gets its OWN database, `<db>_t<TEST_TOKEN>`, cloned from the
 * source database (tables + data, server-side) the first time that worker
 * needs it. Under plain `vendor/bin/phpunit` there is no TEST_TOKEN and the
 * source database is used directly, exactly as before.
 *
 * The clone is re-made only when the schema fingerprint changes (the emitted
 * schema.sql + the config/*.sql seeds), so a normal run reuses the worker
 * databases and pays nothing. Force a refresh — after changing dev data the
 * tests read — with GC_TEST_DB_REFRESH=1. Views, triggers and routines are
 * NOT cloned (generated projects have none).
 *
 * A test class that genuinely cannot run beside others (it commits, or it
 * needs the dev database itself) opts out with #[Group('db')]: the build runs
 * `paratest --exclude-group db` first, then `phpunit --group db` serially.
 * PHPUnit does not inherit #[Group] from a parent, so tag the concrete class.
 *
 * Worker databases need one grant, `GRANT ALL ON `<db>\_t%`.*`, which
 * `gc build` applies with the orchestrator's root credentials (Project\TestDb).
 */
abstract class DbTestCase extends TestCase
{
    /** Database the Propel connection in THIS process is bound to. */
    private static ?string $boundDb = null;

    /** Marker table holding the fingerprint of the clone in a worker database. */
    private const MARKER = '_gc_test_db';

    /**
     * Connection details of the SOURCE database — the one worker databases
     * are cloned from. Defaults to the project's own database (.env).
     * Override to point a suite somewhere else; see AuthyTestCase, which
     * targets the parallel `<project>_test` project.
     *
     * @return array{host:string,name:string,user:string,pass:string}
     */
    protected static function sourceConnection(): array
    {
        return [
            'host' => (string) env('MY_DB_HOST'),
            'name' => (string) env('MY_DB_NAME'),
            'user' => (string) env('MY_DB_USER'),
            'pass' => (string) env('MY_DB_PASSWORD'),
        ];
    }

    /**
     * Unique-per-worker string for columns under a UNIQUE index. Sibling
     * workers hold separate databases, but a value you also use as a lookup
     * key must not collide with seed data either — so prefix, token, and a
     * per-call counter.
     */
    protected static function uniq(string $prefix): string
    {
        static $n = 0;
        return $prefix . '_' . (self::workerToken() ?: '0') . '_' . (++$n) . '_' . substr(uniqid(), -5);
    }

    /** paratest's worker id; '' under plain phpunit (single process). */
    final protected static function workerToken(): string
    {
        $token = getenv('TEST_TOKEN');
        return is_string($token) && preg_match('/^[0-9]{1,4}$/', $token) ? $token : '';
    }

    /**
     * Database this process must use: the source under plain phpunit, this
     * worker's own clone under paratest. Falls back to the source when the
     * suffixed name would exceed MySQL's 64-character limit (such a suite
     * still runs, just serially behind #[Group('db')]).
     */
    final public static function workerDbName(string $source): string
    {
        $token = self::workerToken();
        if ($token === '') {
            return $source;
        }
        $worker = $source . '_t' . $token;

        return strlen($worker) <= 64 ? $worker : $source;
    }

    public static function setUpBeforeClass(): void
    {
        if (! defined('_AUTH_VAR')) {
            require __DIR__ . '/../../../config/Built/config.php';
        }
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION[_AUTH_VAR] = new \ApiGoat\Sessions\AuthySession();

        $src = static::sourceConnection();
        $db  = self::workerDbName($src['name']);
        if ($db !== $src['name']) {
            self::ensureWorkerDb($src, $db);
        }

        // A worker process runs many test classes; rebind only when the
        // target database actually changes (suites with different sources
        // can share one process — the isInit() check alone would silently
        // leave the second suite on the first one's connection).
        if (self::$boundDb === $db && Propel::isInit()) {
            return;
        }

        set_include_path(_BASE_DIR . 'src/' . PATH_SEPARATOR . get_include_path());

        Propel::setConfiguration([
            'datasources' => [
                // Datasource id must be the project's actual Propel datasource
                // (_DATA_SRC in config/Built/config.php) so the generated
                // models resolve their connection. For git-cloned projects it
                // differs from the project directory name — never hardcode it.
                _DATA_SRC => [
                    'adapter'    => 'mysql',
                    'connection' => [
                        'dsn'      => sprintf(
                            'mysql:host=%s;dbname=%s;charset=utf8mb4;',
                            $src['host'],
                            $db
                        ),
                        'user'     => $src['user'],
                        'password' => $src['pass'],
                    ],
                ],
                'default' => _DATA_SRC,
            ],
        ]);
        Propel::initialize();
        self::$boundDb = $db;
    }

    protected function setUp(): void
    {
        Propel::getConnection(_DATA_SRC)->beginTransaction();
        $_SESSION[_AUTH_VAR] = new \ApiGoat\Sessions\AuthySession();
    }

    protected function tearDown(): void
    {
        $con = Propel::getConnection(_DATA_SRC);
        if ($con->isInTransaction()) {
            $con->rollBack();
        }
    }

    /**
     * Create this worker's database and clone the source into it, unless the
     * clone already matches the current schema fingerprint.
     */
    private static function ensureWorkerDb(array $src, string $worker): void
    {
        $pdo = new PDO(
            sprintf('mysql:host=%s;charset=utf8mb4', $src['host']),
            $src['user'],
            $src['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        try {
            $pdo->exec('CREATE DATABASE IF NOT EXISTS ' . self::quote($worker));
        } catch (\Throwable $e) {
            self::markTestSkipped(
                "Cannot create the per-worker test database {$worker}: " . $e->getMessage() . "\n"
                . 'Run `gc build` once (it grants the project user ALL on `' . $src['name'] . "\\_t%`), "
                . 'or run the suite serially with vendor/bin/phpunit.'
            );
        }

        $fingerprint = self::schemaFingerprint();
        if (getenv('GC_TEST_DB_REFRESH') !== '1' && self::cloneFingerprint($pdo, $worker) === $fingerprint) {
            return;
        }

        // cloneInto() DROPS every table in $worker. Only a database this
        // harness made (it carries the marker) or an empty one is ours to
        // wipe — CREATE DATABASE IF NOT EXISTS also "succeeds" on a real,
        // unrelated database that merely happens to be named <db>_t<N>.
        if (self::cloneFingerprint($pdo, $worker) === null && self::tables($pdo, $worker) !== []) {
            self::markTestSkipped(
                "Refusing to clone into {$worker}: it holds tables but no " . self::MARKER . " marker, so it was "
                . 'not created by this test harness. Drop or rename it, or run serially with vendor/bin/phpunit.'
            );
        }

        self::cloneInto($pdo, $src['name'], $worker);

        $pdo->exec(
            'CREATE TABLE ' . self::quote($worker) . '.' . self::quote(self::MARKER)
            . ' (fingerprint VARCHAR(64) NOT NULL) ENGINE=InnoDB'
        );
        $stmt = $pdo->prepare(
            'INSERT INTO ' . self::quote($worker) . '.' . self::quote(self::MARKER) . ' VALUES (?)'
        );
        $stmt->execute([$fingerprint]);
    }

    /** Fingerprint of the clone already in $worker, or null when there is none. */
    private static function cloneFingerprint(PDO $pdo, string $worker): ?string
    {
        try {
            $sql = 'SELECT fingerprint FROM ' . self::quote($worker) . '.' . self::quote(self::MARKER) . ' LIMIT 1';
            $value = $pdo->query($sql)->fetchColumn();

            return is_string($value) ? $value : null;
        } catch (\Throwable $e) {
            return null; // no marker table yet — first run for this worker
        }
    }

    /**
     * Copy every table (structure + rows) from $source into $worker.
     *
     * The structure comes from SHOW CREATE TABLE, not `CREATE TABLE ... LIKE`,
     * which silently drops FOREIGN KEY definitions: a clone without them lets
     * an orphan row insert succeed and a cascade never fire, so a test
     * asserting either passes (or fails) for the wrong reason. The DDL names
     * its referenced tables unqualified, so it is executed with $worker as the
     * current database and FK checks off — tables are created in arbitrary
     * order, and rows land before the tables they point at exist.
     */
    private static function cloneInto(PDO $pdo, string $source, string $worker): void
    {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach (self::tables($pdo, $worker) as $table) {
                $pdo->exec('DROP TABLE IF EXISTS ' . self::quote($worker) . '.' . self::quote($table));
            }
            $pdo->exec('USE ' . self::quote($worker));
            // Marker while cloning, with a fingerprint no schema hashes to: a
            // clone interrupted half-way still reads as ours (and as stale)
            // next run instead of tripping the not-our-database refusal. The
            // caller writes the real marker once the copy is complete.
            $pdo->exec('CREATE TABLE ' . self::quote(self::MARKER) . ' (fingerprint VARCHAR(64) NOT NULL) ENGINE=InnoDB');
            $pdo->exec('INSERT INTO ' . self::quote(self::MARKER) . " VALUES ('incomplete')");
            foreach (self::tables($pdo, $source) as $table) {
                $src = self::quote($source) . '.' . self::quote($table);
                $ddl = $pdo->query('SHOW CREATE TABLE ' . $src)->fetch(PDO::FETCH_NUM);
                $pdo->exec((string) $ddl[1]);
                $pdo->exec('INSERT INTO ' . self::quote($table) . ' SELECT * FROM ' . $src);
            }
            $pdo->exec('DROP TABLE ' . self::quote(self::MARKER));
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /** @return string[] base tables (not views) in $schema */
    private static function tables(PDO $pdo, string $schema): array
    {
        $stmt = $pdo->prepare(
            'SELECT table_name FROM information_schema.tables '
            . "WHERE table_schema = ? AND table_type = 'BASE TABLE'"
        );
        $stmt->execute([$schema]);

        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Identity of the schema the worker databases were cloned from: the
     * emitted schema.sql plus the seed SQL, by size and mtime. Changing any
     * of them re-clones every worker on the next run.
     */
    private static function schemaFingerprint(): string
    {
        $files = array_merge(
            [_BASE_DIR . 'config/Built/schema.sql'],
            glob(_BASE_DIR . 'config/*.sql') ?: []
        );
        sort($files);

        $parts = [];
        foreach ($files as $file) {
            if (is_file($file)) {
                $parts[] = basename($file) . ':' . filesize($file) . ':' . filemtime($file);
            }
        }

        return substr(sha1(implode('|', $parts)), 0, 40);
    }

    /** Backtick-quote an identifier. */
    private static function quote(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
