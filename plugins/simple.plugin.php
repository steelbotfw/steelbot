<?php

function simple_plugin($param) {
    
        SteelBot::Msg("Вы ввели параметр: ".$param); // отправка сообщения пользователю
   
}

SteelBot::RegisterCmd('test', 'simple_plugin', 1, 'test - тест простого плагина');
SteelBot::RegisterCmd('тест', 'simple_plugin', 1, 'тест - тест команды на русском');