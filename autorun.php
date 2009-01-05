<?php

Proto::SetStatus('STATUS_ONLINE');
SteelBot::RegisterCmd('help', array('SteelBot', 'help'), 1, 'help - вывести помощь');