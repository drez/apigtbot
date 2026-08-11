<?php

use App\Domains\Deploy\Config;

#error_reporting(0);
#ini_set('display_errors', 0);
set_time_limit(0);
ini_set('max_execution_time',0);

#try autoload
require __DIR__ . '/../vendor/autoload.php';

$Config = new Config();

exit();