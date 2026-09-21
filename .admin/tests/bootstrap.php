<?php

/**
 * PHPUnit bootstrap.
 *
 * Loads composer autoload, then the generated runtime config so that
 * _BASE_DIR, _DATA_SRC, _AUTH_VAR and the rest are defined for EVERY test
 * process. Booting them here (not in a base class) is what lets a test class
 * run on its own, in any order, in any worker: the default run is parallel
 * (paratest), so a test that only worked because a sibling had loaded config
 * first is a bug that shows up as an "Undefined constant" error.
 *
 * It does NOT open a database connection. Tests that need one extend
 * Tests\Builder\Support\DbTestCase, which binds Propel to this worker's own
 * database.
 */

require __DIR__ . '/../vendor/autoload.php';

$gcConfig = __DIR__ . '/../config/Built/config.php';
if (is_file($gcConfig) && is_file(__DIR__ . '/../../.env')) {
    require $gcConfig;
}

// Register this project's translation dictionary (merged over the apigoat/runtime
// base), mirroring config/legacy.php. Unit tests exercise Translator /
// document-block classes directly without booting the full app, so without this
// they assert against raw i18n keys instead of the translated strings.
if (class_exists(\ApiGoat\I18n\Translator::class)
    && is_dir(__DIR__ . '/../src/App/Domains/I18n/lang')
) {
    \ApiGoat\I18n\Translator::registerDictionaryPath(__DIR__ . '/../src/App/Domains/I18n/lang');
}
