<?php

class CommandManager extends SComponent {

	protected $commands = array(),
              $aliases = array();
	
	public function __construct($bot) {
		parent::__construct($bot);
	}

    public function getAliases() {
        return array_keys($this->aliases);
    }

    public function getCommands() {
        return array_unique(array_values($this->commands));
    }

	public function RegisterCommand($command) {
		$pluginName = S::bot()->pluginmanager->AddCommand($command);
        $dbAccess = S::bot()->db->getCmdAccess($pluginName, $command->name);
        if ($dbAccess >= 0) {
            $command->SetAccess($dbAccess);
        } else {
            S::bot()->db->setCmdAccess($pluginName, $command->name, $command->GetAccess());
        }		
        $this->commands[$pluginName][$command->name] = $command;
        return $this;
	}

    public function CreateAlias($command, $alias) {
        if (array_key_exists($alias, $this->aliases)) {
            throw new BotException("Alias '$alias' already exists", 0);
        }
        S::logger()->log("Creating alias $alias");
        $this->aliases[$alias] = $command;
        return $this;
    }

    public function GetCommandByAlias($alias) {
        if (array_key_exists($alias, $this->aliases)) {
            return $this->aliases[$alias];
        } else {
            return null;
        }
    }
    
    /**
     * 
     *
    function CommandExists($plugin, $cmd) {
        return array_key_exists($cmd,self::$cmdlist) &&
               array_key_exists($plugin, self::$cmdlist[$cmd]);   
    }

    function AliasExists($alias) {
        return array_key_exists($alias, self::$aliases);
    }
    
    
    function SetCmdAccess($plugin, $cmd,$level) {
	if (!is_numeric($level)) {
	    throw new BotException("$level is not a numeric value", ERR_NOT_NUMERIC);
	} elseif (self::CommandExists($plugin, $cmd)) {
		if (self::$cmdlist[$cmd][$plugin]->SetAccess( $level )) {
		    self::$database->SetCmdAccess($plugin, $cmd, $level);
		    $ev = new Event(EVENT_CMD_ACCESS_CHANGED, array(
		                                               'plugin' => $plugin,
		                                               'command'=>$cmd, 
		                                               'level'=>$level
		                                              )
		    );
		    self::EventRun($ev);
		}
		return true;
	} else throw new BotException("Command does not exists", ERR_CMD_NOTFOUND);
}
*/

    public function BuildCommand($name, $func, $access = 1, $helpstr = null) {
        $name = mb_strtolower($name, 'utf-8');
	    if (!is_numeric($access)) {
	         $access = 1;
	    }
        $command = new BotCommand($name);
        $command->addCallbackFunc($func);
        $command->setAccess($access);
        $command->helpFull = $helpstr;
        $command->helpShort = $name;
        
	    return $command;
    }		

}
