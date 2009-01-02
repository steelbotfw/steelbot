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

static public  $database,
               $msgdropped = false,
               $lng,
               $sender,
               $content,
               $current_plugin = null;               
                
static public   $cmdlist = array();                
                
static function Init($cfg) {
    slog::add('steelbot',"Initializing database ... "); 
    self::$database = new SteelBotDB();
    slog::result("OK");
    
    parent::$cfg = $cfg;
  
    if (parent::$cfg['save_actual_timers']) {
        self::LoadTimers(parent::$cfg['timers_file']);
        
        parent::RegisterEventHandler(EVENT_EXIT, array('SteelBot', 'SaveTimers'));
    }
    
    
    parent::$next_timer = time() * 2;

    // загрузка плагинов
    self::LoadPlugins(parent::$cfg['plugin_dir']);
    foreach (self::$plugins as $name=>$plug) {
        self::CheckDependency($name);    
    }
    
    
    // commands accesses levels
    $commands_list = STEELBOT_DIR.'/tmp/commands.access';
    if ( is_readable( $commands_list ) ) {
        $newlevels = unserialize( file_get_contents( $commands_list ) );
        foreach ($newlevels as $cmd=>$level) {
            if (array_key_exists($cmd, self::$cmdlist)) {
                self::$cmdlist[$cmd]->SetAccess($level);
            }
        }
    }
    
    // i18n
    self::InitLang();
    
}

static function InitLang($lang = null) {
    if (!$lang) {
        $lang = parent::$cfg['language'];
    } elseif (strlen($lang) > 2) {
        $lang = substr($slng, 0,2);
    }
    
    self::$lng = new SteelBotLng( $lang );
    self::$lng->AddDict( STEELBOT_DIR.'/include/lang/'.$lang.'.php' );

}

static function SaveTimers() {
    slog::add('steelbot',"Saving timers ... ");
    if ( file_put_contents( parent::$cfg['timers_file'], serialize(parent::$timers)) ) {
        slog::result("OK");
    } else {
        slog::result("ERROR");
    }
}

static function SaveCommandsAccesses() {
    $commands_list = STEELBOT_DIR.'/tmp/commands.access';
    $accesses = array();
    foreach (self::$cmdlist as $obj) {
        $name = $obj->GetName();
        $accesses[$name] = $obj->GetAccess();
    }
    $result = file_put_contents($commands_list, serialize($accesses));
    return $result;
}

static function LoadTimers($filename) {
    $now = time();
    slog::add('steelbot', 'Loading timers ... ');
    if ( is_readable($filename) ) {
        $timers = @unserialize( $filename );
        if ( is_array($timers) ) {
            foreach ($timers as $time=>$funcs) {
                if (time() < $time) {
                    foreach ($funcs as $func) {
                        parent::TimerAdd($time-time(), $func);
                    }
                } else {
                    slog::add('steelbot', "   timer skipped: ($time) ");
                }
            }
            slog::result("OK");
        } else {
            slog::result("no timers");
        }
        
    } else {
        slog::reswult("OK");
    }
            
}

static function LoadPlugins($dir) {
    $pattern = $dir.
               DIRECTORY_SEPARATOR."*.plugin.php"; 
    
    $pluglist = glob($pattern);
    foreach ($pluglist as $v) {
        
            try {
                self::LoadPluginByName($v);
            } catch (BotException $e) {
                slog::add('steelbot','Exception: '.$e->getMessage(), LOG_PLUGIN_LOAD);
            }
        
    } 
    
    foreach (glob($dir.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR) as $d) {
        self::LoadPlugins($d);
    }
}

