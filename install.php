#!/usr/bin/env php
<?php
/**
 * apigtbot installer — self-contained, no build toolchain required.
 *
 * The repo ships all generated artifacts (ORM models, schema.sql, seeds),
 * so installing is: preflight → .env → composer install → database →
 * schema + seeds → admin user → permissions.
 *
 *   php install.php                       # interactive
 *   php install.php --non-interactive \
 *     --db-host=localhost --db-name=apigtbot --db-user=apigtbot \
 *     --db-pass=... [--db-root-user=root --db-root-pass=...] \
 *     --url=https://example.com/apigtbot --admin-pass=...
 *
 * With --db-root-user the script CREATEs the database and grants the app
 * user; without it the database and user must already exist.
 */

error_reporting(E_ALL);
const MIN_PHP = '8.4.0';
$root = __DIR__;
$admin = $root . '/.admin';

function fail(string $msg): never { fwrite(STDERR, "\n[FAIL] $msg\n"); exit(1); }
function ok(string $msg): void { echo "  [ok] $msg\n"; }
function step(string $msg): void { echo "\n== $msg\n"; }

/** Split an SQL file into statements, respecting quotes and comments. */
function sqlStatements(string $sql): array {
    $out = [];
    $buf = '';
    $len = strlen($sql);
    $in = null; // null | ' | " | `
    for ($i = 0; $i < $len; $i++) {
        $c = $sql[$i];
        if ($in !== null) {
            $buf .= $c;
            if ($c === '\\' && $in !== '`') { $buf .= $sql[++$i] ?? ''; continue; }
            if ($c === $in) { $in = null; }
            continue;
        }
        if ($c === '-' && ($sql[$i + 1] ?? '') === '-') {           // -- comment to EOL
            while ($i < $len && $sql[$i] !== "\n") { $i++; }
            continue;
        }
        if ($c === '#') { while ($i < $len && $sql[$i] !== "\n") { $i++; } continue; }
        if ($c === '/' && ($sql[$i + 1] ?? '') === '*') {           // /* block */
            $i += 2;
            while ($i < $len && !($sql[$i] === '*' && ($sql[$i + 1] ?? '') === '/')) { $i++; }
            $i++;
            continue;
        }
        if ($c === "'" || $c === '"' || $c === '`') { $in = $c; $buf .= $c; continue; }
        if ($c === ';') {
            $stmt = trim($buf);
            if ($stmt !== '') { $out[] = $stmt; }
            $buf = '';
            continue;
        }
        $buf .= $c;
    }
    $stmt = trim($buf);
    if ($stmt !== '') { $out[] = $stmt; }
    return $out;
}

// Test hook: `INSTALL_SPLIT_TEST=<file> php install.php` prints the parsed
// statement count and first token of each statement, then exits — validates
// the splitter against the shipped dumps without touching any database.
if (($splitTest = getenv('INSTALL_SPLIT_TEST')) !== false && $splitTest !== '') {
    $sts = sqlStatements((string) file_get_contents($splitTest));
    echo count($sts), " statements\n";
    foreach ($sts as $s) { echo '  ', strtok($s, " \n(`"), "\n"; }
    exit(0);
}

$opts = getopt('', [
    'non-interactive', 'db-host:', 'db-name:', 'db-user:', 'db-pass:',
    'db-root-user:', 'db-root-pass:', 'url:', 'admin-user:', 'admin-pass:',
]);
$interactive = !isset($opts['non-interactive']);

$ask = function (string $label, string $default = '', bool $hidden = false) use ($interactive): string {
    if (!$interactive) {
        return $default;
    }
    $suffix = $default !== '' ? " [$default]" : '';
    echo "$label$suffix: ";
    if ($hidden && DIRECTORY_SEPARATOR === '/') {
        shell_exec('stty -echo');
        $v = trim(fgets(STDIN) ?: '');
        shell_exec('stty echo');
        echo "\n";
    } else {
        $v = trim(fgets(STDIN) ?: '');
    }
    return $v !== '' ? $v : $default;
};

