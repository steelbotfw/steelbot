<?php

/**
 * Вспомогательные функции для SteelBot
 *
 * @author N3x^0r
 * 
 */

function func2str($func) {
    if (is_array($func)) {
        if (is_object($func[0])) {
            return get_class($func[0]).'->'.$func[1];
        } else {
            return $func[0].'::'.$func[1];
        }
    } else {
        return $func;
    }
}
