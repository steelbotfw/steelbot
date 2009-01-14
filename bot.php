<?php
/**
 * SteelBot - модульный ICQ PHP бот.
 * 
 * http://steelbot.net
 * 
 * @version  1.4
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
define('STEELBOT_MINOR_VER', 4);

define('REG_CMD_OK', 1);
define('REG_CMD_ALREADY_DEFINED', 2);
define('STEELBOT_DIR', dirname(__FILE__)); 

error_reporting(E_ALL);

echo 'SteelBot v. '.STEELBOT_MAJOR_VER.'.'.STEELBOT_MINOR_VER."\n\n";

include_once STEELBOT_DIR.'/include/classes/slog.class.php';
require_once STEELBOT_DIR.'/include/interfaces/isteelbotdb.interface.php';
require_once STEELBOT_DIR.'/include/classes/steelbotcore.class.php';
require_once STEELBOT_DIR.'/include/classes/steelbot.class.php';


$i = @array_search('-cfg', $argv);
if ($i++) {
    echo "Loading {$argv[$i]} ...";
    require_once($argv[$i]);
    echo " OK\n";
} else {
    echo "Loading config.php ...";
    require_once(dirname(__FILE__).'/config.php');
    echo " OK\n";
}
SteelBot::$cfg = $cfg;
slog::add('***',"Log started at ".date("r"));


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
if (@in_array('-test', $argv ) ) {
    die();
}

foreach (glob(dirname(__FILE__)."/include/const/*.php") as $file) {
    include_once $file;
}

require_once(dirname(__FILE__)."/include/common.php");

// interfaces

require_once(dirname(__FILE__)."/include/interfaces/isteelbotprotocol.interface.php");


//other classes
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

if (SteelBot::$cfg['lockfile_enabled']) {
    register_shutdown_function(array('SteelBot', 'DeleteLockFile'));
}


slog::add('***',"Plugins count: ".count(SteelBot::$plugins));
slog::add('***', "Standard commands count: ".count(SteelBot::$cmdlist));

$connect_attempts = 0; //попытки подключения

while ($connect_attempts++ < SteelBot::$cfg['connect_attempts']) {
   flush();
   if (SteelBot::$cfg['lockfile_enabled']) {
       slog::add('***',"Checking for lockfile ... ");
       if ( SteelBot::CheckLockFile() ) {
           slog::add('***',"LOCK enabled. You must delete lock file to enable connecting to server");
           die();
       }
       slog::result("OK");
   }
   
   slog::add('***',"Connecting to server [ ".SteelBot::$cfg['bot_uin']." ]... "); 
   if ( SteelBot::Connect() ) {
         $connect_attempts = 0;	
         if (SteelBot::$cfg['lockfile_enabled']) {     
	       SteelBot::CreateLockFile();
         }
	     @include SteelBot::$cfg['autoinclude_file'];
	     slog::add('***', "Ready to work.");
 	     while (SteelBot::Connected()) {	       		      
 		      $time = time();
 		      if ($time > SteelBotCore::$next_timer) {
 		          SteelBotCore::TimerRun($time);
 		      }
 		      SteelBot::ParseMessage(); 		      		      	
 	     } 
 	              
    } else {
        slog::add('***', "Connection error: ".SteelBot::Error() );
    }
    
    if (SteelBot::$cfg['lockfile_enabled']) {
        SteelBot::DeleteLockFile();
    }
    slog::add('***', "Disconnected." );
    sleep(10);
}

echo "\nBot stopped.\n";  
  
