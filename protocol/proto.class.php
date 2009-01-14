<?php

/**
 * Proto class for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 1.2
 * 
 * 2008-08-22
 * 
 */

require_once(dirname(__FILE__)."/WebIcqPro.class.php");

//реализация интерфейса
class Proto implements ISteelBotProtocol  {
    
static $icq;

static function Connect($uin,$password) {
    self::$icq = new WebIcqPro();
    self::$icq->setOption('timeout',5);
    self::$icq->setOption('MessageCapabilities', 'utf');
    return self::$icq->connect($uin, $password);
}

static function Disconnect() {
    self::$icq->disconnect();
}

static function Connected() {
    return self::$icq->IsConnected();
}

static function GetMessage() {
    $msg = self::$icq->ReadMessage();
    switch($msg['type']) {
        case 'message': if (isset($msg['encoding']))  {
                            switch ( mb_strtoupper( $msg['encoding']['numset']) ) {
                                case 'LATIN_1': $from = 'windows-1251';
                                    break;
                                 
                                default: $from = $msg['encoding']['numset'];
                                    break;
                            }
			                $msg['realmessage'] = $msg['message'];
				                                    
				            $msg['message'] = mb_convert_encoding(
				                                    $msg['message'], 
				                                     'UTF-8', 
				                                      $from
				            ); 
				                                              
		                }                    
                        return array($msg['from'], $msg['message']);
        case 'accepted':
            
            break;
            
        case 'offlinemessage':
            slog::add('proto', "offline message recieved");
            break;
            
        case 'error':
            slog::add('proto', "Error ".@$msg['code'].": ".@$msg['error']);
            break;
            
        default:
                 return false;
    }
}

static function Error() {
    return self::$icq->error;
}

static function Msg($txt,$touin = false) {
	if (!$touin) {
	    $touin = SteelBot::$sender;
	}
	
	if (SteelBot::$cfg['msg_charset']) {
	    $txt = iconv('utf-8', SteelBotCore::$cfg['msg_charset'], $txt);
	}
	self::$icq->sendMessage($touin,$txt);
}

static function SetStatus($status) {
    self::$icq->setStatus($status);
}

static function SetXStatus($status) {
    self::$icq->SetXStatus($status);
}

}

?>