step('Preflight');
if (version_compare(PHP_VERSION, MIN_PHP, '<')) {
    fail('PHP ' . MIN_PHP . '+ required, found ' . PHP_VERSION);
}
foreach (['pdo_mysql', 'bcmath', 'curl', 'openssl', 'json'] as $ext) {
    extension_loaded($ext) ? ok("ext $ext") : fail("missing PHP extension: $ext");
}
$composer = trim((string) shell_exec('command -v composer 2>/dev/null'));
$composer !== '' ? ok('composer found') : fail('composer not found in PATH');
is_dir($admin) || fail('.admin/ directory missing — run from the repo root');
foreach (['/config/Built/schema.sql', '/config/Built/basedata.sql'] as $f) {
    is_file($admin . $f) || fail("missing $f — incomplete checkout");
}
ok('generated artifacts present');

step('Configuration');
$dbHost = $opts['db-host'] ?? $ask('Database host', 'localhost');
$dbName = $opts['db-name'] ?? $ask('Database name', 'apigtbot');
$dbUser = $opts['db-user'] ?? $ask('Database user', 'apigtbot');
$dbPass = $opts['db-pass'] ?? $ask('Database password', '', true);
$rootUser = $opts['db-root-user'] ?? $ask('MySQL root/admin user (empty = database already exists)', '');
$rootPass = $rootUser !== '' ? ($opts['db-root-pass'] ?? $ask("Password for $rootUser", '', true)) : '';
$url = rtrim($opts['url'] ?? $ask('Public base URL', 'https://localhost/apigtbot'), '/');
$adminUser = $opts['admin-user'] ?? $ask('Admin username', 'apigoat');
$adminPass = $opts['admin-pass'] ?? $ask('Admin password (empty = generate)', '', true);
if ($adminPass === '') {
    $adminPass = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
    $generatedAdminPass = true;
}
$dbPass !== '' || fail('a database password is required');

step('Writing .env');
$example = $root . '/.env.example';
is_file($example) || fail('.env.example missing');
if (is_file($root . '/.env')) {
    fail(".env already exists — remove it first if you really want to re-install");
}
$env = file_get_contents($example);
$set = function (string $key, string $value) use (&$env): void {
    $count = 0;
    $env = preg_replace('/^' . preg_quote($key, '/') . '=.*$/m', $key . '="' . addslashes($value) . '"', $env, 1, $count);
    if ($count === 0) {
        $env .= "\n$key=\"" . addslashes($value) . "\"\n";
    }
};
$set('MY_DB_HOST', $dbHost);
$set('MY_DB_NAME', $dbName);
$set('MY_DB_USER', $dbUser);
$set('MY_DB_PASSWORD', $dbPass);
$set('MY_PROJECT_ROOT', dirname($root));
$set('MY_PROJECT_PATH', $admin);
$set('MY_PROJECT_URL', $url . '/.admin');
$set('APP_USER', $adminUser);
$set('APP_PASSWORD', $adminPass);
$set('JWT_SECRET', bin2hex(random_bytes(32)));
$set('OAUTH_ENCRYPTION_KEY', bin2hex(random_bytes(32)));
file_put_contents($root . '/.env', $env);
chmod($root . '/.env', 0600);
if (!file_exists($admin . '/.env')) {
    symlink('../.env', $admin . '/.env') || fail('could not symlink .admin/.env');
}
ok('.env written (0600) and linked into .admin/');

step('Installing dependencies (composer)');
passthru('cd ' . escapeshellarg($admin) . ' && composer install --no-interaction --no-dev 2>&1 | tail -3', $rc);
$rc === 0 || fail('composer install failed');

step('Database');
if ($rootUser !== '') {
    try {
        $rootPdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $rootUser, $rootPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $rootPdo->exec("CREATE USER IF NOT EXISTS " . $rootPdo->quote($dbUser) . "@'localhost' IDENTIFIED BY " . $rootPdo->quote($dbPass));
        $rootPdo->exec("GRANT ALL PRIVILEGES ON `$dbName`.* TO " . $rootPdo->quote($dbUser) . "@'localhost'");
        $rootPdo->exec('FLUSH PRIVILEGES');
        ok("database $dbName + user $dbUser provisioned");
    } catch (PDOException $e) {
        fail('database provisioning: ' . $e->getMessage());
    }
}
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    fail("cannot connect as $dbUser: " . $e->getMessage());
}
ok('connected');

