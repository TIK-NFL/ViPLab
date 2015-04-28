<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Represents an ECS exercise
 * 
 */
class ilECSExercise
{
	public $Exercise = NULL;
	
	private $json;
	private $part_id;
	
	/**
	 * Constructor
	 * @param type $a_json
	 */
	public function __construct($a_json = '')
	{
		$this->Exercise = new stdClass();
		$this->json = $a_json;
		$this->read();
	}
	
	public function setExercise($a_exercise)
	{
		$this->Exercise = $a_exercise;
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
			$this->Exercise = $this->getJson()->Exercise;
		}
	}
	
}
?>