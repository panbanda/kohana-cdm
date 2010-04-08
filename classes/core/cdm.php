<?php

/**
 * CDM :: Cassandra Data Model
 * 
 * This class aims to create ORM-like for NoSQL environments within Cassandra.
 */

class Core_CDM {
	
	// Config file contents
	protected $_config;
	
	// Column family name ex: Users
	protected $_column_family;
	
	// If the column is super
	protected $_is_supercolumn = TRUE;
	
	// Key to update in Cassandra (primary key)
	protected $_key;
	
	// Keyspace for queries
	protected $_keyspace;
	
	// Object to be updated in Cassandra, defined in model
	protected $_object;
	
	// Manager for columns
	protected $_manager;
	
	/**
	 * Factory a new Cassandra communication model
	 *
	 * @param  $keyspace  Application keyspace in storage-conf.xml
	 * @return  Cassandra
	 */
	public static function factory($model_name, $key = NULL)
	{
		$model = "Model_" . ucfirst($model_name);
		
		return new $model($key);
	}
	
	public function __construct($key = NULL)
	{
		$config = $this->_config = Kohana::config('cdm');
	
		// Find the application keyspace if not already set
		$this->_keyspace = !is_null($this->_keyspace) ? $this->_keyspace : $config->default['keyspace'];
		
		// Assign the key to the object
		$this->_key = $key;
		
		// Assign thrift vendor root
		$GLOBALS['THRIFT_ROOT'] = $config->thrift;
		
		// Include necessary files
		require_once $GLOBALS['THRIFT_ROOT'].'/packages/cassandra/Cassandra.php';
		require_once $GLOBALS['THRIFT_ROOT'].'/packages/cassandra/cassandra_types.php';
		require_once $GLOBALS['THRIFT_ROOT'].'/transport/TSocket.php';
		require_once $GLOBALS['THRIFT_ROOT'].'/protocol/TBinaryProtocol.php';
		require_once $GLOBALS['THRIFT_ROOT'].'/transport/TFramedTransport.php';
		require_once $GLOBALS['THRIFT_ROOT'].'/transport/TBufferedTransport.php';
		
		// Include the PHPCassa awesomeness
		require_once Kohana::find_file('vendor', 'phpcassa/phpcassa');
		require_once Kohana::find_file('vendor', 'phpcassa/uuid');
		
		// Attach servers
		foreach ($config->connection['servers'] as $server)
		{
			CassandraConn::add_node($server['host'], $server['port']);
		}
		
		// Create column manager
		$this->_manager = new CassandraCF($this->_keyspace, $this->_column_family, $this->_is_supercolumn);
	}
	
	/**
	 * Assigns values to the object from an array of data
	 * 
	 * @param  $data  Array of key => values
	 */
	public function values($data = NULL)
	{		
		// Populate the object
		foreach ($this->_object as $key => $value)
		{
			if (in_array($key, array_keys($data)))
			{
				$this->_object[$key] = $data[$key];
			}
		}
		
		return $this;
	}
	
	public function save()
	{
		// See that a key is supplied
		if ($this->_key === NULL)
		{
			throw new Kohana_Exception('No key for :model update has been supplied',
				array(':model' => $this->_column_family));
		}
		
		// Objecting being sent
		$data = array();
		
		// Remove null values
		foreach ($this->_object as $key => $value)
		{
			$value = is_null($value) ? '' : $value;
			
			$data[$key] = $value;
		}
		
		// Need to remap if it is a supercolumn
		if ($this->_is_supercolumn)
		{
			$cfmap[$this->_column_family] = $data;
			
			$data = $cfmap;
		}
		
		return $this->_manager->insert($this->_key, $cfmap);
	}
	
	/**
	 * Get one or multiple keys
	 *
	 * @param  $keys  array|string
	 */
	public function get($keys = NULL)
	{
		// See that a key is supplied
		if ($keys === NULL)
		{
			throw new Kohana_Exception('No key for :model update has been supplied',
				array(':model' => $this->_column_family));
		}
		
		// Finding multiple keys
		if (is_array($keys))
		{
			$result = array();
			
			foreach ($keys as $key)
			{
				$result[] = $this->_get($key);
			}
		}
		
		else
		{
			// Finding one key
			$result = $this->_get($keys);
		}
		
		return $result;
	}
	
	protected function _get($key)
	{
		$model = new $this($key);
		
		// Search cassandra
		$data = $this->_manager->get($key);
		
		// Need to revert data back to simple array
		if ($this->_is_supercolumn)
		{
			$data = $data[$this->_column_family];
		}
		
		$model->values($data);
		
		return $model;
	}
	
	/**
	 * Populates model from response
	 *
	 * @param  $response  Response from Cassandra, column or supercolumn array
	 */
	protected function _populate_object($data)
	{
		// Clear any previous content
		$this->clear();
		
		// Make sure data was returned, otherwise clear object
		if ($data === NULL) 
		{
			$this->_key = NULL;
			
			return false;
		}
		
		// Assign values back into object
		foreach ($data as $set)
		{
			// Foreach sets of data, mostly returning one
			foreach ($set as $key => $value)
			{
				// If it exists in the array, set it
				if (in_array($key, array_keys($this->_object)))
				{
					$this->_object[$key] = $value;
				}
			}
		}
	}
	
	/**
	 * Clears the data from the model
	 */
	public function clear()
	{
		foreach ($this->_object as &$value)
		{
			$value = NULL;
		}
	}
	
	public function __get($key)
	{
		if (in_array($key, $this->_object))
		{
			return $this->_object[$key];
		}
	}
}