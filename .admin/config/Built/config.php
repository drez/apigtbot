<?php

namespace App;

use \Ahc\Env\Loader;

// Web requests stay capped tight; CLI (the test suite, scripts, cron) needs
// headroom — the full PHPUnit suite OOMs at 128M. Set here (not in the
// test base) so it is durable: config.php is generated every build, whereas
// the template-refreshed AuthyTestCase would lose an override.
ini_set('memory_limit', php_sapi_name() === 'cli' ? '512M' : '128M');

(new Loader)->load(__DIR__.'/../../../.env');

if (php_sapi_name() != 'cli') {
    $subdir_url = parse_url(env('MY_PROJECT_URL') . DIRECTORY_SEPARATOR, PHP_URL_PATH);
} else {
    $subdir_url = "";
}

$defines = [
    "_DATA_SRC" => "apigtbot",
    "_PROJECT_NAME" => "apigtbot",
    "_PROJECT_PRFX" => "",
    "API_VERSION" => "1",
    "_DEPLOYMENT_TYPE" => "Standalone",
    "_SYSTEM_USER" => "web1",
    "_SITE_TITLE" => "",
    "_SUB_DIR_URL" => $subdir_url,	 # Routes prefix (/project_name/admin_dir/)
    "_ASSET_RELATIVE_PATH" => "",
    "_BASE_DIR" => env('MY_PROJECT_PATH').DIRECTORY_SEPARATOR, 
    "_AUTH_VAR" => "83585994bb",
    "_CRYPT_KEY" => "cb4a5ad416054075",
    "_CRYPT_IV" => "862171604cba0a9e",
];

$locales = [
    LC_MONETARY => 'en_CA.UTF-8',
    LC_NUMERIC => 'en_CA.UTF-8',
    LC_TIME => 'en_CA.UTF-8'
];

if(!isset($skipConfig)){
    foreach($defines as $define => $val){
        define($define, $val);
    }

    if (php_sapi_name() != 'cli') {
        $_gcScheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)) ? 'https' : 'http';
            define("_SITE_URL", $_gcScheme . "://" . $_SERVER['SERVER_NAME'] . _SUB_DIR_URL);
    } else {
        define("_SITE_URL", "");
    }
    
    define("_SRC_URL", _SITE_URL);

    define("_INSTALL_PATH", _BASE_DIR);
    define("_VENDOR_DIR", _BASE_DIR . "vendor/");

    define("_PROPEL_BASE_PATH", _VENDOR_DIR . "propel/propel1/");
    define("_PROPEL_RUNTIME_PATH", _PROPEL_BASE_PATH . 'runtime');
    define("_PEAR_LOG_PATH", _PROPEL_RUNTIME_PATH);
    define("_PROPEL_GEN", _PROPEL_BASE_PATH . "generator/bin/propel-gen");

    $_emailSettings = require __DIR__ . '/../settings.defaults.php';
    define("_FROM", $_emailSettings['email']['default_from']);
    unset($_emailSettings);

    foreach($locales as $locale => $val){
        setlocale($locale, $val);
    }
}
