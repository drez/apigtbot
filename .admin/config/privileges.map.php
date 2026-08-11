<?php

$privilegesMap = require _BASE_DIR . 'config/Built/privileges.map.defaults.php';

$privilegesMap["action"][] = ['doc' => 'r'];

return $privilegesMap;
