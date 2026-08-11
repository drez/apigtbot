<?php

namespace App;


// Server-specific config (written by gc deploy). Under CLI/cron SERVER_NAME
// is absent, so fall back to the host of MY_PROJECT_URL — without this, cron
// on a deployed server silently connects with the build machine's db.php.
$gcServerName = $_SERVER['SERVER_NAME'] ?? '';
if ($gcServerName === '' && function_exists('env')) {
    $gcServerName = (string) parse_url((string) env('MY_PROJECT_URL'), PHP_URL_HOST);
}
if($gcServerName !== '' && file_exists(__DIR__ .'/'. $gcServerName .'.php')){
    \Propel::init(__DIR__ .'/'. $gcServerName .'.php');
}else{
    \Propel::init(__DIR__ . '/db.php');
    $_SESSION['loader'] = ['db' => 'default'];
}

global $con;
$con = \Propel::getConnection('apigtbot');
