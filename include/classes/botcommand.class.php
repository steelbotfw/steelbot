<?php

class BotCommand {
    private $name,
            $helpstr,
            $access,
            $case_sens = false,
            $hidden = false,
            $callback_list = array(),
            $enabled = true;
    
    
    public function __construct($command, $func, $access = 1, $helpstr = false) {
        $this->name = $command;
        $this->callback_list[] = $func;
        $this->access = $access;
        $this->helpstr = $helpstr;
    }
    
    public function SetHide($v) {
        $this->hidden = $v;
    }
    
    public function IsHidden() {
        return $this->hidden;
    }
    
    public function GetName() {
        return $this->name;
    }
    
    public function GetCallbackList() {
        return $this->callback_list;
    }
    
    public function AddCallbackFunc($func) {
        if (!in_array($func, $this->callback_list)) {
            $this->callback_list[] = $func;
        }
    }
    
    public function DelCallbackFunc($func) {
        foreach ($this->callback_list as $k=>$c) {
            if ($c==$func) {
                unset($this->callback_list[$k]);
                break;
            }
        }
    }
    
    public function CaseSensitive($value = false) {
        $this->case_sens = $value;
    }
    
    public function GetHelp($inlist=false) {
        if ($this->hidden || !$this->enabled || is_null($this->helpstr) ) {
            return null;
        }
        if ($inlist) {
            $msg = str_replace( 
                array('%c', '%s'),
                array( $this->name, $this->helpstr ),
                SteelBot::$cfg['help.format']
            );
        } else {
            $msg = str_replace( 
                array('%c', '%s'),
                array( $this->name, $this->helpstr ),
                SteelBot::$cfg['help.format_full']
            );
        }
        return $msg;
    }
    
    public function SetHelp($text) {
        $this->helpstr = $text;
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
    }
    
    public function Disable() {
        $this->enabled = false;
    }
    
    public function IsEnabled() {
        return $this->enabled;
    }
    
    public function Execute($params) {
        if (!$this->enabled) return;
        //access check
        if ( $ac=SteelBot::GetUserAccess() < $this->access) {
            throw new BotException("{$this->name}: acces denied (user: $ac, cmd: {$this->access}", ERR_CMD_ACCESS);
        }
        
        //running handlers
        foreach ($this->callback_list as $callback) {
            if ( is_callable($callback) )  {
                call_user_func($callback, $params);
            } else {
                throw new BotException("Function ".func2str($callback)." does not exists", ERR_FUNC);
            }
        }
    }
   
}