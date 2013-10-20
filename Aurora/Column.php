<?php

namespace Aurora;

class Column
{
	private $name;
	private $type;
	private $nullable;
	private $default;
	private $unique;
	private $primaryKey;
	private $autoIncrement;
	private $value = null;
	
	public function __construct(
		$type,
		$nullable = false,
		$default = null,
		$unique = false,
		$primaryKey = false,
		$autoIncrement = false
	) {
		try {
			$this->__set('type', $type);
			$this->__set('nullable', $nullable);
			$this->__set('unique', $unique);
			$this->__set('default', $default);
			$this->__set('primaryKey', $primaryKey);
			$this->__set('autoIncrement', $autoIncrement);
			if (!is_null($default))
				$this->value = $default;
		} catch (\Exception $e) {
			throw new \Aurora\Error\CreateTableException('Invalid parameters supplied for column creation.');
		}
	}
	
	public function __get($property)
	{
		if (!in_array($property, array_keys(get_object_vars($this))))
			throw new \RuntimeException("{$property} property does not exist.");
		return $this->$property;
	}
	
	public function __set($property, $value)
	{
		switch ($property) {
			case 'nullable':
			case 'unique':
			case 'primaryKey':
			case 'autoIncrement':
				if (!is_bool($value))
					throw new \RuntimeException("{$property} only accepts boolean values.");
				break;
				
			case 'type':
				if (!($value instanceof \Aurora\Type))
					throw new \RuntimeException("{$property} only accepts \Aurora\Type values.");
				break;
			case 'default':
			case 'value':
				if (!$this->type->isValidValue($value) && !is_null($value))
					throw new \RuntimeException("The given value is not valid for the column type.");
				break;
			case 'name':
				if (!is_string($value))
					throw new \RuntimeException('name property only accepts string values.');
				break;
		}
		
		$this->$property = $value;
	}
	
	public function __toString()
	{
		$strValue = "{$this->name} {$this->type->getRepresentation()}";
		if (!$this->nullable)
			$strValue .= ' NOT NULL';
		if ($this->unique)
			$strValue .= ' UNIQUE';
		if (!is_null($this->default))
			$strValue .= " DEFAULT '{$this->default}'";
		if ($this->autoIncrement)
			$strValue .= ' AUTO_INCREMENT';
		if ($this->primaryKey)
			$strValue .= ' PRIMARY KEY';
			
		return $strValue;
	}
}