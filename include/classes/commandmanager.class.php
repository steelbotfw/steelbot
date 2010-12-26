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

	public function RegisterCommand($command) {
		$pluginName = S::bot()->pluginmanager->AddCommand($command);		
        $this->commands[$pluginName][$command->name] = $command;
        return $this;
	}

    public function CreateAlias($command, $alias) {
        if (array_key_exists($alias, $this->aliases)) {
            throw new BotException("Alias '$alias' already exists");
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
    } */

    public function BuildCommand($name, $func, $access = 1, $helpstr = null, $create_alias = true) {
        $name = mb_strtolower($name, 'utf-8');
	    if (!is_numeric($access)) {
	         $access = 1;
	    }	
	    return new BotCommand($name, $func, $access, $helpstr);
    }	

	

}
