<?php

use ApiGoat\Utility\Assets;

// The pipeline salt is a per-build id (written by `gc build` to
// config/.buildid). It changes every build, so the minified bundle
// filename rotates and browsers/proxies can never serve a stale
// bundle after an asset update. ('auto'/filemtime proved unreliable
// behind warm PHP-FPM + realpath cache; a plain `true` was a constant
// hash → permanently stale.) Fallback to this file's mtime if the
// build id is missing (e.g. a project not yet rebuilt).
$buildId = trim((string) @file_get_contents(__DIR__ . '/.buildid'));
if ($buildId === '') {
    $buildId = (string) (@filemtime(__FILE__) ?: '1');
}

$Assets = new Assets(['deployment_type' => _DEPLOYMENT_TYPE, 'pipeline' => $buildId]);
$Assets->add('public/css/main.css');
$Assets->add('public/css/remix/remixicon.css');
$Assets->add('public/js/index.js');
$Assets->add('public/js/selectbox.js');
$Assets->add('public/js/app/list.js');
$Assets->add('public/js/app/filter.js');
$Assets->add('public/js/app/shell.js');
$Assets->add('public/js/app/screens.js');
$Assets->add('public/js/app/drawer.js');
$Assets->add('public/js/app/autocomplete.js');
$Assets->add('public/js/app/taginput.js');
$Assets->add('public/js/app/upload.js');
$Assets->add('public/js/app/colorfield.js');
$Assets->add('public/js/app/locationfield.js');
$Assets->add('public/js/app/pdfmenu.js');
$Assets->add('public/js/app/stripe.js');
$Assets->add('public/js/pwa.js');

$AssetsAdmin = new Assets(['deployment_type' => _DEPLOYMENT_TYPE, 'pipeline' => false]);
// CKEditor 5 (self-hosted UMD bundle, composer-managed — see composer.json
// "ckeditor/ckeditor5-self-hosted"; gc build runs composer update which fetches
// the pinned version into vendor/). Exposes window.CKEDITOR; driven through the
// gcEditor helper. ckeditor.config.js builds window.gcEditorConfig from the
// bundle's plugin exports, so it must load after the UMD file.
$AssetsAdmin->add('vendor/ckeditor/ckeditor5-self-hosted/ckeditor5/ckeditor5.css');
$AssetsAdmin->add('vendor/ckeditor/ckeditor5-self-hosted/ckeditor5/ckeditor5.umd.js');
$AssetsAdmin->add('public/js/gceditor.js');
$AssetsAdmin->add('public/js/ckeditor.config.js');

$AssetsHead = new Assets(['deployment_type' => _DEPLOYMENT_TYPE, 'pipeline' => $buildId]);
// jQuery dropped (jquery core removal): the admin client is fully vanilla.
