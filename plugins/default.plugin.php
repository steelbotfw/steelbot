<?php

/**
 * default - плагин для SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 2.1
 * 
 * 2010-26-12
 *
 */



function plg_md5($val) {
	S::bot()->Msg(md5($val));
}
  
function plg_ip2host($params) {
	 S::bot()->Msg(gethostbyaddr($params));
}	   
   
function plg_host2ip($host) {
	 S::bot()->Msg(gethostbyname($host));   
}
	 
function plg_url_enc($val) {    
	S::bot()->Msg("encoded URL = ".urlencode($val)); 
}	 

function plg_url_dec($val) {
	S::bot()->Msg("decoded URL = ".urldecode($val)); 
}	 

function plg_echo($val) {
    if (!empty($val)) {
        S::bot()->Msg($val);
    }
}


S::bot()->RegisterCmd("md5",     "plg_md5",        1,"%c <string> - вычислить md5 хеш строки");
S::bot()->RegisterCmd("ip2host", "plg_ip2host",    1,"%c <ip> - узнать имя хоста по ip-адресу");
S::bot()->RegisterCmd("host2ip", "plg_host2ip",    1,"%c <host>- узнать ip-адрес по имени хоста");
S::bot()->RegisterCmd("urle",    "plg_url_enc",    1,"%c <url> - закодировать URL");
S::bot()->RegisterCmd("urld",    "plg_url_dec",    1,"%c <encoded url> - раскодировать URL");
S::bot()->RegisterCmd("echo", "plg_echo", 1, "%c - отправить присланную строку");

