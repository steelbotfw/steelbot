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

class slog implements ISteelBotLog {
    
   static $buffer = array(),
          $buffer_size = 30;
    
   static function add($name, $msg, $code = '000', $sender = false) {
       if (in_array($code, SteelBot::$cfg['log']['exclude_types'])) {
           return;
       }
       
       $date = date( SteelBot::$cfg['log']['dateformat'] );       
       $logmsg = str_replace( array('%d', '%u', '%m', '%n', '%с'),
                              array($date, $sender, $msg, $name, $code), 
                                SteelBot::$cfg['log']['msgformat']
                            );
                            
       self::$buffer[] = $logmsg;
       //echo $logmsg."\n";
       
       if ( count (self::$buffer) > self::$buffer_size) {
           self::save();
           
           self::$buffer = array();
       }
       
   }
   
   static function save() {
       $filename = STEELBOT_DIR."/logs/".date(SteelBot::$cfg['log']['filename_format']).".log";
       if ($f = fopen($filename, "a+") ) {
           echo "[logger] saving log... ";
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

