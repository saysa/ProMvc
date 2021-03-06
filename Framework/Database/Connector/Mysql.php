<?php

namespace Framework\Database\Connector;

use \Framework\Database\Exception as Exception;

use Framework\Database\Connector;

class Mysql extends Connector {
	
	protected $_service;
	
	/**
	 * @readwrite
	 */
	protected $_host;
	
	/**
	 * @readwrite
	 */
	protected $_username;
	
	/**
	 * @readwrite
	 */
	protected $_password;
	
	/**
	 * @readwrite
	 */
	protected $_schema;
	
	/**
	 * @readwrite
	 */
	protected $_port = "3306";
	
	/**
	 * @readwrite
	 */
	protected $_charset = "utf8";
	
	/**
	 * @readwrite
	 */
	protected $_engine = "InnoDB";
	
	/**
	 * @readwrite
	 */
	protected $_isConnected = false;
	
	/**
	 * Checks if connected to the database
	 * @return boolean
	 */
	protected function _isValidService()
	{
		$isEmpty = empty($this->_service);
		$isInstance = $this->_service instanceof \MySQLi;
		
		if ($this->isConnected && $isInstance && !$isEmpty)
		{
			return true;
		}
		
		return false;
	}
	
	
	
	/**
	 * Connects to the database
	 * @return \Framework\Database\Connector\Mysql
	 */
	public function connect()
	{
		if (!$this->_isValidService())
		{
			$this->_service = new \MySQLi(
				$this->_host,
				$this->_username,
				$this->_password,
				$this->_schema,
				$this->_port		
			);
			
			if ($this->_service->connect_error)
			{
				throw new Exception\Service("Unable to connect to service");
			}
			
			$this->isConnected = true;
		}
		
		return $this;
	}
	
	/**
	 * Disconnects from the database
	 * @return \Framework\Database\Connector\Mysql
	 */
	public function disconnect()
	{
		if ($this->_isValidService())
		{
			$this->isConnected = false;
			$this->_service->close();
		}
		
		return $this;	
	}
	
	/**
	 * Returns a corresponding query instance
	 * @return \Framework\Database\Query\Mysql
	 */
	public function query()
	{
		return new \Framework\Database\Query\Mysql(array(
			"connector" => $this
		));
	}
	
	/**
	 * Executes the provided SQL statement
	 * @param string $sql
	 */
	public function execute($sql)
	{
		if (!$this->_isValidService())
		{
			throw new Exception\Service("Not connected to a valid service");
		}
		
		return $this->_service->query($sql);
	}
	
	/**
	 * Escapes the provided value to make it safe for queries
	 * @param string $value
	 * @throws \Exception
	 * @return string
	 */
	public function escape($value)
	{
		if (!$this->_isValidService())
		{
			throw new Exception\Service("Not connected to a valid service");
		}
		
		return $this->_service->real_escape_string($value);
	}
	
	/**
	 * Returns the ID of the last row to be inserted
	 * @throws Exception\Service
	 */
	public function getLastInsertId()
	{
		if (!$this->_isValidService())
		{
			throw new Exception\Service("Not connected to a valid service");
		}
		
		return $this->_service->insert_id;
	}
	
	/**
	 * Returns the number of rows affected by the last SQL query executed
	 * @throws Exception\Service
	 */
	public function getAffectedRows()
	{
		if (!$this->_isValidService())
		{
			throw new Exception\Service("Not connected to a valid service");
		}
		
		return $this->_service->affected_rows; 
	}
	
	/**
	 * Returns the last error of occur
	 * @throws Exception\Service
	 */
	public function getLastError()
	{
		if (!$this->_isValidService())
		{
			throw new Exception\Service("Not connected to a valid service");
		}
	
		return $this->_service->error;
	}
	
	/**
	 * Converts the class/properties into a valid SQL query,
	 * and ultimately into a physical database table
	 * @param unknown_type $model
	 * @throws Exception\Sql
	 * @return \Framework\Database\Connector\Mysql
	 */
	public function sync($model)
	{	
		$lines = array();
		$indices = array();
		$columns = $model->columns;
		$template = "CREATE TABLE `%s` (\n%s,\n%s\n) ENGINE=%s DEFAULT CHARSET=%s;";
	
		foreach ($columns as $column)
		{
			$raw = $column["raw"];
			$name = $column["name"];
			$type = $column["type"];
			$length = $column["length"];
				
			if ($column["primary"])
			{
				$indices[] = "PRIMARY KEY (`{$name}`)";
			}
				
			if ($column["index"])
			{
				$indices[] = "KEY `{$name}` (`{$name}`)";
			}
				
			switch ($type)
			{
				case "autonumber":
					{
						$lines[] = "`{$name}` int(11) NOT NULL AUTO_INCREMENT";
						break;
					}
				case "text":
					{
						if ($length !== null && $length <= 255)
						{
							$lines[] = "`{$name}` varchar({$length}) DEFAULT NULL";
						}
	
						else
						{
							$lines[] = "`{$name}` text";
						}
						break;
					}
				case "integer":
					{
						$lines[] = "`{$name}` int(11) DEFAULT NULL";
						break;
					}
				case "decimal":
					{
						$lines[] = "`{$name}` float DEFAULT NULL";
						break;
					}
				case "boolean":
					{
						$lines[] = "`{$name}` tinyint(4) DEFAULT NULL";
						break;
					}
				case "datetime":
					{
						$lines[] = "`{$name}` datetime DEFAULT NULL";
						break;
					}
			}
		}
	
		$table = $model->table;
		$sql = sprintf(
				$template,
				$table,
				join(",\n", $lines),
				join(",\n", $indices),
				$this->_engine,
				$this->_charset
		);
	
		$result = $this->execute("DROP TABLE IF EXISTS {$table};");
		
		if ($result === false)
		{
			$error = $this->lastError;
			throw new Exception\Sql("There was an error in the query: {$error}");
		}
		
		$result = $this->execute($sql);
	
		if ($result === false)
		{
			$error = $this->lastError;
			throw new Exception\Sql("There was an error in the query: {$error}");
		}
			
		return $this;
	}
}
