<?php
use \Ahc\Env\Loader;
(new Loader)->load(__DIR__.'/../.env');
$settings = require __DIR__ . '/settings.defaults.php';

return $settings;
