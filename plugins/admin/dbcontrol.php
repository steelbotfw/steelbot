<?php

class AdminDBControl {
    
    static $tables;
    
    static function Info() {
        $info = SteelBot::$database->GetDBInfo();
        $msg = SteelBotAdmin::_('cmddbinfo_1', 
            $info['name'], $info['version'], $info['author']);
        SteelBot::Msg($msg);
    }
    
    static function User($param) {
        list($cmd, $params) = explode(' ', $param, 2);
        switch ($cmd) {
            case 'stat':
                $query = "SELECT COUNT(*) FROM ".self::$tables['user'];
                slog::add('admin', $query);
                $result = SteelBot::$database->QueryValue($query);
                $msg = SteelBotAdmin::_('cmduser_1', $result);
                SteelBot::Msg( $msg );
                break;
                
            
        }
        
    }
    
}

$lang_ru = array(

    'cm_dbinfo' => "Синтаксис:\n.dbinfo - информация об используемой базе данных",
    'cm_user'   => "Синтаксис:\n.user stat - количество пользователей в БД", 
    
    'cmddbinfo_1' => "Информация о БД:\nИдентификатор: %1\nВерсия: %2\nРазработчик: %3\n",
    'cmduser_1'   => "Всего пользователей: %1"

);

AdminDBControl::$tables = SteelBot::$database->GetTableNames();
SteelBotAdmin::$lng->ImportDict('ru', $lang_ru);
