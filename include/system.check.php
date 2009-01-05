<?php

/**
 * @desc Проверяет систему на пригодность для использования бота, а также
 * важные конфигурационные переменные на корректность
 *
 */
function CheckSystem() {  
  global $cfg;
  echo "Testing bot and system ...\n";
  
  // system capabilities check
  
  // php version check
  if (phpversion() < 5) {
     exit("   Fatal error: PHP version must be 5 or higher\n");
  }

  // sockets extension check
  if (!function_exists('socket_create')) {
     echo "   Warning: no php_sockets extension\n";
  }
  
  // iconv exetnsion check
  if (!function_exists('iconv')) {
      if ( !function_exists('libiconv') ) {
          exit(   "Fatal error: iconv extension must be loaded");
      } else {
          function iconv($input_encoding, $output_encoding, $string) {
              return libiconv($input_encoding, $output_encoding, $string);
          }
          echo "   Warning: iconv() replaced with libiconv()\n";
      }
  }
  
  // mbstring extension check
  if (!function_exists('mb_strtolower')) {
      echo "   Warning: no mb_string extension found\n";
  }

  // script time limit check
  $time = ini_get('max_execution_time');
  set_time_limit(0);
  if (ini_get('max_execution_time') > 0) {
     exit("Fatal error: script time limit must be equal 0\n");
     exit();
  }
  set_time_limit($time);

  // configuration check
  if (empty($cfg)) {
     exit("Fatal error: missing config file\n"); 
  } else {
     if (empty($cfg['bot_uin'])) {
        exit("Fatal error: no uin to connect to - ['bot_uin']\n");    
     }
     
     if (empty($cfg['bot_password'])) {
        exit("Fatal error: no password for bot uin - ['bot_password']\n");    
     }
     
     if (empty($cfg['plugin_dir'])) {
        echo "   Warning: no plugins directory - ['plugin_dir'] (set to 'plugins') \n";    
        $cfg['plugin_dir'] = dirname(__FILE__).'/plugins';
     }
       
     if (empty($cfg['delaylisten'])) {
        echo "   Warning: socket listening delay set to 1 second - ['delaylisten']\n";    
        $cfg['delaylisten'] = 1;
     }
     
     if (empty($cfg['connect_attempts'])) {
        echo "   Warning: Connection attempts set to 20\n";    
        $cfg['connect_attempts'] = 20;
     }
     Echo "Test OK\n";
  }
}
 