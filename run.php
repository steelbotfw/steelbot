<?php

define('APP_DIR', dirname(__FILE__));
define('STEELBOT_DIR', realpath("./"));

$config = array(
	'db' => array(
		'engine' => 'mysqldb',
		'password' => '123456',
		'database' => 'steelbot21'
	),
	'proto' => array(
		//'engine' => 'icq',
		//'uin' => '37751377',
		
		//'password' => '$+Ce=(Yc'
		'engine' => 'jabber',
		'jid' => 'test@test.ru',
		'password' => '123'
	),
	'plugins' => array(
		'default'
		)
);

include STEELBOT_DIR.'/bot.php';
