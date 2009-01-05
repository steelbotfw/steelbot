<?php

/**
 * admin - плагин для SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 1.3
 *
 * 2008-09-17
 * 
 */

SteelBot::RegisterEventHandler(EVENT_MSG_RECIEVED, array('SteelBotAdmin', 'ParseCommand'));

if (!defined('STEELBOT_MAJOR_VER') || (STEELBOT_MAJOR_VER < 1) || (STEELBOT_MINOR_VER < 3)) {
    echo "ERROR: plugin admin require SteelBot 1.3 or higher version. You can download it from http://steelbot.net\n";
    SteelBot::DoExit();
}

class SteelBotAdmin {

static $firstchar = '.',
       $lng,
       $commands = array (
           'help'      => array(
                            'func' => array('SteelBotAdmin', 'CmdHelp'),
                          ),

           'cmdaccess' => array(
                            'func' => array('SteelBotAdmin', 'CmdCmdAccess'),                            
                          ),                         
           'eval'    => array(
                            'func' => array('SteelBotAdmin', 'CmdEval'),                            
                          ),              
                           
           'exit'      => array(
                            'func' => array('SteelBotAdmin', 'CmdExit'),                            
                          ),
                          
           'opt'       => array(
                            'func' => array('SteelBotAdmin', 'CmdOpt'),                            
                          ),
                          
           'plugins' =>   array(
                            'func' => array('SteelBotAdmin', 'CmdPlugins'),                            
                          ),
                                                    
           'reconnect' => array(
                            'func' => array('SteelBotAdmin', 'CmdReconnect'),
                            
                          ),                   
                          
           'useraccess'=> array(
                            'func' => array('SteelBotAdmin', 'CmdUserAccess'),
                            
                          ),
                          
           'timer' => array(
                            'func' => array('SteelBotAdmin', 'CmdTimer'),
                          
                          )                            
       );
   
static function _($key) {
    $translated = self::$lng->GetTranslate( $key );
    if (func_num_args() > 1) {
        $params = func_get_args();
        array_splice($params,0,1 );
        for ($i=0; $i<count($params); $i++) {
            $translated = str_replace('%'.($i+1), $params[$i], $translated);
        }       
    }
    return $translated;    
}

static function ParseCommand() {
    $text = SteelBot::GetMsgText();
    if ($text[0] != self::$firstchar) return false;
    
	list($command,$val) = explode(' ', $text, 2);
	$command = substr($command, 1);
	
	if (SteelBot::GetUserAccess() >= 100) {
	    if (array_key_exists($command, self::$commands)) {
	        call_user_func(self::$commands[$command]['func'], $val);
	        
	    } else {
	        SteelBot::Msg( self::_('parsecommand_1', $command, self::$firstchar) );
	    }
	    SteelBot::DropMsg();
	}
}     

static function CmdHelp($cmd) {
    if ($cmd[0] == self::$firstchar) {
        $cmd = substr($cmd, 1);
    }
    if ( empty($cmd) ) {
        $list = array_keys(self::$commands);
        sort($list);
        $commands = self::$firstchar.implode(', '.self::$firstchar, $list );
        SteelBot::Msg(self::_('cmdhelp_1')."\n".$commands);
           
    } elseif (array_key_exists($cmd, self::$commands)) {
        SteelBot::Msg(self::$commands[$cmd]['helpstr']);
        
    } else {    
        SteelBot::Msg(self::_('cmdhelp_2', self::$firstchar, $cmd) ); 
    }
}

static function CmdReconnect() {
    SteelBot::Disconnect();   
}

static function CmdExit() {
    echo "Exit requested by ".SteelBot::GetUin()."\n";
    SteelBot::Msg( self::_('cmdexit_1') );
    SteelBot::DoExit();    
}

static function CmdOpt($val) {
    
    list($action, $p1) = explode(' ', $val, 2);
    switch ($action) {
        case 'create': list($p1, $p2) = explode(' ', $p1,2);
                       if (!array_key_exists($p1, SteelBot::$cfg)) {
                           SteelBot::$cfg[$p1] = $p2;
                           SteelBot::Msg( self::_('cmdopt_1'), $p1, $p2);
                           
                       } else {
                           SteelBot::Msg(self::_('cmdopt_2', $p1) );
                       }
                       break;
                       
        case 'delete': $p1 = array_pop( explode(' ', $p1,2));
                       if (array_key_exists($p1, SteelBot::$cfg)) {
                           unset( SteelBot::$cfg[$p1] );
                           SteelBot::Msg(self::_('cmdopt_3', $p1));
                           
                       } else {
                           SteelBot::Msg(self::_('cmdopt_4', $p1));
                       }
                       break;
                       
        case 'set':    list($p1, $p2) = explode(' ', $p1,2);
                       if ($p1 == 'bot_password') SteelBot::$cfg['bot_password'] = '<hidden>';
                       if ($p1 == 'master_uin') {
                           SteelBot::Msg(self::_('cmdopt_5'));
                           return;
                       }
                       if (array_key_exists($p1, SteelBot::$cfg)) {
                           $oldval = SteelBot::$cfg[$p1];
                           SteelBot::$cfg[$p1] = $p2;
                           SteelBot::Msg(self::_('cmdopt_6', $p1, $p2, $oldval));
                       }
                       break;
        
        case 'list':   $p1 = array_pop( explode(' ', $p1,2));
                       if (empty($p1)) {
                           $options = implode(' ,', array_keys(SteelBot::$cfg) );
                           Steelbot::Msg(self::_('cmdopt_7', $options));
                           
                       } elseif (array_key_exists($p1, SteelBot::$cfg)) {
                           switch ($p1) {
                               case 'bot_password': $value = '<hidden>';
                                  break;
                               default: $value = SteelBot::$cfg[$p1];
                                  break;
                           }
                           
                           SteelBot::Msg(self::_('cmdopt_8', $p1, $value));
                           
                       } else {
                           SteelBot::Msg(self::_('cmdopt_9', $p1));
                       }
                       break;
                       
        default:               
                        $options = implode(' ,', array_keys(SteelBot::$cfg) );
                        Steelbot::Msg(self::_('cmdopt_10', $options));               
    }  
}

static function CmdUserAccess($p1) {
    if (empty($p1)) {
        SteelBot::Msg(self::_('cmduseraccess_1', self::$firstchar));
        return;
    }
    list($p1, $p2) = explode(' ', $p1,2);    
    if ($p2 == null) {
        SteelBot::Msg(self::_('cmduseraccess_2', $p1, SteelBot::GetUserAccess($p1)));
    } elseif (SteelBot::is_uin($p1)) {
        if ( $p1 == SteelBot::$cfg['master_uin'] || 
             (is_array(SteelBot::$cfg['master_uin']) && in_array($p1, SteelBot::$cfg['master_uin']) )
           )    {
            SteelBot::Msg(self::_('cmduseraccess_3', $p1));
        } else {
            if ( SteelBot::SetUserAccess($p1,$p2) ) {
                SteelBot::Msg(self::_('cmduseraccess_4', $p1, $p2));
            } else {
                SteelBot::Msg(self::_('cmduseraccess_5'));
            }
        }
        
    } else {
        SteelBot::Msg(self::_('cmduseraccess_6'));
    }
}

static function CmdCmdAccess($p1) {
    if (empty($p1)) {
        SteelBot::Msg(self::_('cmdcmdaccess_1', self::$firstchar));
        return;
    }
    list($p1, $p2) = explode(' ', $p1,2);
    if (empty($p2)) {
        if (array_key_exists($p1, SteelBot::$cmdlist)) {       
            SteelBot::Msg(self::_('cmdcmdaccess_2', $p1, SteelBot::$cmdlist[$p1][0]));
        } else {
            SteelBot::Msg(self::_('cmdcmdaccess_3', $p1));
        }
        
    } elseif ( ((int)$p2 < 100) && ((int)$p2 > 0) ) {
        SteelBot::$cmdlist[$p1][0] = $p2;
        SteelBot::Msg(self::_('cmdcmdaccess_4', $p1, $p2));
        SteelBot::SaveCommandsAccesses();
        
    } else {
        SteelBot::Msg(self::_('cmdcmdaccess_5'));
    }    
}

static function CmdPlugins($param) {
    if (empty($param)) {
        $plugins = implode(", ", SteelBot::$plugins);
        SteelBot::Msg(self::_('cmdplugins_1', $plugins));    
    } else {
        list($cmd, $p1) = explode(' ', $param);
        switch($cmd) {
            case 'load': if ( SteelBot::LoadPluginByName($p1) ) {
                             SteelBot::Msg(self::_('cmdplugins_2', $p1));
                         } else {
                             SteelBot::Msg(self::_('cmdplugins_3'));
                         }
        }
    }
}

static function CmdEval($phpcode) {
    ob_start();
    eval($phpcode);
    $output = ob_get_flush();
    if (empty($output)) {
        $output = "Done.";
    }
    SteelBot::Msg($output);
}

static function CmdTimer($val) {
    list ($cmd, $param) = explode(' ',$val,2);
    switch ($cmd) {
        case 'list':
            $timer_list = self::_('cmdtimer_1');
            foreach (SteelBot::$timers as $label=>$functions) {
                $wait = $label - time();
                foreach ($functions as $func) {          
                    $timer_list .=  date("d M Y H:i:s",$label).' => '.func2str($func).
                                    " ($wait)\n";
                }
            }
            SteelBot::Msg($timer_list);           
            break;
            
        case 'add': 
            list($time, $func) = explode(' ',$param, 2);
            if (strpos($func, '::') !== false) {
                    $func = explode('::', $func);
            }
            switch ( substr_count(':', $time) ) {
                case 0: 
                    $time = (int)$time;
                    break;
                          
                case 1: 
                    list($min,$sec) = explode(':', $time, 2);
                    $time = ((int)$min *60)+$sec;
                    break;
                    
                case 2: 
                    list($hr, $min, $sec) = explode(':', $time, 3);
                    $time = ((int)$hr*3600) + ((int)$min *60) + $sec;
                    break;
                    
                case 3:    
                    list($days, $hr, $min, $sec) = explode(':', $time, 4);
                    $time = ((int)$days*3600*24) + ((int)$hr*3600) + ((int)$min *60) + $sec;
                    break;

            }
                    
            if ( ($time > 0) && ($time < 31536000)) {
                SteelBot::TimerAdd($time, $func);
                SteelBot::Msg(self::_('cmdtimer_2', func2str($func), $time));
            } else {
                SteelBot::Msg(self::_('cmdtimer_3'));                    
            }
            break;
                    
        case 'del': if ($param[0] == '~') {
                        $param = time()+(int)substr($param, 1);
                        $count = 0;
                        foreach (SteelBot::$timers as $k=>$v) {
                            if ($k <= $param) {
                                $count += count(SteelBot::$timers[$k]);
                                unset(SteelBot::$timers[$k]);
                            }
                        }            
                    } elseif ($param[0] == '^') {
                        $func = substr($param, 1);
                        $count = 0;
                        if (strpos($func, '::') !== false) {
                            $func = explode('::', $func);
                        }
                        foreach (SteelBot::$timers as $k=>$v) {
                            foreach ($v as $kk=>$vv) {
                                if ( $func == $vv ) {
                                    array_splice(SteelBot::$timers[$k],$kk,1);
                                    $count++;
                                }    
                            }                           
                        }                      
                    }
                  SteelBot::SyncTimers();  
                  SteelBot::Msg(self::_('cmdtimer_4', $count));  
                  break;
        default: 
            if (count(SteelBot::$timers)) {
                $first_timer = min(array_keys(SteelBot::$timers));
                $wait = $first_timer - time();
                $first_timer = date("d M Y H:i:s", $first_timer);
                 
                $timers_count = 0;
                foreach (SteelBot::$timers as $label) {
                    $timers_count += count($label);
                }
                $msg = self::_('cmdtimer_5', $timers_count, $first_timer, $wait);
            } else {
                $msg = self::_('cmdtimer_6');
            }
            SteelBot::Msg($msg);    
                     
    }
}


}


SteelBotAdmin::$lng = new SteelBotLng( 'ru', 'ru' );
SteelBotAdmin::$lng->AddDict( dirname(__FILE__).'/'.SteelBot::$cfg['language'].'.php' );
SteelBotAdmin::$lng->AddDict( dirname(__FILE__).'/ru.php' );
foreach (SteelBotAdmin::$commands as $k=>$v) {
    SteelBotAdmin::$commands[$k]['helpstr'] = SteelBotAdmin::$lng->GetTranslate('cm_'.strtolower($k));
}