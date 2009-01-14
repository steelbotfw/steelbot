<?php

// to change database engine edit db.engine option in config.php file
$db_class = dirname(__FILE__).DIRECTORY_SEPARATOR.$cfg['db.engine'].
                DIRECTORY_SEPARATOR."steelbotdb.class.php";
                
if (!is_readable($db_class)) {
    slog::add('database', "Error: can't load database class");
} else {
    include $db_class;
}