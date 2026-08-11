<?php

/**
 * PHPUnit bootstrap.
 *
 * Loads composer autoload — sufficient for tests that exercise classes
 * in isolation with mocked collaborators.
 *
 * Tests that need runtime constants (_BASE_DIR, _AUTH_VAR, etc.) or a
 * Propel connection require AuthyTestCase, which lazily boots both.
 */

require __DIR__ . '/../vendor/autoload.php';
