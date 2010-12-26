<?php

/**
 * ISteelBotLog interface for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 2.0
 * 
 * 
 */

interface ISteelBotLog {
       
    static function add($name, $msg, $code = '000', $sender = false, $level = LOG_LEVEL_NOTICE);
    
    static function result($res);
    
    static function save();
    
}
