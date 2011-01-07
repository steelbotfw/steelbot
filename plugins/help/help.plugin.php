<?php

/**
 * help - SteelBot plugin
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * 
 */

//SteelBot::ExportInfo('help', '1.0.0', 'nexor'); 
//SteelBot::AddDependence('steelbot', '2.1.2', 'bot');
S::bot()->RegisterCmd( SteelBotHelp::$helpAlias, array('SteelBotHelp', 'help'), 1, 'help - вывести помощь');
S::bot()->eventManager->RegisterEventHandler(EVENT_MSG_UNHANDLED, array('SteelBotHelp', 'notfound'));

class SteelBotHelp {

    public static $helpAlias = 'help';
	public static function help($params) {
        $cm = S::bot()->commandManager;
		if (empty($params)) {
				$helpstr = array();
				foreach ($cm->getAliases() as $alias) {
                    $cmd = $cm->getCommandByAlias($alias);
                    $cmdaccess = $cmd->GetAccess();

					// Показываем команду, только если она подходит пользователю по уровню доступа,
					// и не является администраторской (для администраторских команд свой хелпер)
					//if ( ($cmdaccess <= SteelBot::GetUserAccess()) && ($cmdaccess < 100) ) {
						$helpstr[] = $cmd->GetHelp(BotCommand::HELP_SHORT);
					//}
				}	
				S::bot()->Msg( "Доступные команды: \n".implode("\n",$helpstr) );
		} else {
            S::bot()->Msg("Help for command under construction");
           //self::CmdHelp($val); 
		} 
	}

	/**
	 * @desc Отправляет сообщение со справкой по указанной команде $cmd
	 *
	 * @param string $cmd - имя команды
	 */
	public static function CmdHelp($cmd) {
        /*
        if (array_key_exists($cmd, Steelbot::$aliases)) {
			if ( Steelbot::$aliases[$cmd]->GetAccess() <= Steelbot::GetUserAccess() ) {
				$msg = Steelbot::$aliases[$cmd]->GetHelp(BotCommand::HELP_FULL, $cmd);
				Steelbot::Msg( $msg );
			} else {
				Steelbot::Msg( LNG(LNG_CMDNOACCESS) );
			}
		} else {
			SteelBot::EventRun( new Event(EVENT_HELP_NOTFOUND) );
			Steelbot::Msg( LNG( LNG_HELP_NOTFOUND, array('alias'=>$cmd ) ));
		}
        * */
	}

    public static function notfound($event) {
        $alias = $event->alias;
        S::bot()->Msg("Команда $alias не найдена. Для получения помощи отправьте ".SteelBotHelp::$helpAlias);
    }
}

