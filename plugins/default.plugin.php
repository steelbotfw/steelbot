<?php

/**
 * default - плагин для SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 1.0
 * 
 * 2008-08-07
 *
 */


function plg_md5($val) {
    if (empty($val)) {
       SteelBot::CmdHelp("md5");
       return; 
    }
	Proto::Msg(md5($val));
}

  
function plg_ip2host($params) {
     if (empty($params)) {
       SteelBot::CmdHelp("ip2host");
       return; 
    }
	 Proto::Msg(gethostbyaddr($params));
}	   
   
function plg_host2ip($host) {
     if (empty($host)) {
       SteelBot::CmdHelp("host2ip");
       return; 
     }
	 Proto::Msg(gethostbyname($host));   
}
	 
function plg_url_enc($val) {    
    if (empty($val)) {
       SteelBot::CmdHelp("url_enc");
       return; 
    }
	Proto::Msg("encoded URL = ".urlencode($val)); 
}	 

function plg_url_dec($val) {
    if (empty($val)) {
       SteelBot::CmdHelp("url_dec");
       return; 
    }
	 Proto::Msg("decoded URL = ".urldecode($val)); 
}	 

SteelBot::RegisterCmd("md5",     "plg_md5",        1,"md5 <string> - вычислить md5 хеш строки");
SteelBot::RegisterCmd("ip2host", "plg_ip2host",    1,"ip2host <ip> - узнать имя хоста по ip-адресу");
SteelBot::RegisterCmd("host2ip", "plg_host2ip",    1,"host2ip <host>- узнать ip-адрес по имени хоста");
SteelBot::RegisterCmd("urle",    "plg_url_enc",    1,"urle <url> - закодировать URL");
SteelBot::RegisterCmd("urld",    "plg_url_dec",    1,"urld <encoded url> - раскодировать URL");



?>