<?php
require_once(dirname(__FILE__)."/mysql.cfg.php");

/**
 * SteelBotDB class for Steelbot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 1.1
 * 
 * 2008-09-16
 *
 */

class SteelBotDB implements ISteelBotDB  {
    
    public $table_prefix,
           $dbname,
           $username,
           $password,
           $host,
           $error = false,
           $errno = false;
           
    private $dbhandle,
            $connected = false;
    
    public function __construct() {
        $this->dbname = $GLOBALS['mysql_database'];
        $this->username = $GLOBALS['mysql_user'];
        $this->password = $GLOBALS['mysql_password'];
        $this->table_prefix = $GLOBALS['table_prefix'];
        $this->host = $GLOBALS['mysql_host'];
        
        try {
            $this->Connect();
        } catch (MySQL_exception $e) {
            echo 'Mysql error: '.$e->getMessage()."\n";
        }
        
    }
    
    public function Connect() {
        if ($this->connected) {
            return true;
        }
        
        $this->dbhandle = mysql_connect($this->host, $this->username, $this->password);
        if (!$this->dbhandle) {
            throw new MySQL_Exception(mysql_error(), mysql_errno());
        }
        
        if ( mysql_select_db($this->dbname, $this->dbhandle) ) {
          $this->connected = true; 
          
          $table_users = file_get_contents(dirname(__FILE__).'/steelbot_users.sql');
          
          if ($table_users) {
              $table_users = str_replace('@', $this->table_prefix,$table_users);
              $this->Query($table_users);
          }
          
          $table_data = file_get_contents(dirname(__FILE__).'/steelbot_data.sql');
          if ($table_data) {
              $table_data = str_replace('@', $this->table_prefix,$table_data);
              $this->Query($table_data);
          }
          return true;
          
        } else {
             throw new MySQL_exception( mysql_error(), mysql_errno() );
        }
    }
    
    public function __destruct() {
        mysql_close($this->dbhandle);
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
        $query = "REPLACE INTO ".$this->table_prefix."data 
                        (data, user, dkey) 
                        VALUES ('$value', $user, '$key')";
        $this->Query($query);
        if ($this->errno) echo $this->error;
        return !$this->errno;  
    }
    
    public function ReadData($user, $key) {
        $user = (int)$user;
        $key = $this->EscapeString($key);
        
        $query = "SELECT value FROM ".$this->table_prefix."data WHERE user=$user AND dkey='$key'";
        $value = $this->QueryValue($query);
        return $value;
    }
    
    public function GetDBInfo() {
        return array(
            'author' => 'N3x^0r',
            'version' => '1.0',
            'name' => 'mysqldb'
            );
    }
    
    public function UserAccess($user) {
        $user = (int)$user;
        $query = "SELECT access FROM ".$this->table_prefix."users WHERE user=$user";
        $result = $this->QueryValue($query);
        if (mysql_affected_rows( $this->dbhandle )) {
            return (int)$result;
        } else {
            $this->CreateUser($user); 
            return 1;
        }
    }
    
    public function SetUserAccess($user, $access) {
        $user = (int)$user;
        $access = (int)$access;
        $query = "UPDATE ".$this->table_prefix."users SET access=$access WHERE user=$user";
        $result = $this->Query($query);
        if (mysql_affected_rows($this->dbhandle)) {
            return true;
        } else {
            return $this->CreateUser($user, $access);
        }    
    }
    
    public function Query($query) {
        $this->errno = $this->error = false;
        $return = mysql_query($query, $this->dbhandle);
        $this->errno = mysql_errno($this->dbhandle);
        $this->error = mysql_error($this->dbhandle);
        if ( $this->errno == 2006 ) {
            $this->connected = false;
            try {
                $this->Connect();
            } catch (MySQL_exception $e) {
                echo 'Mysqlerror: '.$e->getMessage()."\n";
            }
            return false;
        }
        return $return;
    }
    
    public function QueryValue($query) {
        $result = $this->Query($query);
        if ($this->errno) {
            //error
            return false;
        } else {
            $result = mysql_fetch_array($result);
            return $result[0];
        }
    }
    
    public function QueryArray($query) {
        $result = $this->Query($query);
        if ($this->errno) {
            //error
            return false;
        } else {
            $return = array();
            while ($row = mysql_fetch_row($result)) {
            	$return[] = $row;
            }
            return $return;
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