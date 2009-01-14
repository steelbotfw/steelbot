<?php

/**
 * Логирование сообщений для SteelBot
 * 
 * @author N3x^0r
 * @version 1.0
 * 
 * 2008-08-11
 *
 */

include_once dirname(__FILE__)."/../const/log_codes.php";
include_once dirname(__FILE__)."/../interfaces/isteelbotlog.interface.php";

class slog implements ISteelBotLog {
    
   static $buffer = array(),
          $buffer_size = 30;
    
          
   static function add($name, $msg, $code = '000', $sender = false) {
       if (in_array($code, SteelBot::$cfg['log']['exclude_types'])) {
           return;
       }       
       
       $date = date( SteelBot::$cfg['log']['dateformat'] );       
       $logmsg = self::format($date, $sender, $msg, $name, $code);

       if ( count (self::$buffer) > self::$buffer_size) {
           self::save();           
           self::$buffer = array();
       }      
       self::$buffer[] = $logmsg;
       
       $logmsg = self::format($date, $sender, $msg, $name.@str_repeat(' ', 8-strlen($name)), '');
       echo "\n".$logmsg;      
       
   }
   
   static function format($date, $name, $msg, $code, $sender) {
       return str_replace( array('%d', '%u', '%m', '%n', '%с'),
                              array($date, $sender, $msg, $name, $code), 
                                SteelBot::$cfg['log']['msgformat'] );
   }
   
   static function result($res) {       
       $index = count(self::$buffer);
       if ($index>0) {
           $index--;         
           self::$buffer[$index] = self::$buffer[$index].$res;
           echo $res;
         
       }
   }
   
   static function save() {
       $filename = STEELBOT_DIR.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.
        SteelBot::$cfg['bot_uin'].'.'.date(SteelBot::$cfg['log']['filename_format']).".log";
       if ($f = fopen($filename, "a+") ) {
           $date = date( SteelBot::$cfg['log']['dateformat'] ); 
           $msg = "Saving log... ";      
           $logmsg = self::format($date, 'logger', $msg, ' ', ' ');
           echo "\n".$logmsg;
           
           foreach (self::$buffer as $msg) {
                   fputs($f, $msg."\n");
           }
           fclose($f);
		   echo "OK\n";
               
       } else {
               echo "[logger] error: can't open $filename\n";
       }    
   }
    
}

