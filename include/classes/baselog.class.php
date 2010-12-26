<?php

/**
 * Логирование сообщений для SteelBot
 * 
 * @author N3x^0r
 * @version 1.0
 * 
 * 2010-04-25
 *
 */

include_once dirname(__FILE__)."/../interfaces/isteelbotlog.interface.php";

class BaseLog {
    
   private $last_ignored = false;

   public function __construct($bufferSize = 30) {
		$this->log("Logger ".__CLASS__." started at ".date("r"));
		set_error_handler(array($this, 'errorHandler'));
   }   

   /**
    * @deprecated
    */
   public function add($name, $msg, $code = '000', $sender = false, $level = LOG_LEVEL_NOTICE) {
        if (!$this->checkRule($name, $level, $code)) {
		    $this->last_ignored = true;
            return;
        }
        $this->last_ignored = false;
       
        $date = date( SteelBot::$cfg['log.dateformat'] );       
        $logmsg = $this->format($date, $sender, $msg, $name, $code);
       
        if (strlen($name) < 8) {
		   $offset = 8-strlen($name);
	    } else {
	       $offset = 1;
	    }
        $logmsg = $this->format($date, $sender, $msg, $name.@str_repeat(' ', $offset), '');
        echo "\n".$logmsg;
        return true;  
    }

	/**
	 * @param string $msg
	 * @param string $component
	 */
    public function log($msg, $component = null, $level = LOG_LEVEL_NOTICE) {
		echo date("[H:i:s] ");
		if (!is_null($component)) {
			echo "[$component] ";
		}
		echo $msg."\n";
    }

    /**
     * @param srting $date
     * @param string $name
     * @param string $msg
     * @param string $code
     * @param string $sender
     */
    public function format($date, $name, $msg, $code, $sender) {	
        return str_replace( array('%d', '%u', '%m', '%n', '%c'),
                               array($date, $sender, $msg, $name, $code), 
                                 SteelBot::$cfg['log.msgformat'] );
    }

    /**
     * @param string $res
     */
    public function result($res) {       
	    if ($this->last_ignored) return;
        echo $res;
        return true;
    }

    public function errorHandler($error_level, $error_message, $error_file, $error_line) {
		static $levels = array(
			E_USER_ERROR => array('E_USER_ERROR',LOG_LEVEL_ERROR),
			E_ERROR => array('E_ERROR',LOG_LEVEL_ERROR),
			E_USER_WARNING => array('E_USER_WARNING', LOG_LEVEL_WARNING),
			E_WARNING => array('E_WARNING', LOG_LEVEL_WARNING),
			E_USER_NOTICE => array('E_USER_NOTICE', LOG_LEVEL_NOTICE),
			E_NOTICE => array('E_NOTICE', LOG_LEVEL_NOTICE)
		);

		if (isset($levels[$error_level])) {
			$this->log(
				"{$levels[$error_level][0]} in $error_file, line $error_line: $error_message",
				'PHP',
				$levels[$error_level][1]
			);
		} else {
			$this->log(
				"unknown error($error_level) in $error_file, line $error_line: $error_message",
				'PHP',
				LOG_LEVEL_ERROR
			);
		}
   }

    private function checkRule($name, $level, $code = '000') {
	    if (in_array($code, SteelBot::$cfg['log.exclude_types'])) {
            return false;
        } elseif (isset(SteelBot::$cfg['log.rules'][$name]) ) {
			if (SteelBot::$cfg['log.rules'][$name] < $level) {
				return false;
			} else {
				return true;
			}
		} elseif (SteelBot::$cfg['log.rules']['*'] < $level) {
			return false;
		}
		return true;
   }   
}