static function LoadPluginByName($name) {
    $name = str_replace('.plugin.php', '', basename($name) );
    if (is_dir(self::$cfg['plugin_dir'].DIRECTORY_SEPARATOR.$name)) {
        $filename = self::$cfg['plugin_dir'].DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.$name.'.plugin.php';
    } else {
        $filename = self::$cfg['plugin_dir'].DIRECTORY_SEPARATOR.$name.'.plugin.php';
    }
    if (array_key_exists($name, parent::$plugins)) {
        throw new BotException(("Plugin $name already exists"));
    } else {
        slog::add('steelbot', "Loading plugin: $name ... ");
        $plug = new Plugin($filename);
        
        try {            
            self::$current_plugin = $plug;
                $plug->Load();
                self::CheckDependency($name);
                parent::$plugins[$name] = $plug;
                slog::add('steelbot', "'$name' load OK", LOG_PLUGIN_LOAD);
                parent::EventRun( new Event(EVENT_PLUGIN_LOADED, array('name'=>$name)));
                   
            self::$current_plugin = null;
            return true;
        } catch (BotException $e) {
            throw $e;
        }
    }
}

static function Msg($text, $to = false) {
    if (!$to) {
        $to = self::$sender;        
    }
    $ev = parent::EventRun( new Event(EVENT_MSG_SENT, array('text'=>$text, 'to'=>$to)) );
    Proto::Msg($ev->text, $ev->to);
    slog::add('steelbot', "[>$to ".$text, LOG_MSG_SENT);
}
    
static function GetUin() {
	return self::$sender;
}

static function GetMsgText() {
	return self::$content;
}       
       
/**
 * @desc Отправляет сообщение со справкой по указанной команде $cmd
 *
 * @param string $cmd - имя команды
 */
