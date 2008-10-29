<?php

/**
 * SteelBot - модульный ICQ PHP бот.
 * 
 * http://steelbot.net
 * 
 * @version  1.3
 * @author   N3x^0r ( mailto: n3xorus@gmail.com )
 * @license  GPL v. 2
 * 
 */

if ( isset($_SERVER['HTTP_HOST']) ) {
    define('STEELBOT_CLI', false);
    switch (@$_REQUEST['web']) {
        case 'skip':
             echo '<pre>';
            break;
            
        default: header("Location: web/index.php");
            exit;    
    }
} else {
    define('STEELBOT_CLI', true);
}

define('STEELBOT_MAJOR_VER', 1);
define('STEELBOT_MINOR_VER', 3);

define('REG_CMD_OK', 1);
define('REG_CMD_ALREADY_DEFINED', 2);
define('STEELBOT_DIR', dirname(__FILE__)); 

echo "SteelBot v. 1.2\n\n";

include_once STEELBOT_DIR.'/include/const/log_codes.php';

$i = @array_search('-cfg', $argv);
if ($i++) {
    echo "Loading {$argv[$i]} ...";
    require_once($argv[$i]);
    echo " OK\n";
} else {
    echo "Loading ./config.php ...";
    require_once(dirname(__FILE__).'/config.php');
    echo " OK\n";
}

// password check for web 
if ( !STEELBOT_CLI ) {
    if ( !empty($cfg['web_password']) && $_REQUEST['password'] != $cfg['web_password']) {
        die('Incorrect web password. Please, edit "web_password" option to set password for web access');
    }
    ignore_user_abort(true);
}

// checking system
require_once('include/system.check.php');
CheckSystem();

require_once(dirname(__FILE__)."/include/common.php");


// interfaces
require_once(dirname(__FILE__)."/include/interfaces/isteelbotlog.interface.php");
require_once(dirname(__FILE__)."/include/interfaces/isteelbotdb.interface.php");
require_once(dirname(__FILE__)."/include/interfaces/isteelbotprotocol.interface.php");

// classes
require_once( dirname(__FILE__)."/include/classes/steelbotcore.class.php");
require_once ( dirname(__FILE__).'/include/classes/steelbotlng.class.php' );

foreach ( glob( dirname(__FILE__)."/include/classes/*.class.php" ) as $class) {
    include_once($class);
}

require_once(dirname(__FILE__)."/protocol/proto.class.php");
require_once(dirname(__FILE__)."/database/db.php");

set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);

require_once(dirname(__FILE__)."/include/i18n.php");
SteelBot::Init($cfg);

flush();


register_shutdown_function(array('slog', 'save'));
register_shutdown_function(array('SteelBot', 'DeleteLockFile'));

echo "\n";
echo "Plugins count: ".count(SteelBot::$plugins)."\n";
echo "Standard commands count: ".count(SteelBot::$cmdlist)."\n\n";

$connect_attempts = 0; //попытки подключения

while ($connect_attempts < SteelBot::$cfg['connect_attempts']) {
   echo "Connecting to server [ ".SteelBot::$cfg['bot_uin']." ]... "; 
   flush();
   if (file_exists(dirname(__FILE__)."/include/tmp/".SteelBot::$cfg['bot_uin'].'.lock')) {
       echo "LOCK enabled. You must delete lock file to enable connecting to server\n";
       die();
   }
   if ( SteelBot::Connect() ) {
         $connect_attempts = 0;
	     echo "Connected. Ready to work.\n";
	     file_put_contents( dirname(__FILE__).SteelBot::$cfg['bot_uin'].'.lock', '1' );
	     @include SteelBot::$cfg['autoinclude_file'];
 	     while (SteelBot::Connected()) {	       		      
 		      $time = time();
 		      if ($time > SteelBotCore::$next_timer) {
 		          SteelBotCore::TimerRun($time);
 		      }
 		      SteelBot::ParseMessage();
 		      		      	
 	     } 
 	              
    } else {
        echo "Connection error: ".SteelBot::Error()."\n";
        
        $connect_attempts++;
    }
    SteelBot::DeleteLockFile();
    echo "Disconnected.\n";
    sleep(10);
}

echo "Bot stopped.\n";  
  
