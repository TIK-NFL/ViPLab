<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Represents an ECS exercise
 * 
 */
class ilECSVipResult
{
	public $Result = NULL;
	
	private $json;
	private $part_id;
	
	/**
	 * Constructor
	 * @param type $a_json
	 */
	public function __construct($a_json = '')
	{
		$this->Result = new stdClass();
		$this->json = $a_json;
		$this->read();
	}
	
	public function setResult($a_exercise)
	{
		$this->Evaluation = $a_exercise;
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
			$this->Evaluation = $this->getJson()->Evaluation;
		}
	}
	
}
?>