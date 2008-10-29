<?php

/**
 * SteelBotCore class for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 1.2
 * 
 * 2008-08-22
 *
 */

define('EVENT_CONNECTED',    0x001);
define('EVENT_MSG_RECIEVED', 0x002);
define('EVENT_MSG_SENT',     0x004);
define('EVENT_DISCONNECTED', 0x006);
define('EVENT_EXIT',         0x007);
define('EVENT_RECONNECT',    0x008);
define('EVENT_PLUGIN_LOADED',0x009);


class SteelBotCore {

static $next_timer,
       $timers,
       $events = array(),
       $plugins,
       $objects,
       $content,
       $sender,
       $cfg;    


/**
 * @desc Проверяет, зарегистрирована ли указанная пользовательская команда
 *
 * @param string $command
 * @return bool
 */
static function CommandExists($command) {
    return array_key_exists($command, self::$cmdlist);
}

/**
 * @desc Записывает вызов заданной через заданное время
 *
 * @param int $time - время в секундах, по прохождении которого будет вызвана
 * функция
 * @param string $func - имя функции
 * @return true
 */
static function TimerAdd($time,$func) {
    $time = time()+$time;
    self::$timers[$time][] = $func;
    self::$next_timer = min(array_keys(self::$timers));        
    return true;
}

static function AddObject($object) {
    return self::$objects[] = $object;
}

static function PluginLoaded($plugin) {
    return array_key_exists($plugin, self::$plugins);
}

/**
 * @desc Вызывает все функции, время вызова которых меньше чем заданное (т.е.
 * вызывает все функции, которые к моменту времени $time должны были выполнится,
 * но не выполнились
 *
 * @param int $time - время в формате UNIX
 */
static function TimerRun($time) {    
    foreach ( array_keys(self::$timers) as $v) {
        if ($v < $time) {            
            foreach (self::$timers[$v] as $vv) {
      
                call_user_func($vv);
            }
            unset(self::$timers[$v]); 
        }
    }   
}

static function SyncTimers() {
    foreach (self::$timers as $k=>$v) {
        if (count($v) < 1) {
            unset(self::$timers[$k]);
        }
    }
    self::$next_timer = min(array_keys(self::$timers));    
}

static function RegisterEventHandler($event_type, $func) {    
    self::$events[$event_type][] = $func;
}

static function EventRun($event_type) {
    if (array_key_exists($event_type, self::$events)) {
        foreach (self::$events[$event_type] as $func) {
            call_user_func($func);
        }
    }
}

/**
 * @desc Последовательно вызывает все выходные функции, а затем завершает работу
 * скрипта.
 *
 */
static function DoExit() {    
    self::EventRun(EVENT_EXIT);
    exit;
}


}
