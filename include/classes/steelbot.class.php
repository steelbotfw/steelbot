<?php

/**
 * SteelBot class for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 1.2
 * 
 * 2008-08-25
 *
 */

class SteelBot extends SteelBotCore {

static  public $database,
                $msgdropped = false,
               $lng ;
                
static public   $cmdlist = array();                
                
static function Init($cfg) {
    echo "Initializing database ... "; 
    self::$database = new SteelBotDB();
    echo "OK\n\n";
    
    parent::$cfg = $cfg;
  
    if (parent::$cfg['save_actual_timers']) {
        self::LoadTimers(parent::$cfg['timers_file']);
        
        parent::RegisterEventHandler(EVENT_EXIT, array('SteelBot', 'SaveTimers'));
    }
    
    // загрузка плагинов
    parent::$next_timer = time() * 2;

    
    self::LoadPlugin(parent::$cfg['plugin_dir']);
    // устанавлиаем все плагины
    $pattern = parent::$cfg['plugin_dir'].
               DIRECTORY_SEPARATOR."*"; 
    
    $plugdir = glob($pattern, GLOB_ONLYDIR);
    foreach ($plugdir as $v) {
        self::LoadPlugin( $v );
    }
    
    // commands accesses levels
    $commands_list = STEELBOT_DIR.'/tmp/commands.access';
    if ( is_readable( $commands_list ) ) {
        $newlevels = unserialize( file_get_contents( $commands_list ) );
        foreach ($newlevels as $cmd=>$level) {
            if (array_key_exists($cmd, self::$cmdlist)) {
                self::$cmdlist[$cmd][0] = $level;
            }
        }
    }
    
    // i18n
    self::$lng = new SteelBotLng( parent::$cfg['language'] );
    self::$lng->AddDict( STEELBOT_DIR.'/include/lang/'.parent::$cfg['language'].'.php' );
    
}

static function SaveTimers() {
    echo "Saving timers ... ";
    if ( file_put_contents( parent::$cfg['timers_file'], serialize(parent::$timers)) ) {
        echo "OK\n";
    } else {
        echo "ERROR\n";
    }
}

static function SaveCommandsAccesses() {
    $commands_list = STEELBOT_DIR.'/tmp/commands.access';
    $accesses = array();
    foreach (self::$cmdlist as $name=>$cm) {
        $accesses[$name] = $cm[0];
    }
    $result = file_put_contents($commands_list, serialize($accesses));
    return $result;
}

static function LoadTimers($filename) {
    $now = time();
    echo 'Loading timers ... ';
    if ( is_readable($filename) ) {
        $timers = @unserialize( $filename );
        if ( is_array($timers) ) {
            foreach ($timers as $time=>$funcs) {
                if (time() < $time) {
                    foreach ($funcs as $func) {
                        parent::TimerAdd($time-time(), $func);
                    }
                } else {
                    echo "\n   timer skipped: ($time) ";
                }
            }
            echo "OK\n";
        } else {
            echo "no timers\n";
        }
        
    } else {
        echo " OK\n";
    }
            
}

static function LoadPlugin($dir) {
    $pattern = $dir.
               DIRECTORY_SEPARATOR."*.plugin.php"; 
    
    $pluglist = glob($pattern);
    foreach ($pluglist as $v) {
        $shortname = str_replace('.plugin.php','',basename($v));
        echo "Loading plugin: $shortname ... ";
        include_once($v);
    
        parent::$plugins[] = $shortname;
        slog::add('', "$shortname", LOG_PLUGIN_LOAD);
        parent::EventRun(EVENT_PLUGIN_LOADED, $shortname);
        echo "OK\n";
    }    
}

static function LoadPluginByName($name) {
    $name = str_replace('.plugin.php', '', $name);
    if (array_key_exists($name, parent::$plugins)) {
        return false;
    } else {
        $fullname = parent::$cfg['plugin_dir']."/$name.plugin.php";
        echo "Loading plugin: $name ... ";
        if (is_readable($fullname)) {
            include_once($fullname);
    
            parent::$plugins[] = $name;
            slog::add('', "$name", LOG_PLUGIN_LOAD);
            parent::EventRun(EVENT_PLUGIN_LOADED, $name);
            echo "OK\n";   
            return true;
        } else {
            echo "ERROR\n";
            return false;
        }
    }
}

static function Msg($text, $to = false) {
    if (!$to) {
        $to = parent::$sender;        
    }
    Proto::Msg($text, $to);
    slog::add('', "[>$to ".$text, LOG_MSG_SENT);
}
    
static function GetUin() {
	return parent::$sender;
}

static function GetMsgText() {
	return parent::$content;
}       
       
/**
 * @desc Отправляет сообщение со справкой по указанной команде $cmd
 *
 * @param string $cmd - имя команды
 */
static function CmdHelp($cmd) {
    if (array_key_exists($cmd, self::$cmdlist)) {
        Proto::Msg(self::$cmdlist[$cmd][2]);
    } else {
		Proto::Msg( LNG( LNG_HELP_NOTFOUND, $cmd ) );
	}
}

/**
 * @desc Регистрирует пользовательскую команду в системе.
 *
 * @param string $command - имя команды, которое отправляет пользователь,
 * без командного символа
 * @param string $func - функция, которая будет вызвана при получении этой команды
 * @param int $access - уровень доступа к команде
 * @param string $helpstr - текст, который будет отправляться при обращении
 * к помощи по данной команде
 * 
 * @return REG_CMD_ALREADY_DEFINED - команда уже определена ранее
 *         REG_CMD_OK - команда зарегистрирована
 */
static function RegisterCmd($command,$func,$access = 1,$helpstr = false) {
    if (!parent::$cfg['msg_case_sensitive']) {
        $command = mb_strtolower($command, 'utf-8');
    }
	if (!is_numeric($access)) {
	    $access = 1;
	}
	
	if (array_key_exists($command, self::$cmdlist)) {
	    slog::add('', $command. ' => '.func2str(self::$cmdlist[$command][1]), LOG_CMD_ALREADY_REG );
		return REG_CMD_ALREADY_DEFINED;
		
	} else {
	    
	if (!$helpstr) {
	    $helpstr = LNG(LNG_NOHELP);
	}
	    
	
	
	self::$cmdlist[$command][0] = $access;
	self::$cmdlist[$command][1] = $func;
	self::$cmdlist[$command][2] = $helpstr;
	slog::add('', $command. ' => '.func2str($func), LOG_CMD_REGISTER);
	return REG_CMD_OK;
	}
}

/**
 * @desc Удаляет пользовательскую команду
 *
 * @param string $command - имя команды
 * @return bool - true, если команда удалена
 *                false, если команда не зарегистрирована
 */
static function UnregisterCmd($command) {
    if (array_key_exists($command,self::$cmdlist)) {
        unset(self::$cmdlist[$command]);
        slog::add('', $command, LOG_CMD_UNREGISTER);
        return true;
    } else {
        return false;
    }
}

static function DropMsg() {
    self::$msgdropped = true;
}

/**
 * @desc Устанавливает уровень доступа к указанной пользовательской команде.
 *
 * @param unknown_type $cmd
 * @param unknown_type $level
 * @return bool - true, если уровень установлен
 *                false,если получен некорректный уровень доступа или
 *                команда не найдена 
 */
static function SetCmdAccess($cmd,$level) {
	if (!is_numeric($level)) {
	    return false;
	} elseif (array_key_exists($cmd,self::$cmdlist)) {
		self::$cmdlist[$cmd][0] = $level;
		return true;
	} else return false;
}

static function SetOption($option, $value) {
    if (array_key_exists($option, parent::$cfg) ){
        parent::$cfg[$option] = $value;
        return true;
    } else {
        return false;
    }
}

/**
 * @desc Если не задан параметр, выводит список команд. Если параметр задан,
 * то отправляет помощь по указанной команде $val
 *
 * @param string $val параметр-команда
 */
static function help() {
    list($command, $val) = explode(' ', self::$content, 2);
	if (empty($val)) {
            
	        $level = self::GetUserAccess();
	        $helpstr = LNG(LNG_HELPCOMMANDS)."\n";
	        
	        if (parent::$cfg['help_detailed']) {
	           foreach (self::$cmdlist as $name=>$cmd) {
	               if ( ($cmd[0] <= $level) && ($cmd[0] > 0)) {
	                   $helpstr .= $cmd[2]."\n"; 
	               }
	           }
	                
	        } else {
	           $commands = array();
	           foreach (self::$cmdlist as $cmd) {
	               if ( ($cmd[0] <= $level) && ($cmd[0] > 0)) {
	                   $commands[] = $name;
	               }
	           }
	           $helpstr .= implode(", ", $commands);
	        }
	        
	        $helpstr .= "\n".parent::$cfg['help_ps'];
	
	        Proto::Msg($helpstr);
	} else {
	   self::CmdHelp($val); 
	}
}

/**
 * @desc Проверяет, является ли строка правильной записью UIN
 *
 * @param string $uin - UIN
 * @return boolean
 */
static function is_uin($uin) {
    return (is_numeric($uin) && (strlen($uin) < 10) && (strlen($uin) > 4));
}

/**
 * @desc Установливает определенному UIN уровень доступа к боту
 *
 * @param string $user - UIN
 * @param int $level - уровень доступа от 1 до 100 
 * @return boolean
 */
static function SetUserAccess($user,$level) {
    if (!self::is_uin($user)) {
        
        return false;
    } elseif (!is_numeric($level) || $level > 100) {
        
        return false;
    } else {
        self::$database->SetUserAccess($user, $level);
        return true;
    }    
}

/**
 * @desc Возвращает уровень доступа, установленный для данного UIN. Если UIN не
 * указан, то возвращает уровень доступа приславшего сообщение.
 *
 * @param  string $uin - UIN
 * @return int
 */
static function GetUserAccess($uin = false) {
    if (!self::is_uin($uin)) {
        $uin = self::GetUin();
    }  
    
    if ( is_array(parent::$cfg['master_uin']) && in_array($uin, self::$cfg['master_uin']) ) {
        return 100; 
    } elseif ($uin == parent::$cfg['master_uin']) {
        return 100;
    }
    
    if ( ($access = self::$database->userAccess($uin)) !== false) {
        return $access;
    } else {
        return 1;
    }
}

/**
 * @desc Анализирует и исполняет присланную пользовательскую команду
 *
 * @return unknown
 */
static function Parse() {
    list($command, $params) = explode(' ', parent::$content, 2);
    if (!parent::$cfg['msg_case_sensitive']) {
        $command = mb_strtolower($command, 'utf-8');
    }
	if (!array_key_exists($command,self::$cmdlist)) {
        self::Msg(parent::$cfg['err_cmd']);
		return false;
		    
	} elseif (self::$cmdlist[$command][0] > self::GetUserAccess()){
	    self::Msg( LNG(LNG_CMDNOACCESS) );
	        
	} else {
	    call_user_func(self::$cmdlist[$command][1], $params);
	}
	return true;    
}

static function Connect() {
    if ($p = Proto::Connect(parent::$cfg['bot_uin'], parent::$cfg['bot_password']) ) {
        slog::add('', "Connected", LOG_CONNECTED);
        parent::EventRun(EVENT_CONNECTED);    
    } else {
        slog::add('', "Connection error", LOG_CONNECTION_ERROR);
    }
    return $p;
}

static function Disconnect() {
    Proto::Disconnect();
    slog::add('', "Disconnected", LOG_DISCONNECTED);
}

static function Connected() {
    return Proto::Connected();
}

static function ParseMessage() {
    self::$msgdropped = false;
    $message = Proto::GetMessage();
    if ($message) {
 	  parent::$sender = $message[0];
 	  parent::$content = trim($message[1]);
 	  slog::add('', parent::$content, LOG_MSG_RECIEVED, parent::$sender);
 	  parent::EventRun(EVENT_MSG_RECIEVED);
 	  if (!self::$msgdropped) {
 	      self::Parse();
 	  }
 	  sleep(SteelBot::$cfg['delaylisten']); 
    }
}

static function Error() {
    return Proto::Error();
}

static function DeleteLockFile($uin = false) {
    if (!$uin) $uin = parent::$cfg['bot_uin'];
    $filename = dirname(__FILE__)."/tmp/$uin.lock";
    if (file_exists($filename)) {
        return @unlink( $filename );
    } else {
        return true;
    }
}

}