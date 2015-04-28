<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Represents an ECS subparticipant
 * 
 */
class ilECSSubParticipant
{
	public $realm = '';
	public $description = '';
	public $communities = array();
	public $dns = '';
	public $auth_ids = array();
	public $community_selfrouting = FALSE;
	public $name = '';
	public $events = TRUE;
	public $email = '';
	
	
	private $json;
	private $part_id;
	private $mid;
	
	/**
	 * Constructor
	 * @param type $a_json
	 */
	public function __construct($a_json = '')
	{
		$this->json = $a_json;
		$this->read();
	}
	
	/**
	 * Get json string
	 * @return type
	 */
	public function getJson()
	{
		return $this->json;
	}
	
	public function setId($a_id)
	{
		$this->part_id = $a_id;
	}
	
	public function getId()
	{
		return $this->part_id;
	}
	
	/**
	 * get first cookie
	 */
	public function getCookie()
	{
		foreach($this->auth_ids as $nr => $auth)
		{
			return $auth['auth_id'];
		}
		return '';
	}
	
	public function getMid()
	{
		return $this->mid;
	}
	
	public function resetCommunities()
	{
		$this->communities = array();
	}
	
	public function addCommunity($a_com)
	{
		$this->communities[] = $a_com;
	}
	
	public function getCommunities()
	{
		return $this->communities;
	}
	
	
	/**
	 * Read from json
	 */
	protected function read()
	{
		if(is_object($this->getJson()))
		{
			$this->realm = $this->getJson()->realm;
			$this->description = $this->getJson()->description;
			$this->communities = (array) $this->getJson()->communities;
			$this->dns = $this->getJson()->dns;
			
			$counter = 0;
			
			$GLOBALS['ilLog']->write(__METHOD__.': '.$this->getJson()->auth_ids);
			
			foreach((array) $this->getJson()->auth_ids as $auth_id)
			{
				$this->auth_ids[$counter]['desc'] = (string) $auth_id->desc;
				$this->auth_ids[$counter]['auth_id'] = (string) $auth_id->auth_id;
			}
			
			$this->community_selfrouting = $this->getJson()->community_selfrouting;
			$this->name = $this->getJson()->name;
			$this->events = $this->getJson()->events;
			$this->email = $this->getJson()->email;

			foreach((array) $this->getJson()->memberships as $membership)
			{
				foreach((array) $membership->participants as $participant)
				{
					if($participant->itsyou == TRUE)
					{
						$GLOBALS['ilLog']->write(__METHOD__.': Found mid '.$participant->mid);
						$this->mid = $participant->mid;
					}
				}
			}
		}
	}
	
}
?>