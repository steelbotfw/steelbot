#!/usr/local/php7/bin/php
<?php

require __DIR__.'/vendor/autoload.php';

define('APP_DIR', __DIR__);

if (getenv('STEELBOT_ENV')) {
    define('STEELBOT_ENV', getenv('STEELBOT_ENV'));
} else {
    define('STEELBOT_ENV', 'dev');
}

$app = new Steelbot\Application();
$app->getContextRouter()->setRoute('~^Echo$~', __DIR__.'/modules/echo.php');

$app->run();
