<?php

/**
 * Файл настроек.
 * 
 * Сделайте необходимые изменения в настройках для запуска бота.
 * Для того чтобы бот заработал, достаточно изменить 'bot_uin',
 * 'bot_password' и 'master_uin' на нужные UIN,пароль и UIN
 * администратора соответственно.
 * 
 * @version 1.2
 * 
 * 2008-08-22
 * 
 */

$cfg = array(            			  
			  //UIN
			  'bot_uin'       => 333333334,
			  
			  //пароль от уина
			  'bot_password'  => 'p1a2s3s',		  

			  // номер администратора бота (или несколько номеров через запятую, 
			  // например array(321742) 
			  'master_uin'    => array( ),
			  
			  //директория с плагинами( абсолютный путь )
			  'plugin_dir'    => dirname(__FILE__).'/plugins',
			  
			  //интервал прослушки сокета, рекомендуется 1
			  'delaylisten'   =>1,
			  
			  //максимальное количество попыток подключения
			  'connect_attempts' => 5,
			  
			  //сообщение пользователю, если введена не команда
			  'err_cmd' => 'Команда не найдена. Для получения помощи наберите !help',
			  
			  // кодировка отправляемых сообщений
			  'msg_charset' => 'windows-1251',
			  
			  // кодировка входящих сообщений
			  'msg_charset_in' => 'windows-1251',
			  
			  // чувствительность команд к регистру
			  'msg_case_sensitive' => false,
			  
			  'log' => array (
			  
			     // формат даты в логах
			     // переменные: см. http://php.net/date
			     'filename_format' => 'd_M_Y',
			     
			     // формат даты в логах
			     // переменные: см. http://php.net/date
			     'dateformat' => 'H:i:s',
			     
			     /**
			      * формат сообщения лога
			      * 
			      * переменные:
			      *     %d - дата и время (см. опцию log_dateformat)
			      *     %u - UIN, отправивший сообщение 
			      *     %m - сообщение лога
			      *     %n - группа записи (например, название плагина)
			      *     %c - код сообщения
			      * 
			      */
			     'msgformat' => '%d %с %n <%u> %m',
			     
			     'exclude_types' => array(LOG_MSG_SENT)
			  
			  ),
			  
			  // подробная справка по командам
			  'help_detailed' => 1,
			  
			  // дополнительный текст после показа помощи
			  'help_ps' => 'Официальный сайт бота: http://steelbot.net',
			  
			  'save_actual_timers' => true,
			  'timers_file' => dirname(__FILE__)."/tmp/timers",
			  
			  'autoinclude_file' => dirname(__FILE__)."/autorun.php",
			  
			  'language' => 'ru',
			  
			  'web_password' => 'steelbot'
            );
          
