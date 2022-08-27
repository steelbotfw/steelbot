<?php

require __DIR__ . '/vendor/autoload.php';

$app = new \Steelbot\Application('dev', true);
$app->boot();
$app->getEventDispatcher()->addListener(\Steelbot\Protocol\Irc\Protocol::EVENT_AFTER_CONNECT, function ($event) use ($app) {
    $function = function () use ($app) {
        yield from $app->getProtocol()->join('#ngircd');
    };

    new \Icicle\Coroutine\Coroutine($function());

});
$app->run();

