<?php

class Kohana_CDM_Node
{
	protected $_model;
	protected $_object;

	public function __construct($model, & $object)
	{
		$this->_model  = $model;
		$this->_object = & $object;
	}

	public function __get($name)
	{
		return isset($this->_object[$name]) ? $this->_object[$name] : NULL;
	}

	public function __set($name, $value)
	{
		$this->_object[$name] = $value;
	}
}