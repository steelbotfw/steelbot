<?php

/**
 * SteelBotDB class for Steelbot
 *
 * http://steelbot.net
 *
 * @author N3x^0r
 * @version 1.2.0
 *
 */

class SteelBotDB extends SDatabase  {

    public $table_prefix,
           $dbname,
           $username,
           $error = false,
           $errno = false;

    private $dbhandle,
            $password,
            $host,
            $connected = false,
			$setnames,
            $options_table;

    const ER_DUP_ENTRY = 1062;
    const ER_EMPTY_QUERY = 1065;
    const CR_SERVER_GONE_ERROR = 2006;

    /**
     * Конструктор класса
     */
    public function __construct($bot) {
		parent::__construct($bot);
    }
	
    /**
     * Деструктор класса.
     * Автоматически отключает бота от БД.
     */
    public function __destruct() {
        $this->Disconnect();
    }

    /**
     * Получение информации о базе данных, ее версии и авторе.
     * @return array
     */
    public function GetDBInfo() {
        return array(
            'author' => 'nexor',
            'version' => '1.2.0',
            'name' => 'mysqldb'
            );
    }

    /**
     * Подключение к БД.
     * @return bool
     */
    public function Connect() {

        $this->dbname   = S::bot()->config['db']['database'];
        $this->username = S::bot()->config['db']['user'];
        $this->password = S::bot()->config['db']['password'];
        $this->table_prefix = S::bot()->config['db']['table_prefix'];
        $this->host = S::bot()->config['db']['host'];
		$this->setnames = S::bot()->config['db']['setnames'];
        $this->options_table = S::bot()->config['db']['table_config'];
        
        if (!function_exists('mysql_ping')) {
            throw new db_Exception("Fatal error: mysql extension must be loaded", 0);
        }
        
        if (@mysql_ping($this->dbhandle)) {
            return true;
        }        

        
        S::logger()->log('mysql_connect()... ', 'mysqldb');
        
        
        $this->dbhandle = mysql_connect($this->host, $this->username, $this->password);
        if (!$this->dbhandle) {
            
            throw new db_Exception(mysql_error(), mysql_errno());
        }
        
        S::logger()->log('mysql_select_db()... ', 'mysqldb');
		
		if (!empty($this->setnames)) {
			mysql_query("SET NAMES '".$this->setnames."'");
		}
		
        if ( mysql_select_db($this->dbname, $this->dbhandle) ) {
            $this->connected = true;

            $this->InstallTable(dirname(__FILE__).'/steelbot_users.sql');
			$this->UpdateTable('users');

            $this->InstallTable(dirname(__FILE__).'/steelbot_commands.sql');
            $this->UpdateTable('commands');

            $this->InstallTable(dirname(__FILE__).'/steelbot_options.sql');
            $this->UpdateTable('options');

            $this->InstallTable(dirname(__FILE__).'/steelbot_aliases.sql');
            $this->UpdateTable('aliases');

            if (class_exists('Event')) {
                S::bot()->eventManager->EventRun(new Event(EVENT_DB_CONNECTED, array('dbname'=>$this->dbname)));
            } else {
                echo "Connected to db\n";
            }
            return true;

        } else {
            throw new db_exception( mysql_error($this->dbhandle), mysql_errno($this->dbhandle) );
        }
    }

    /**
     *
     * @param string $filename
     */
    public function InstallTable($filename) {
	    $sql = file_get_contents($filename);
        $basename = basename($filename);
        
        S::logger()->log("Installing table from $basename ... ", 'mysqldb');
	    if ($sql) {
                  
                  $sql = str_replace('@', $this->table_prefix, $sql);
				  $queries = explode(";\n", $sql);
				  foreach ($queries as $q) {
					$q = trim($q);
					if (!empty($q)) {
						$this->Query($q);
				    }
				  }
                
              } else {
                  S::logger()->log("Warning: error installing table from $basename", 'mysqldb');                  
              }
    }

    /**
     *
     * @param string $table
     */
    public function UpdateTable($table) {
        $scripts = glob('upgrade.'.$table.'.php');
        sort($scripts);
        foreach ($scripts as $script) {
            include $script;
        }
    }

