<?php

require_once "botcommand.class.php";

class Plugin extends SComponent{
    protected $_name,
            $filename,
            
            $info = array(
                'name'    => '',
                'author'  => '',
                'version' => '99.99'
            ),
            
            $dependencies = array(
                'plugin'   => array(),
                'database' => array(),
                'proto' => array(),
                'bot' => array()
            ),
            
            $commands = array();
    
    public function __construct($filename) {
        parent::__construct(S::bot());
        $this->filename = $filename;
        $this->_name = str_replace('.plugin.php', '', basename($this->filename) );
    }

    public function getName() {
        return $this->_name;
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
            throw new BotException("Can't get access to {$this->filename}", 0);
        }
    }
    

    
    public function AddDependence($dep, $version, $type) {
        switch ($type) {
            case 'plugin':
                    $this->dependencies['plugin'][$dep] = array(
                        'dep' => $dep,
                        'version' => $version
                    );
                    break;

            case 'database':
                    $this->dependencies['database'][$dep] = array(
                        'dep' => $dep,
                        'version' => $version
                    );
                    break;

            case 'proto':
                    $this->dependencies['proto'][$dep] = array(
                        'dep' => $dep,
                        'version' => $version
                    );
                    break;

            case 'bot':
                    $this->dependencies['bot'] = array(
                        'dep' => $dep,
                        'version' => $version
                    );
                    break;
            
            default: 
                throw new BotException("Unknown dependency type: $type",0);
        }
    }
    
    public function GetDependencies($filter = 'all') {
        if ($filter != 'all') {
            return $this->dependencies[$filter];
        } else {
            return $this->dependencies;    
        }
    }
    
    public function AddCommand($cmdobject) {
        $this->commands[$cmdobject->GetName()] = $cmdobject;
        return true;    
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
