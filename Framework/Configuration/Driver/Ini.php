<?php

namespace Framework\Configuration\Driver;

use Framework\Configuration\Exception as Exception;
use Framework\ArrayMethods;
use Framework\Configuration as Configuration;

class Ini extends Configuration\Driver {
	
	public function parse($path)
	{
		if (empty($path))
		{
			throw new Exception\Argument("\$path argument is not valid");
		}
		
		// checks to see if the requested config file has not already been parsed
		if (!isset($this->_parsed[$path]))
		{
			$config = array();
			ob_start();
				include (APP_PATH . DS . "application" . DS . "{$path}.ini");
				$string = ob_get_contents();
			ob_end_clean();
			
			//var_dump($string);
			$pairs = parse_ini_string($string);
			
			if ($pairs == false)
			{
				throw new Exception\Syntax("Could not parse configuration file");
			}
			
			foreach ($pairs as $key => $value)
			{
				$config = $this->_pair($config, $key, $value);
			}
			
			$this->_parsed[$path] = ArrayMethods::toObject($config);
		}
		
		return $this->_parsed[$path];
	}
	
	protected function _pair($config, $key, $value)
	{
		if (strstr($key, "."))
		{
			$parts = explode(".", $key, 2);
			if (empty($config[$parts[0]]))
			{
				$config[$parts[0]] = array();
			}
			$config[$parts[0]] = $this->_pair($config[$parts[0]], $parts[1], $value);
		}
		else 
		{
			$config[$key] = $value;
		}
		
		return $config;
	}
}