    /**
     * Отключение от БД.
     */
    public function Disconnect() {
        @mysql_close($this->dbhandle);
        $this->connected = false;
    }

    /**
     * Заглушка для метода Flush()
     * @return true
     */
    public function Flush() {
        return true;
    }

    /**
     * Создать пользователя с идентификатором $user и правами доступа $access
     *
     * @param string $user
     * @param int $access
     * @return int - установленный уровень доступа
     */
    public function CreateUser($user, $access = -1) {
        $user = self::EscapeString($user);
        if ($access < 0) {
			$access = S::bot()->config['bot']['user.default_access'];
		}

        $access = (int)$access;
        $this->EscapedQuery(
            "INSERT INTO ".$this->table_prefix."users SET
                user={user},
                access={access}",
            array(
                'user' => $user,
                'access' => $access
            )
        );
        return $access;
    }

    /**
     * Удалить пользователя $user из БД.
     *
     * @param string $user
     * @return bool
     */
    public function DeleteUser($user) {
        $query = $this->formatQuery(
            "DELETE FROM ".$this->table_prefix."users WHERE user={user}",
            array(
                'user' => $user
            )
        );
        $this->Query($query);
        return (!$this->errno);
    }

    /**
     * Проверить, является ли пользователь $user зарегистрированным в БД.
     *
     * @param string $user
     * @return bool
     */
    public function UserExists($user) {
        $query = $this->formatQuery(
            "SELECT id FROM ".$this->table_prefix."users WHERE user={user}",
            array('user' => $user)
        );
        $exists = $this->QueryValue($query);
        return $exists;
    }

    /**
     * Получить уровень доступа пользователя $user
     *
     * @param string $user
     * @return int
     */
    public function GetUserAccess($user) {
        $user = self::EscapeString($user);
        $query = "SELECT access FROM ".$this->table_prefix."users WHERE user='$user'";
        $result = $this->QueryValue($query);
        if (mysql_affected_rows( $this->dbhandle )) {
            return (int)$result;
        } else {
            return $this->CreateUser($user);
        }
    }

    /**
     * Установить уровень доступа пользователя.
     *
     * @param string $user
     * @param int $access
     * @return bool
     */
    public function SetUserAccess($user, $access) {
        $result = $this->EscapedQuery(
            "UPDATE ".$this->table_prefix."users
             SET access={access} WHERE user={user}",
             array(
                'access' => $access,
                'user' => $user
             )
        );
        if (mysql_affected_rows($this->dbhandle)) {
            return true;
        } else {
            return $this->CreateUser($user, $access);
        }
    }

    /**
     * Установить уровень доступа к команде.
     *
     * @param string $plugin
     * @param string $command
     * @param int $access
     * @return bool
     */
    public function SetCmdAccess($plugin, $command, $access) {
        if (empty($command)) {
            throw new db_exception("Command name must not be empty");
        }
        $query = $this->FormatQuery(
			"INSERT INTO ".$this->table_prefix."commands
                  SET access={access},
                      plugin={plugin},
                      command={cmd}
                  ON DUPLICATE KEY UPDATE
                  access={access}",
             array(
				'access' => $access,
				'plugin' => $plugin,
				'cmd' => $command
             )
        );
        $updated = $this->Query($query);
        S::logger()->log("Access for '$command' changed to $access", 'mysqldb');
        return $updated;
    }

	/**
	 * Получить все алиасы команды
	 * @param string $plugin
	 * @param string $command
	 */
    public function GetAliases($plugin, $command) {
		$r = $this->EscapedQuery(
			"SELECT alias FROM ".$this->table_prefix."aliases
				WHERE `plugin` = {plugin}
				AND   `command` = {command} 
			",
			array(
				'plugin' => $plugin,
				'command' => $command
			)
		);
		$result =  array();
		while ($row = mysql_fetch_row($r)) {
			$result[] = $row[0];
		}
		return $result;
	}

