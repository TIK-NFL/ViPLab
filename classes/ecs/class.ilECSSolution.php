<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Represents an ECS solution
 * 
 */
class ilECSSolution
{
	public $Solution = NULL;
	
	private $json;
	private $part_id;
	
	/**
	 * Constructor
	 * @param type $a_json
	 */
	public function __construct($a_json = '')
	{
		$this->Solution = new stdClass();
		$this->json = $a_json;
		$this->read();
	}
	
	public function setSolution($a_solution)
	{
		$this->Solution = $a_solution;
	}
	
	/**
	 * Get json string
	 * @return type
	 */
	public function getJson()
	{
		return $this->json;
	}
	
	
	
	/**
	 * Read from json
	 */
	protected function read()
	{
		if(is_object($this->getJson()))
		{
			$this->Solution = $this->getJson()->Solution;
		}
	}
	
}
?>