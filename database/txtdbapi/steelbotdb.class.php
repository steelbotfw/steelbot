<?php
require_once(dirname(__FILE__)."/txt-db-api.php");

/**
 * SteelBotDB class for Steelbot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 1.0
 * 
 * 2008-08-21
 *
 */

class SteelBotDB implements ISteelBotDB  {
    
    public $table_prefix = 'steelbot_',
           $error = false,
           $errno = false;
           
    private $dbhandle,
            $connected = false;
    
    public function __construct() {
        
        $this->dbhandle = new Database("steelbot");
        if (!is_object($this->dbhandle)) {
            echo "error connecting to db\n";
            return;
        } else {
            $this->connected = true;  
                     
            $file_users =  dirname(__FILE__)."/databases/steelbot/{$this->table_prefix}users.txt" ;     
            if ( !file_exists($file_users) ) {
                $table_users = file_get_contents(dirname(__FILE__).'/steelbot_users.sql');
                if ($table_users) {
                    $table_users = str_replace('@', $this->table_prefix,$table_users);
                    $this->Query($table_users);
                }
            }
            
            $file_data =  dirname(__FILE__)."/databases/steelbot/{$this->table_prefix}data.txt" ;
            if ( !file_exists($file_data) ) {
                $table_data = file_get_contents(dirname(__FILE__).'/steelbot_data.sql');
                if ($table_data) {                
                    $table_data = str_replace('@', $this->table_prefix,$table_data);                    
                    $this->Query($table_data);
                }     
            }
        }
        
    }
    
    public function __destruct() {
    }
    
    public function Flush() {
        return true;    
    }
    
    public function CreateUser($user, $access = 1) {
        $registered = time();
        $user = (int)$user;
        $access = (int)$access; 
        $query = "INSERT INTO ".$this->table_prefix."users (user, access, registered) 
                        VALUES ($user, $access, $registered)";
        $this->Query($query);
        return (!$this->errno);
    }
    
    public function DeleteUser($user) {
        $user = (int)$user;
        $query = "DELETE FROM ".$this->table_prefix."users WHERE user=$user";
        $this->Query($query);
        return (!$this->errno);
    }
    
    public function UserExists($user) {
        $user = (int)$user;
        $query = "SELECT COUNT(*) FROM ".$this->table_prefix."users WHERE user=$user";
        $exists = $this->QueryValue($query);
        return $exists;
        
    }
    
    public function WriteData($user, $key, $value) {
        $value = $this->EscapeString($value);
        $key = $this->EscapeString($key);
        $user = (int)$user;
        
        $query_select = "SELECT user FROM ".$this->table_prefix."data WHERE user=$user AND dkey='$key'";
        echo $query_select."\n";
        
        $time = time();
        $r = $this->Query($query_select);
        if ( $r->getRowCount() ) {            
            $query = "UPDATE ".$this->table_prefix."data SET 
                        data='$value', last_edit=$time WHERE user=$user AND dkey='$key'";
        } else {
            $query = "INSERT ".$this->table_prefix."data SET 
                        data='$value' , user=$user , dkey='$key', last_edit=$time";
        }      
        
        $this->Query($query);
        if ($this->errno) echo $this->error;
        return !$this->errno;  
    }
    
    public function ReadData($user, $key) {
        $user = (int)$user;
        $key = $this->EscapeString($key);
        
        $query = "SELECT data FROM ".$this->table_prefix."data WHERE user=$user AND dkey='$key'";
        $value = $this->QueryValue($query);
        return $value;
    }
    
    public function GetDBInfo() {
        return array(
            'author' => 'N3x^0r',
            'version' => '1.0',
            'name' => 'txtdbapi'
            );
    }
    
    public function UserAccess($user) {
        $user = (int)$user;
        $query = "SELECT access FROM ".$this->table_prefix."users WHERE user=$user";
        $r = $this->Query($query);
        if ( $r->next() ) {
            return (int)$r->getCurrentValueByNr(0);
        } else {
            $this->CreateUser($user);
            return false;
        }
    }
    
    public function SetUserAccess($user, $access) {
        $user = (int)$user;
        $access = (int)$access;
        $query = "UPDATE ".$this->table_prefix."users SET access=$access WHERE user=$user";
        $updated = $this->Query($query);
        if (!$updated) {
            $this->CreateUser($user, $access);
        }
        return !$this->errno;
    }
    
    public function Query($query) { 
        $this->errno = $this->error = false;
        $return = $this->dbhandle->executeQuery($query);
        if (txtdbapi_error_occurred()) {
            $this->errno = 1;
            $this->error = implode ("\n", txtdbapi_get_errors()); mysql_error();
        }    
        return $return;
    }
    
    public function QueryValue($query) {
        $result = $this->Query($query);
        if ($this->errno) {
            //error
            return false;
        } else {
            $result->next();
            return $result->getCurrentValueByNr(0);
        }
    }
    
    public function QueryArray($query) {
        $result = $this->Query($query);
        if ($this->errno) {
            //error
            return false;
        } else {
            return $result->getValues();
        }
    }
    
    public static function EscapeString($str) {
        if (get_magic_quotes_gpc()) {
            return $str;
        } else {
            return mysql_escape_string($str);
        }
    }
}