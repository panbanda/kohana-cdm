<?php defined('SYSPATH') or die('No direct script access.');

/**
 * CDM :: Cassandra Data Model
 *
 * This class aims to create ORM-like for NoSQL environments within Cassandra.
 */

class Kohana_CDM extends ArrayObject {

	// If Cassandra is connected
	protected static $_connected = FALSE;

	// Cassandra managers
	protected static $_managers = array();

	/**
	 * Factory a new Cassandra communication model
	 *
	 * @param  name  model name
	 * @param  key   key of record to load
	 * @return CDM
	 */
	public static function factory($name, $key = NULL)
	{
		$model = "Model_CDM_".ucfirst($name);

		return new $model($key);
	}

	// Column family name ex: Users
	protected $_column_family;

	// If the column is super
	protected $_is_supercolumn = TRUE;

	// Record key
	protected $_key;

	// Keyspace for queries
	protected $_keyspace;

	// Manager for columns
	protected $_manager;

	/**
	 * Constructs a new CDM model
	 *
	 * @param  key  record key to load
	 */
	public function __construct($key = NULL)
	{
		if ( ! CDM::$_connected)
		{
			foreach (Kohana::config('cdm')->connection['hosts'] as $host)
			{
				// Connect to cassandra nodes
				CassandraConn::add_node($host['hostname'], $host['port']);
			}

			CDM::$_connected = TRUE;
		}

		if ( ! isset($this->_keyspace))
		{
			// Find the application keyspace if not already set
			$this->_keyspace = Kohana::config('cdm')->keyspace;
		}

		if ( ! isset($this->_column_family))
		{
			// Use singular version of model name for the column family
			$this->_column_family = Inflector::singular(strtolower(substr(get_class($this), 10)));
		}

		if ( ! isset(CDM::$_managers[$this->_keyspace][$this->_column_family]))
		{
			// Create column manager
			CDM::$_managers[$this->_keyspace][$this->_column_family] = new CassandraCF($this->_keyspace, $this->_column_family, $this->_is_supercolumn);
		}

		$this->_manager = CDM::$_managers[$this->_keyspace][$this->_column_family];

		// This is down here because of major PHP bug #45622
		parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);

		if ($key !== NULL)
		{
			// Load initial record
			$this->find($key);
		}
	}

	/**
	 * Updates or creates a record
	 *
	 * @return void
	 */
	public function save()
	{
		// See that a key is supplied
		if ($this->_key === NULL)
		{
			throw new Kohana_Exception('No key for :model update has been supplied',
				array(':model' => $this->_column_family));
		}

		$this->_manager->insert($this->_key, $this->getArrayCopy());
	}

	/**
	 * Get one or multiple records
	 *
	 * @param  string|array    key or keys
	 * @return CDM|ArrayObject result or results
	 */
	public function get($key)
	{
		if (is_array($key))
		{
			// Return group of records
			$results = $this->_manager->multiget($key);
			return $this->_result_group($results);
		}
		else
		{
			// Return individual record
			$this->exchangeArray($this->_manager->get($key));
			$this->_key = $key;

			return $this;
		}
	}

	/**
	 * Grabs a range of records
	 *
	 * @param  string       start key
	 * @param  int          number of records
	 * @return ArrayObject  results
	 */
	public function get_range($start, $count)
	{
		// Return range of records
		$results = $this->_manager->get_range($start, NULL, $count);

		return $this->_result_group($results);
	}

	/**
	 * Generates an ArrayObject of models for a group of results
	 *
	 * @param  array       results
	 * @return ArrayObject results
	 */
	public function _result_group( & $results)
	{
		$object = new ArrayObject();

		foreach ($results as $key => $result)
		{
			$model = clone $this;
			$model->exchangeArray($result);
			$model->key($key);

			$object[$key] = $model;
		}

		return $object;
	}

	/**
	 * Gets and/or sets this model's key
	 *
	 * @param  string  key to set if not NULL
	 * @return string  curreny key value
	 */
	public function key($key = NULL)
	{
		if ($key !== NULL)
		{
			$this->_key = $key;
		}

		return $this->_key;
	}
}