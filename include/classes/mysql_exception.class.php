<?php

class MySQL_exception extends Exception {
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }
}