$loadSql = function (string $file) use ($pdo): int {
    $n = 0;
    foreach (sqlStatements((string) file_get_contents($file)) as $stmt) {
        $pdo->exec($stmt);
        $n++;
    }
    return $n;
};

step('Schema + seeds');
if ((int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn() > 0) {
    fail("database $dbName is not empty — refusing to load the schema over existing tables");
}
$n = $loadSql($admin . '/config/Built/schema.sql');
ok("schema.sql ($n statements)");
$n = $loadSql($admin . '/config/Built/basedata.sql');
ok("basedata.sql ($n statements)");
foreach (glob($admin . '/config/custom*.sql') as $seed) {
    $head = (string) file_get_contents($seed, false, null, 0, 400);
    if (str_contains($head, 'gc:dev-only')) {
        ok(basename($seed) . ' skipped (dev-only sample data)');
        continue;
    }
    $n = $loadSql($seed);
    ok(basename($seed) . " ($n statements)");
}

step('Runtime DB config');
// The app resolves credentials from config/Built/<server-host>.php when it
// exists, else falls back to config/Built/db.php (see config/Built/propel.php).
// Write BOTH so web (SERVER_NAME) and CLI/cron (MY_PROJECT_URL host) resolve
// to this install's credentials.
$propelConf = "<?php\n// Generated by install.php — this install's runtime DB config.\nreturn array (\n  'datasources' =>\n  array (\n    'apigtbot' =>\n    array (\n      'adapter' => 'mysql',\n      'connection' =>\n      array (\n        'dsn' => 'mysql:host=" . addslashes($dbHost) . ";dbname=" . addslashes($dbName) . ";charset=utf8;',\n        'user' => '" . addslashes($dbUser) . "',\n        'password' => '" . addslashes($dbPass) . "',\n      ),\n    ),\n    'default' => 'apigtbot',\n  ),\n  'generator_version' => 'install.php',\n);\n";
$urlHost = (string) parse_url($url, PHP_URL_HOST);
foreach (array_unique(array_filter(['db.php', $urlHost !== '' ? $urlHost . '.php' : ''])) as $f) {
    file_put_contents($admin . '/config/Built/' . $f, $propelConf);
    chmod($admin . '/config/Built/' . $f, 0640);
    ok("config/Built/$f written");
}

step('Admin user');
$stmt = $pdo->prepare(
    'INSERT INTO `authy` (`username`, `passwd_hash`, `email`, `is_root`, `deactivate`,
        `id_authy_group`, `is_system`, `date_creation`)
     VALUES (?, ?, ?, 0, 1, 2, 1, NOW())'
);
$stmt->execute([$adminUser, password_hash($adminPass, PASSWORD_BCRYPT), 'admin@' . parse_url($url, PHP_URL_HOST)]);
ok("admin user '$adminUser' created (group: Admin)");

step('Permissions');
foreach (['/tmp', '/tmp/logs', '/public/css/min', '/public/js/min'] as $dir) {
    $d = $admin . $dir;
    is_dir($d) || @mkdir($d, 0775, true);
    @chmod($d, 0775);
}
ok('writable directories prepared (grant your web-server user write access to .admin/tmp and .admin/public/{css,js}/min)');

echo <<<TXT

============================================================
 Install complete.

 Admin UI:   $url/.admin
 Login:      $adminUser

TXT;
if (isset($generatedAdminPass)) {
    echo " Password:   $adminPass   (generated — store it now, it is not shown again)\n";
}
echo <<<TXT

 Next steps:
  1. Point your web server's document root / vhost at this
     directory and enable URL rewriting.
  2. Install the daemon cron jobs (adjust the PHP and repo paths):
       * * * * *    php8.4 $admin/bin/gtbot-watchdog  >/dev/null 2>&1
       */10 * * * * php8.4 $admin/bin/gtbot-market-collect >/dev/null 2>&1
       0 3 * * *    php8.4 $admin/bin/gtbot-backup    >/dev/null 2>&1
  3. Log in, create a Grid Run (Trading > Grid Run), set it Live —
     the watchdog spawns its daemon within a minute. Everything is
     paper-trading by default (see README, "Safety-model flags").
============================================================

TXT;
