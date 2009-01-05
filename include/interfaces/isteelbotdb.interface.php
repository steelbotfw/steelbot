<?php

/**
 * ISteelBotDB interface for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 1.2
 * 
 * 2008-08-26
 * 
 */

interface ISteelBotDB {
    public function GetDBInfo();
    public function CreateUser($user);
    public function DeleteUser($user);
    public function UserExists($user);
    public function UserAccess($user);
    public function SetUserAccess($user, $access);
    public function WriteData($user, $key, $value);
    public function ReadData($user, $key); 
    public function Flush();

}