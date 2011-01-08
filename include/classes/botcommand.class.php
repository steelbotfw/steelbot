<?php

class BotCommand extends SComponent {
    protected $_name,
              $_access,
              $_case_sens = false,
              $_plugin,
              
            $callback_list = array(),
            $aliases_list = array(),
            $enabled = true,

            $helpstr_short,
	        $helpstr_full,
			$help_handler_short = null,
            $help_handler_full = null;

    const HELP_SHORT = 1;
    const HELP_FULL = 0;
        
    public function __construct($command, $func = null, $access = 1, $helpstr = false, $plugin = null) {
        $this->_name = $command;
        if (!is_null($func)) {
            $this->callback_list[] = $func;
        }
        $this->_plugin = $plugin==null?S::bot()->plugin:$plugin;
        
        $this->SetAccess($access);
        $this->helpstr_short = $helpstr;
        $this->helpstr_full = $helpstr;
        

        $this->help_handler_full = array($this, 'HelpHandlerFull');
        $this->help_handler_short = array($this, 'HelpHandlerShort');
    }
    
      
    public function getName() {
        return $this->_name;
    }
    
    public function GetCallbackList() {
        return $this->callback_list;
    }
    
    public function AddCallbackFunc($func, $allow_duplicate = false) {
        if (!in_array($func, $this->callback_list)) {
            $this->callback_list[] = $func;
            return $this;
        } elseif ($allow_duplicate) {
            $this->callback_list[] = $func;
            return $this;
        } else {
            throw new BotException("Function already is in callbacks",0);
        }
    }
    
    public function DelCallbackFunc($func) {
        foreach ($this->callback_list as $k=>$c) {
            if ($c==$func) {
                unset($this->callback_list[$k]);
            }
        }
        return $this;
    }
    
    public function SetCaseSensitive($value = false) {
        $this->_case_sens = $value;
        return $this;
    }

    public function GetCaseSensitive() {
        return $this->_case_sens;
    }

    public function HelpHandlerFull($alias) {        
        return str_replace(array('%c'), array($alias), $this->helpstr_full);
    }

    public function HelpHandlerShort($alias) {
        return str_replace(array('%c'),array($alias), $this->helpstr_short);
    }
    
    public function GetHelp($inlist=0, $alias = null) {
        
        switch ($inlist) {
            case self::HELP_SHORT:
                $handler = $this->help_handler_short;
                break;
            case self::HELP_FULL:
                $handler = $this->help_handler_full;
                break;
        }
		if (!is_null($handler)) {
			if (is_callable($handler)) {
				return call_user_func($handler, $alias);
			} else {
				trigger_error("Function ".func2str($this->callback)." does not exists", E_USER_WARNING);
			}
		} else {
            trigger_error("{$this->_name}: no help handler found", E_USER_WARNING);
        }
    }

    public function GetPlugin() {
        return $this->_plugin;
    }
    
	public function SetHelpHandlerShort($callback) {
		$this->help_handler_short = $callback;
	}
    
    public function SetHelpHandlerFull($callback) {
        $this->help_handler_full = $callback;
    }
	
    public function SetHelp($text) {
        $this->helpstr = $text;
        return $this;
    }
    
    public function GetAccess() {
        return $this->_access;
    }
    
    public function SetAccess($level) {
        $this->_access = $level;
        return $this;
    }

    public function Execute($params, &$msgevent) {
        //access check
        /*
        if ( $ac=SteelBot::GetUserAccess() < $this->access) {
            throw new BotException("{$this->name}: acces denied (user: $ac, cmd: {$this->access})", ERR_CMD_ACCESS);
        } */
       
        foreach ($this->callback_list as $callback) {
            if ( is_callable($callback) )  {
                call_user_func($callback, $params, $msgevent);
            } else {
                throw new BotException("Function ".func2str($callback)." does not exists", ERR_FUNC);
            }
        }
    }
   
}
