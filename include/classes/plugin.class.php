<?php

require_once "botcommand.class.php";

class Plugin {
    private $name,
            $filename,
            
            $info = array(
                'major_ver' => 99,
                'minor_ver' => 99
            ),
            
            $dependencies = array(
                'plugin'   => array(),
                'database' => array()
            ),
            
            $commands = array();
    
    public function __construct($filename) {
        $this->filename = $filename;
    }
    
    public function GetInfo() {
        return $this->info;
    }
    
    public function ExportInfo($info) {
        $this->info = $info;
    }
    
    public function Load() {
        if ( is_readable($this->filename) ) {
            include_once($this->filename);    
        } else {
            throw new BotException("Can't get access to {$this->filename}",ERR_FILE_ACCESS);
        }
    }
    
    public function AddDependence($dep, $maj_ver, $min_ver, $type) {
        switch ($type) {
            case 'plugin':
                    $this->dependencies['plugin'][$dep] = array(
                        'dep' => $dep,
                        'major_ver' => $maj_ver,
                        'minor_ver' => $min_ver
                    );
            break;
            
            default: 
                throw new BotException("Unknown dependency type",0);
        }
    }
    
    public function GetDependencies($filter = 'all') {
        if ($filter != 'all') {
            return $this->dependencies[$filter];
        } else {
            return $this->dependencies;    
        }
    }
    
    public function AddCommand($cmdname) {
        $this->commands[] = $cmdname;
    }
    
    public function DelCommand($cmdname) {
        unset( $this->commands[$cmdname] );
    }
    
    public function GetCommands() {
        return $this->commands;
    }
    
    public function GetActiveCommands() {
        
    }
}