	public function DeleteAlias($plugin, $name, $alias) {
		$r = $this->EscapedQuery(
			"DELETE FROM ".$this->table_prefix."aliases
				WHERE `plugin` = {plugin} AND
					  `command` = {command} AND
					  `alias` = {alias}",
			array(
				'plugin'  => $plugin,
				'command' => $command,
				'alias'   => $alias
			)
		);
		return $this->RowsAffected();
	}

	public function AddAlias($plugin, $command, $alias) {
		$this->EscapedQuery(
			"INSERT INTO ".$this->table_prefix."aliases SET
					`plugin` = {plugin},
					`command` = {command},
					`alias` = {alias}
			 ON DUPLICATE KEY UPDATE `alias`={alias}",
			 array(
				'plugin' => $plugin,
				'command' => $command,
				'alias' => $alias
			 )
		);
	}

	public function AliasExists($plugin, $command, $alias) {
		$q = $this->FormatQuery(
			"SELECT COUNT(*) FROM ".$this->table_prefix."aliases
			 WHERE `plugin` => {plugin},
				   `command` => {command},
				   `alias` => {alias}",
		     array(
				'plugin' => $plugin,
				'command' => $command,
				'alias' => $alias
		     )
		);

		return $this->QueryValue($q);		
	}

    /**
     * Получить уровень доступа к команде.
     *
     * @param string $plugin
     * @param string $command
     * @return int
     *
     * @todo кеширование результата
     */
    public function GetCmdAccess($plugin, $command) {
        $r = $this->EscapedQuery(
			"SELECT access FROM ".$this->table_prefix."commands WHERE ".
             "plugin={plugin} AND command={command}",
             array(
			     'plugin' => $plugin,
			     'command' => $command	
             )
        );
        if (mysql_num_rows($r)) {
            $r = mysql_fetch_row($r);
            return $r[0];
        } else {
            return -1;
        }
    }

    public function SetOption($option, $value, $type, $id=0) {
        $table = $this->GetTableNames('options');
        switch ($type) {
            case SteelBot::OPTBOT:
            case SteelBot::OPTPLUGIN:
            case SteelBot::OPTPROTOCOL:
                break;
            default:
                throw new db_exception("Unknown option type: $type");
        }

        $q = $this->FormatQuery(
                "INSERT INTO `$table` SET
                    id = {id},
                    type = {type},
                    name = {name},
                    value = {value},
                    datatype = {datatype}
                    ON DUPLICATE KEY UPDATE
                    value= {value}",
				array(
					'id' => $id,
					'type' => $type,
					'name' => $option,
					'value' => is_array($value)?serialize($value):$value,
					'datatype' => is_array($value)?'array':'string'
				)
        );
        $this->query($q);        
    }

    public function GetOption($option, $type=Steelbot::OPTBOT, $id=0) {
        switch ($type) {
            case SteelBot::OPTBOT:
            case SteelBot::OPTPLUGIN:
                $table = $this->GetTableNames('options');
                $r = $this->EscapedQuery(
                    "SELECT value, datatype FROM `$table`
                     WHERE `type`={type} AND id={id} AND `name`={name}",
                    array(
                        'type' => $type,
                        'id'   => $id,
                        'name' => $option
                    )
                );
                if ($this->NumRows($r)) {
                    $row = $this->fetchAssoc($r);
                    switch ($row['datatype']) {
                        case 'string' :
                            return $row['value'];

                        case 'array':
                            return unserialize($row['value']);

                        default:
                            trigger_error("Unknown datatype: {$row['datatype']}", E_USER_WARNING);
                            return $row['value'];
                    }
                } else {
                    throw new BotException("Unknown config option: $option,  type=$type",BotException::UNKNOWN_CONFIG_OPTION);
                }
                break;
            default:
				throw new BotException("Unknown option type: $type", 0);
        }
    }

	public function DeleteOption($option, $type=SteelBot::OPTBOT, $id=0) {
		$table = $this->GetTableNames('options');
      
        $q = $this->FormatQuery(
                "DELETE FROM `$table` WHERE
                    id = {id} AND
                    type = {type} AND
                    name = {name}",
				array(
					'id' => $id,
					'type' => $type,
					'name' => $option
				)
        );
        $this->query($q);
        return $this->RowsAffected();    
	}

