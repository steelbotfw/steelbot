<?php

/**
 * @desc Проверяет систему на пригодность для использования бота, а также
 * важные конфигурационные переменные на корректность
 *
 */
function CheckSystem() {  
  global $cfg;
  slog::add('syscheck', "Testing bot and system ...");
  
  // system capabilities check
  
  // php version check
  slog::add('syscheck', "    PHP version: ".phpversion());
  if (phpversion() < 5) {
     exit("   Fatal error: PHP version must be 5 or higher\n");
  }
  
  $extensions = get_loaded_extensions();
  
  // iconv exetnsion check
  slog::add('syscheck', "    Checking for iconv extension... ");
  if (!in_array('iconv', $extensions)) {
      if ( !function_exists('libiconv') ) {
          exit(   "\nFatal error: iconv extension must be loaded");
      } else {
          function iconv($input_encoding, $output_encoding, $string) {
              return libiconv($input_encoding, $output_encoding, $string);
          }
          slog::add('syscheck', "    [ Warning ] iconv() replaced with libiconv()");
      }
  } else {
      slog::result("OK");
  }
  
  // mbstring extension check
  slog::add('syscheck', "    Checking for mbstring extension... ");
  if ( !in_array('mbstring', $extensions) ) {
      slog::add('syscheck', "    [ Fatal error ] no mb_string extension found");
      exit();
  } else {
      slog::result( "OK" );
  }

  // script time limit check
  $time = ini_get('max_execution_time');
  set_time_limit(0);
  slog::add('syscheck', "    Bot time limit check... ");
  if (ini_get('max_execution_time') > 0) {
     exit("Fatal error: script time limit must be equal 0\n");
  } else {
        slog::result("0. OK");   
  }

  // configuration check
  slog::add('syscheck', '    Configuration check...');
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
        slog::add('syscheck', "   [ Warning ] no plugins directory - ['plugin_dir'] (set to 'plugins')");    
        $cfg['plugin_dir'] = dirname(__FILE__).'/plugins';
     }
       
     if (empty($cfg['delaylisten'])) {
        slog::add('syscheck', "   [ Warning ] socket listening delay set to 1 second - ['delaylisten']");    
        $cfg['delaylisten'] = 1;
     }
     
     if (empty($cfg['connect_attempts'])) {
        slog::add('syscheck', "   [ Warning ] Connection attempts set to 20");    
        $cfg['connect_attempts'] = 20;
     }
     slog::add('syscheck', "Test OK\n");
  }
}
 