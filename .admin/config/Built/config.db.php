<?php

if (class_exists('App\Config')) {
    if (!isset($_SESSION[_AUTH_VAR]->configdb) || !is_array($_SESSION[_AUTH_VAR]->configdb) || $_SESSION[_AUTH_VAR]->config_changed === 'yes') {
        $Configs = \App\ConfigQuery::create()->find();
        unset($_SESSION[_AUTH_VAR]->configdb);
        foreach ($Configs as $Config) {
            if ($Config->getConfig()) {
                $_SESSION[_AUTH_VAR]->configdb[] = array($Config->getConfig(), $Config->getValue());
            }
        }
    }

    if (isset($_SESSION[_AUTH_VAR]->configdb)) {
        foreach ($_SESSION[_AUTH_VAR]->configdb as $Config) {
            define($Config[0], $Config[1]);
        }
        unset($Configs);
        $_SESSION[_AUTH_VAR]->config_changed = 'no';
    }
}
