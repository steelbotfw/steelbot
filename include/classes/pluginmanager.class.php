<?php

class PluginManager extends SComponent {	
	protected $instances = array(),
              $_current_plugin,
              $plugins;
	
	
	public function __construct($bot) {
		parent::__construct($bot);

        $this->plugins = $this->FindPlugins(STEELBOT_DIR.'/plugins');
        $userplugins = $this->FindPlugins($bot->config['bot']['plugin_dir']);
        $this->plugins = S::mergeArray($this->plugins, $userplugins);
	}
    
    public function getPluginInstance($name = null) {
        if (is_null($name)) {
            if ($this->_current_plugin != null) {
                return $this->_current_plugin;
            } else {
                $backtrace = debug_backtrace();
                $name = str_replace('.plugin.php', '', $backtrace[2]['file']);
                $name = array_shift(explode('.', $name, 2));
                if ($this->PluginLoaded($name)) {
                    return $this->instances[$name];
                } else {
                    return null;
                }            
            }
        } else {
            if (array_key_exists($name, $this->instances)) {
                return $this->instances[$name];
            } else {
                return null;
            }
        }
    }

    public function pluginAvailable($name) {
        if (array_key_exists($name, $this->plugins)) {
            return $this->plugins[$name];
        } else {
            return false;
        }
    }

    function FindPlugins($dir) {
        $names = glob($dir.'/*');
        $result = array();
        foreach ($names as $fileName) {
            if (is_dir($fileName)) {
                $result += $this->FindPlugins($fileName);
            } elseif (is_file($fileName) && substr($fileName, -11) == '.plugin.php') {
                $name = str_replace('.plugin.php', '', basename($fileName));
                $result[$name] = realpath($fileName);
            }
        }
        return $result;
    }

    public function LoadPlugin($filename, $params) {
        S::logger()->log("Loading $filename...");
        $name = str_replace('.plugin.php', '', basename($filename) );
        
        if ($this->PluginLoaded($name)) {
            throw new BotException(("Plugin $name already loaded"));
        } else {
            S::logger()->log("Loading plugin: $name ... ");
            $plug = new Plugin($filename);
            $this->_current_plugin = $plug;
            $plug->Load();        
            $this->instances[$name] = $plug;
            S::logger()->log("'$name' load OK");
            S::bot()->eventManager->EventRun( new Event(EVENT_PLUGIN_LOADED, array('name'=>$name)));                   
            $this->_current_plugin = null;
            return true;        
        }
    }

    public function AddCommand($command) {
        $plugin = $this->pluginInstance;
        if ($plugin != null) {
            $plugin->AddCommand($command);
            return $plugin->name;
        } else {
            return null;
        }
    }    

    function PluginLoaded($plugin) {
        return array_key_exists($plugin, $this->instances);
    }

    public function getPluginInstances() {
        return $this->instances;
    }
}
