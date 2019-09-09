<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Storage for created ecs ressources
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSViPLabRessource 
{
	const RES_SUBPARTICIPANT = 'subparticipant';
	const RES_EXERCISE = 'exercise';
	const RES_EVALUATION = 'evaluation';
	const RES_SOLUTION = 'solution';
    
	private $creation_time;
	private $ressource_type;
	private $ressource_id;
	private $id;
	
	private $db;
	
	/**
	 * Constructor
	 */
	public function __construct($a_id = 0)
	{
		$this->db = $GLOBALS['ilDB'];
		
		if($a_id)
		{
			$this->id = $a_id;
			$this->read();
		}
	}
	
	/**
	 * Get creation time
	 * @return int
	 */
	public function getCreationDate()
	{
		return $this->creation_time;
	}
	
	/**
	 * Set ressource type
	 */
	public function setRessourceType($a_type)
	{
		$this->ressource_type = $a_type;
	}
	
	public function getRessourceType()
	{
		return $this->ressource_type;
	}
	
	public function setRessourceId($a_id)
	{
		$this->ressource_id = $a_id;
	}
	
	/**
	 * Get ressource id
	 */
	public function getRessourceId()
	{
		return $this->ressource_id;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Get db object
	 * @return ilDB
	 */
	protected function getDB()
	{
		return $this->db;
	}
	
	/**
	 * create new entry
	 */
	public function create()
	{
		$new_id = $this->getDB()->nextId('il_qpl_qst_viplab_res');
		$query = 'INSERT INTO il_qpl_qst_viplab_res (id, res_id, ecs_res, create_dt) '.
				'VALUES ( '.
				$this->getDB()->quote($new_id, 'integer').', '.
				$this->getDB()->quote($this->getRessourceId(),'integer').', '.
				$this->getDB()->quote($this->getRessourceType(),'text').', '.
				$this->getDB()->quote(time(), 'integer').' '.
				') ';
		$this->getDB()->manipulate($query);
		$this->id = $new_id;
		
		return true;
	}
	
	/**
	 * Delete entry
	 */
	public function delete()
	{
		$query = 'DELETE FROM il_qpl_qst_viplab_res '.
				'WHERE id = '.$this->getDB()->quote($this->getId(),'integer');
		$this->getDB()->manipulate($query);
		return true;
	}
	
	/**
	 * Read entry from db
	 */
	public function read()
	{
		$query = 'SELECT * FROM il_qpl_qst_viplab_res '.
				'WHERE id = '. $this->getDB()->quote($this->getId(),'integer');
		$res = $this->getDB()->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$this->id = $row->id;
			$this->ressource_id = $row->res_id;
			$this->ressource_type = $row->ecs_res;
			$this->creation_time = $row->create_dt;
		}
		return true;
	}
}
?>
