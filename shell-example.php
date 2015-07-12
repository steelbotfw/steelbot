#!/usr/local/php7/bin/php
<?php

/**
 * This is an example of a bot with shell(stdin/stdout) protocol
 */

require __DIR__.'/vendor/autoload.php';

$config = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(__DIR__.'/config.yml'));

$app = new \Steelbot\Application($config);
$app->run();
