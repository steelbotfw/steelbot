<?php

/**
 * ISteelbotProtocol interface for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 1.0
 * 
 * 2008-08-05
 * 
 */

/**
 * Интерфейс доступа к протоколу ICQ
 *
 * Для работы бота необходимо и достаточно наличия этих функций.
 * 
 */
interface ISteelBotProtocol {

/**
 * Подключиться к серверу
 *
 * @param string $uin - UIN
 * @param string $password - пароль
 * 
 * @return bool
 */
static function Connect($uin,$password);

/**
 * Отключиться от сервера
 *
 */
static function Disconnect();

/**
 * Проверить, находится ли UIN в онлайне
 *
 * @return bool
 */
static function Connected();

/**
 * Получить пришедшее сообщение
 *
 * Функция должна присвоить переменным
 *  SteelBotCore::$uin - UIN, с которого пришло сообщение
 *  SteelBotCore::$text - текст сообщения
 * 
 * @return bool
 */
static function GetMessage();

/**
 * Какая-либо информация в случае возникновения ошибки.
 * 
 * Функция используется только для вывода информации на экран,
 * поэтому может быть пустой.
 * 
 * @return mixed info - любая информация
 *
 */
static function Error();

/**
 * Послать сообщение
 *
 * @param string $txt - текст сообщения
 * @param string $touin - UIN, на который надо отсылать
 *                        сообщение. Если передается
 *                        false, то сообщение должно
 *                        быть отправлено приславшему команду.          
 */
static function Msg($txt,$touin = false);

/**
 * Установить статус бота
 *
 * @param $status - какой статус поставить (может меняться в зависимости
 *                  от реализации протокола)
 */
static function SetStatus($status);


/**
 * Установить X-статус бота
 *
 * @param $status - какой статус поставить (может меняться в зависимости
 *                  от реализации протокола)
 */
static function SetXStatus($status);

}