<?php

/**
 * ISteelBotDB interface for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 1.0
 * 
 * 2008-08-12
 * 
 */

interface ISteelBotLog {
       
    static function add($name, $msg, $code = '000', $sender = false);
    
    static function save();
    
}