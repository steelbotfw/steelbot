<?php

class BotCommand extends SComponent{
    protected $_name,
	        
            $access,
            $case_sens = false,
            $hidden = false,
            $callback_list = array(),
            $aliases_list = array(),
            $enabled = true,

            $helpstr_short,
	        $helpstr_full,
			$help_handler_short = null,
            $help_handler_full = null;

    public $plugin = null;
    const HELP_SHORT = 0;
    const HELP_FULL = 1;
    
    
    public function __construct($command, $func, $access = 1, $helpstr = false, $plugin = null) {
        $this->_name = $command;
        $this->callback_list[] = $func;
        $this->SetAccess($access);
        $this->helpstr_short = $helpstr;
        $this->helpstr_full = $helpstr;
        $this->plugin = $plugin==null?S::bot()->plugin:$plugin;

        $this->help_handler_full = array($this, 'HelpHandlerFull');
        $this->help_handler_short = array($this, 'HelpHandlerShort');
    }
    
    public function SetHide($v) {
        $this->hidden = $v;
        return true;
    }
    
    public function IsHidden() {
        return $this->hidden;
    }
    
    public function getName() {
        return $this->_name;
    }
    
    public function GetCallbackList() {
        return $this->callback_list;
    }
    
    public function AddCallbackFunc($func) {
        if (!in_array($func, $this->callback_list)) {
            $this->callback_list[] = $func;
            return true;
        } else {
            return false;
        }
    }
    
    public function DelCallbackFunc($func) {
        foreach ($this->callback_list as $k=>$c) {
            if ($c==$func) {
                unset($this->callback_list[$k]);
                return true;
            }
        }
        return false;
    }
    
    public function CaseSensitive($value = false) {
        $this->case_sens = $value;
        return true;
    }

    public function HelpHandlerFull($name) {
        return str_replace(array('%c'), array($name), $this->helpstr_full);
    }

    public function HelpHandlerShort($name) {
        return str_replace(array('%c'),array($name), $this->helpstr_short);
    }
    
    public function GetHelp($inlist=0, $alias) {
        if ($this->hidden || !$this->enabled ) {
            return null;
        }
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
            trigger_error("{$this->name}: no help handler found", E_USER_WARNING);
        }
    }

    public function GetPlugin() {
        return $this->plugin;
    }
    
	public function SetHelpHandlerShort($callback) {
		$this->help_handler_short = $callback;
	}
    
    public function SetHelpHandlerFull($callback) {
        $this->help_handler_full = $callback;
    }
	
    public function SetHelp($text) {
        $this->helpstr = $text;
        return true;
    }
    
    public function GetAccess() {
        return $this->access;
    }
    
    public function SetAccess($level) {
        $this->access = $level;
        return true;
    }
    
    public function Enable() {
        $this->enabled = true;
        return true;
    }
    
    public function Disable() {
        $this->enabled = false;
        return true;
    }
    
    public function IsEnabled() {
        return $this->enabled;
    }

    public function AddAlias($alias) {
        $i = array_search($alias, $this->aliases_list);
        if ($i === false) {
            $this->aliases_list[] = $alias;
            return true;
        } else {
            return false;
        }
    }

    public function RemoveAlias($alias) {
        $i = array_search($alias, $this->aliases_list);
        if ($i !== false) {
            unset( $this->aliases_list[$i] );
            return true;
        } else {
            return false;
        }
    }

    public function GetAliases($single) {
        if ($single) {
            if (!empty($this->aliases_list)) {
                return $this->aliases_list[0];
            } else {
                return false;
            }
        } else {
            return $this->aliases_list;
        }
    }

    public function HaveAlias($alias) {
        return in_array($alias, $this->aliases_list);
    }

    public function Execute($params, &$msgevent) {
        if (!$this->enabled) return;

        //access check
        /*
        if ( $ac=SteelBot::GetUserAccess() < $this->access) {
            throw new BotException("{$this->name}: acces denied (user: $ac, cmd: {$this->access})", ERR_CMD_ACCESS);
        } */

       
        //running handlers
        foreach ($this->callback_list as $callback) {
            if ( is_callable($callback) )  {
                call_user_func($callback, $params, $msgevent);
            } else {
                throw new BotException("Function ".func2str($callback)." does not exists", ERR_FUNC);
            }
        }
    }
   
}
