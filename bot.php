<?php
/**
 * SteelBot - модульный PHP бот.
 * 
 * http://steelbot.net
 * 
 * @version  3
 * @author   N3x^0r ( mailto: n3xorus@gmail.com )
 * @license  GPL v. 2
 * 
 */

define('STEELBOT_VERSION', '3.0.0');
if (!defined('STEELBOT_DIR'))
	define('STEELBOT_DIR', dirname(__FILE__)); 
error_reporting(E_ALL);

echo 'SteelBot v. '.STEELBOT_VERSION."\n\n";

define('LOG_LEVEL_INFO', 4);
define('LOG_LEVEL_NOTICE', 3);
define('LOG_LEVEL_WARNING', 2);
define('LOG_LEVEL_ERROR', 1);
define('LOG_LEVEL_NONE', 0);

if (!isset($argv)) {
	$argv = array(__FILE__);
}

function __autoload($classname) {
	$name = mb_strtolower($classname);
	if (file_exists(STEELBOT_DIR."/include/classes/$name.class.php")) {
		include STEELBOT_DIR."/include/classes/$name.class.php";
		return true;
	} else {
		return false;
	}
}

// interfaces
foreach (glob(STEELBOT_DIR.'/include/interfaces/*.interface.php') as $in) {
	include_once $in;
}

if (!isset($config)) {
	die('Configuration is not specified');
}

S::init($config);

// common functions
require_once STEELBOT_DIR.'/include/common.php';

// checking system
if ( !@array_search('-skipcheck', $argv) ) {
	require_once('include/system.check.php');
	CheckSystem();
}



/*
if ($cfg['db.use_config']) {
	foreach ($cfg as $key=>$value) {
		if (SteelBot::DbIgnoredOption($key)) {
		    echo "Ignoring loading option from database: $key\n";
			continue;
		}
		try {
			$value = SteelBot::GetOption($key);
			$cfg[$key] = $value;
		} catch (BotException $e) {
			switch ($e->getCode()) {
				case BotException::UNKNOWN_CONFIG_OPTION:
					SteelBot::SetOption($key, $value);
					break;
				default:
					throw $e;
			}
		}
	}
} */

require_once STEELBOT_DIR.'/include/i18n.php';

if (@in_array('-test', $argv ) || S::bot()->config['bot']['test']) {
    S::logger()->log("'-test' option enabled. Exiting.");
    die();
}


set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);

S::bot()->eventManager->RegisterEventHandler(EVENT_MSG_RECEIVED, array(S::bot(),'Parse'))
//->RegisterEventHandler(EVENT_MSG_UNHANDLED, array(SteelBot::$lng, 'RestorePrimaryLang'), 10)
//->RegisterEventHandler(EVENT_MSG_HANDLED, array(SteelBot::$lng, 'RestorePrimaryLang'), 10)
->RegisterEventHandler(EVENT_EXIT, array(S::bot()->proto, 'Disconnect'));


$connect_attempts = 0; //попытки подключения
S::bot()->eventManager->EventRun( new Event(EVENT_BOT_LOADED) );
while ($connect_attempts++ < S::bot()->config['bot']['connect_attempts']) {
   flush();
   
   S::logger()->log("Connecting to server  ... ");
   if ( S::bot()->proto->Connect() ) {
         $connect_attempts = 0;		   
	     S::logger()->log("Ready to work.");
 	     while (S::bot()->proto->Connected()) {	       		      
 		      S::bot()->timerManager->checkTimers();
 		      $event = S::bot()->proto->GetMessage();
              if ($event===false) {
                  usleep((int)S::bot()->config['bot']['delaylisten']*1000000);  
              } else {
                  S::bot()->eventManager->EventRun($event);
              }
              echo '.';   		      	
 	     } 
 	              
    } else {
        S::logger()->log("Connection error: ".SteelBot::Error() );
    }
    
    S::logger()->log("Disconnected.");
    sleep(10);
}
S::logger()->log('Bot stopped');
echo "\nBot stopped.\n";  
  