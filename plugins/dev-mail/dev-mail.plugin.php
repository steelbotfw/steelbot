<?php

include dirname(__FILE__).'/mail.class.php';

$mail = new Mail('n3xorus@gmail.com', 'голосвание', 'тестовое сообщение', 'от кого');
$r = $mail->send();
var_dump($r);
die;