static function CmdHelp($cmd) {
    if (array_key_exists($cmd, self::$cmdlist)) {
        self::Msg( self::$cmdlist[$cmd]->GetHelp() );
    } else {
		self::Msg( LNG( LNG_HELP_NOTFOUND, $cmd ) );
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
static function RegisterCmd($command, $func, $access = 1, $helpstr = false) {
    if ($command instanceof BotCommand) {
        $name = mb_strtolower( $command->GetName() );
        if ( !array_key_exists($name, self::$cmdlist)) {
            self::$cmdlist[$name] = $command;    
        } else {
           slog::add('steelbot', $command. ' => '.func2str(self::$cmdlist[$command]->GetName()), LOG_CMD_ALREADY_REG );
           return REG_CMD_ALREADY_DEFINED; 
        }
        
            
    } else {
       if (!parent::$cfg['msg_case_sensitive']) {
            $command = mb_strtolower($command, 'utf-8');
       }
	   if (!is_numeric($access)) {
	        $access = 1;
	   }
	
	if (array_key_exists($command, self::$cmdlist)) {
	    slog::add('steelbot', $command. ' => '.func2str(self::$cmdlist[$command]->GetName()), LOG_CMD_ALREADY_REG );
		return REG_CMD_ALREADY_DEFINED;
		
	} else {
	    
	    if (!$helpstr) {
	        $helpstr = LNG(LNG_NOHELP);
	    }
	    
	
	    $obj = new BotCommand($command, $func, $access, $helpstr);
	    self::$cmdlist[ $command ] = $obj;
		
	    }
    }
    if (self::$current_plugin != null) {
        self::$current_plugin->AddCommand($command);
    }
    slog::add('steelbot', $command. ' => '.func2str($func), LOG_CMD_REGISTER);
	return REG_CMD_OK;
}

static function AddDependence($dep, $maj_ver=99, $min_ver=99, $type='plugin') {
    if (self::$current_plugin == null) {
        return;
    } else {
        self::$current_plugin->AddDependence($dep, $maj_ver, $min_ver, $type);
    }
    
    
}

static function CheckDependency($plugin) {
    if (array_key_exists($plugin, self::$plugins)) {
        slog::add('steelbot', "Checking for dependencies $plugin... ");
        $plug_dep = self::$plugins[$plugin]->GetDependencies('plugin');
        foreach ($plug_dep as $dep) {
            if ( array_key_exists($dep['dep'], self::$plugins) ) {
                $info = self::$plugins[$dep['dep']]->GetInfo();
                if ( ($info['major_ver'] >= $dep['major_ver']) ||
                     ($info['minor_ver'] >= $dep['minor_ver']) ) {
                         continue;
                     }
            }
            
            if ( ($dep['major_ver'] == 99) && ( $dep['minor_ver'] ) == 99 ) {
                    $ver = '';
            } else {
                    $ver = "{$dep['major_ver']}.{$dep['minor_ver']}";
            }
            throw new BotException("Plugin $name require {$dep['dep']} $ver plugin", ERR_DEPENDENCY);
            
        }    
        slog::result("OK");
    }
}

static function ExportInfo($name, $major_ver, $minor_ver, $author) {
    if (self::$current_plugin != null) {
        $info = array(
            'name' => $name,
            'author' => $author,
            'major_ver' => $major_ver,
            'minor_ver' => $minor_ver
        );
        self::$current_plugin->ExportInfo($info);
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
        slog::add('steelbot', $command, LOG_CMD_UNREGISTER);
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
	    throw new BotException("$level is not a numeric value", ERR_NOT_NUMERIC);
	} elseif (array_key_exists($cmd,self::$cmdlist)) {
		self::$cmdlist[$cmd]->SetAccess( $level );
		return true;
	} else throw new BotException("Command does not exists", ERR_CMD_NOTFOUND);
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
	        $helpstr = array();;	        

	        foreach (self::$cmdlist as $cmd) {	            
	            $helpstr[] = $cmd->GetHelp(1); 
	        }
	
	        self::Msg( LNG(LNG_HELPCOMMANDS)."\n".implode(', ',$helpstr) );
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
    list($command, $params) = explode(' ', self::$content, 2);
    if (!parent::$cfg['msg_case_sensitive']) {
        $command = mb_strtolower($command, 'utf-8');
    }
	if (!array_key_exists($command,self::$cmdlist)) {
        self::Msg(parent::$cfg['err_cmd']);
		return false;
		    
	} else {
	    try {
	        self::$cmdlist[$command]->Execute( $params );
	        
	    } catch (BotException $e) {
	        slog::add('steelbot', $e->getMessage(), LOG_EXCEPTION);
	        switch ($e->getCode()) {
	            case ERR_CMD_ACCESS:
	                self::Msg( LNG(LNG_CMDNOACCESS) );
	                break;
	                
	            case ERR_FUNC:
	                break;
	        }
	    }
	}
	return true;    
}

static function Connect() {
    if ($p = Proto::Connect(parent::$cfg['bot_uin'], parent::$cfg['bot_password']) ) {
        slog::add('steelbot', "Connected", LOG_CONNECTED);
        parent::EventRun( new Event(EVENT_CONNECTED) );    
    } else {
        slog::add('steelbot', "Connection error", LOG_CONNECTION_ERROR);
    }
    return $p;
}

static function Disconnect() {
    Proto::Disconnect();
    slog::add('steelbot', "Disconnected", LOG_DISCONNECTED);
}

static function Connected() {
    return Proto::Connected();
}

static function ParseMessage() {
    self::$msgdropped = false;
    $message = Proto::GetMessage();
    if ($message) {
 	  self::$sender = $message[0];
 	  self::$content = trim($message[1]);
 	  slog::add('steelbot', self::$content, LOG_MSG_RECIEVED, self::$sender);
 	  $ev = parent::EventRun( new Event(EVENT_MSG_RECIEVED, 
 	      array('sender' => $message[0], 'content'=>trim($message[1]) ) ));
 	  self::$sender = $ev->sender;
 	  self::$content = $ev->content;
 	  
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
    $filename = STEELBOT_DIR."/tmp/$uin.lock";
    $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
    slog::add('steelbot', "Deleting lock file $filename ..");
    if (file_exists($filename)) {
        return @unlink( $filename );
    } else {
        return true;
    }
}

static function CheckLockFile($uin = false) {
    if (!$uin) $uin = parent::$cfg['bot_uin'];
    $filename = STEELBOT_DIR."/tmp/$uin.lock";
    $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
    return file_exists($filename);    
}

static function CreateLockFile($uin = false) {
    if (!$uin) $uin = parent::$cfg['bot_uin'];
    $filename = STEELBOT_DIR."/tmp/$uin.lock";
    $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
    return file_put_contents( $filename, '1' );
}

}