    /**
     * Сделать запрос к БД и получить ссылку на mysql-результат.
     *
     * @param string $query
     * @return resource
     */
    public function Query($query) {
        $this->errno = $this->error = false;
        //echo "\n=======\n";
        //print_r($query);
        //echo "\n=======\n";
        $return = mysql_query($query, $this->dbhandle);
        $this->errno = mysql_errno($this->dbhandle);
        $this->error = mysql_error($this->dbhandle);

        // проверка работоспособности сервера
        if ( $this->errno == self::CR_SERVER_GONE_ERROR ) {
            try {
                $this->Disconnect();
                $this->Connect();
                $return = mysql_query($query, $this->dbhandle);
                $this->errno = mysql_errno($this->dbhandle);
                $this->error = mysql_error($this->dbhandle);
            } catch (db_exception $e) {
                S::logger()->add('Mysqlerror: '.$e->getMessage(), 'mysqldb');
            }
        }

        if ($this->errno == self::ER_EMPTY_QUERY) {
            return false;
        }
        
        if ($this->errno) {
            var_dump($this->errno);
            throw new db_exception($this->error, $this->errno);
        }
        return $return;
    }

	/**
	 * Запрос, автоматически экранирующий пользовательские данные
	 *
	 * @param string $query
	 * @param array $data
	 */
	public function EscapedQuery($query, $data) {
		$keys = array_keys($data);
		$values = array_values($data);
		
		foreach ($keys as &$k) {
			$k = '{'.$k.'}';
		}
		foreach ($values as &$v) {
			$v = "'".mysql_real_escape_string($v, $this->dbhandle)."'";
		}

        $query = str_replace($keys, $values, $query);
         
		return $this->query( $query );
	}

	public function FormatQuery($query, $data) {
		$keys = array_keys($data);
		$values = array_values($data);
		
		foreach ($keys as &$k) {
			$k = '{'.$k.'}';
		}
		foreach ($values as &$v) {
			$v = "'".mysql_real_escape_string($v, $this->dbhandle)."'";
		}
		
		return str_replace( $keys, $values, $query);
	}
	
	/**
	 * Извлечь строку из mysql результата в виде ассоциативного массива
	 */
	public function FetchAssoc($r) {
		return mysql_fetch_assoc($r);
	}
	
	/**
	 * Извлечь строку из mysql результата в виде неассоциативного массива
	 */
	public function FetchRow($r) {
		return mysql_fetch_row($r);
	}
	
	/**
	 * Получить количество строк в mysql результате
	 */
	public function NumRows($r) {
		return mysql_num_rows($r);
	}
	
    /**
     * Сделать запрос к БД, и получить результат в виде единственного значения.
     *
     * @param string $query
     * @return string
     */
    public function QueryValue($query) {
        $result = $this->Query($query);
        $result = mysql_fetch_array($result);
        return $result[0];
    }

    /**
     * Сделать запрос к БД и получить результат в виде двумерного массива
     * значений.
     *
     * @param string $query
     * @return array
     */
    public function QueryArray($query) {
        $result = $this->Query($query);        
		$return = array();
		while ($row = mysql_fetch_row($result)) {
			$return[] = $row;
		}
		return $return;
    }

    /**
     * Получить массив с именами таблиц для бота.
     *
     * @return array
     */
    public function GetTableNames($filter=null) {
	    $tables = array( 
			'user'     => $this->table_prefix.'users',
            'commands' => $this->table_prefix.'commands',
			'options'  => $this->table_prefix.$this->options_table,
			'aliases'  => $this->table_prefix.'aliases'
        ); 
		if ($filter) {
			return $tables[$filter];
		} else {
			return $tables;
		}
    }

    /**
     * Экранировать опасные для sql запроса символы в строке.
     *
     * @param string $str
     * @return string
     */
    public function EscapeString($str) {
        return mysql_real_escape_string($str, $this->dbhandle);
    }

    public function RowsAffected() {
        return mysql_affected_rows($this->dbhandle);
    }
}
