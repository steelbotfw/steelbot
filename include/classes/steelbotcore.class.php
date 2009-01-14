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

class SteelBotCore {

static $next_timer,
       $timers,
       $events = array(),
       $plugins = array(),
       $cfg;    

// event functions
static function RegisterEventHandler($event_type, $func) {    
    self::$events[$event_type][] = $func;
}

static function EventRun(Event $event) {
    $code = $event->GetCode();
    if (array_key_exists($code, self::$events)) {
        foreach (self::$events[$code] as $func) {
            call_user_func($func, $event);
        }
    }
    return $event;
}



// plugin functions
static function PluginLoaded($plugin) {
    return array_key_exists($plugin, self::$plugins);
}

/**
 * @desc Проверяет, зарегистрирована ли указанная пользовательская команда
 *
 * @param string $command
 * @return bool
 */
static function CommandExists($command) {
    return array_key_exists($command, self::$cmdlist);
}


// timer functions

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


/**
 * @desc Последовательно вызывает все выходные функции, а затем завершает работу
 * скрипта.
 *
 */
static function DoExit() {    
    self::EventRun( new Event(EVENT_EXIT) );
    exit;
}


}
