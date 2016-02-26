<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Global viPLab Settings
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilViPLabSettings 
{
	private static $instance = null;
	
	
	private $storage = null;
	
	private $active = FALSE;
	private $ecs = 0;
	private $width = 800;
	private $height = 600;
	private $languages = array();
	
	private $log_level;
	
	
	/**
	 * Singeleton constructor
	 */
	private function __construct()
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$this->storage = new ilSetting('ass_viplab');
		$this->init();
	}
	
	/**
	 * Get songeleton instance
	 * @return ilViPLabSettings
	 */
	public static function getInstance()
	{
		if(!self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	
	public function isActive()
	{
		return $this->active;
	}
	
	public function setActive($a_stat)
	{
		$this->active = $a_stat;
	}
	
	public function getECSServer()
	{
		return $this->ecs;
	}
	
	public function setECSServer($a_server)
	{
		$this->ecs = $a_server;
	}
	
	public function getWidth()
	{
		return $this->width;
	}
	
	public function setWidth($a_width)
	{
		$this->width = $a_width;
	}
	
	public function getHeight()
	{
		return $this->height;
	}
	
	public function setHeight($a_height)
	{
		$this->height = $a_height;
	}
	
	/**
	 * Get enabled languages
	 * @return type
	 */
	public function getLanguages()
	{
		return $this->languages;
	}
	
	public function setLanguages($a_lang)
	{
		$this->languages = $a_lang;
	}
	
	public function getLanguageMid($a_lang_key)
	{
		if(array_key_exists($a_lang_key, $this->getLanguages()))
		{
			return $this->languages[$a_lang_key];
		}
		return 0;
	}
	
	public function getLogLevel()
	{
		return $this->log_level;
	}
	
	public function setLogLevel($a_level)
	{
		$this->log_level = $a_level;
	}
	
	/**
	 * Update settings
	 */
	public function update()
	{
		$this->getStorage()->set('active', (int) $this->isActive());
		$this->getStorage()->set('ecs', $this->getECSServer());
		$this->getStorage()->set('width', $this->getWidth());
		$this->getStorage()->set('height', $this->getHeight());
		
		$ser_language = serialize($this->getLanguages());
		$this->getStorage()->set('languages',$ser_language);
		$this->getStorage()->set('log_level', $this->getLogLevel());
	}
	
	/**
	 * @return ilSetting 
	 */
	protected function getStorage()
	{
		return $this->storage;
	}

	

	/**
	 * Init (read) settings
	 */
	protected function init()
	{
		$this->setActive($this->getStorage()->get('active',$this->active));
		$this->setECSServer($this->getStorage()->get('ecs',$this->ecs));
		$this->setWidth($this->getStorage()->get('width',$this->width));
		$this->setHeight($this->getStorage()->get('height',$this->height));
		$this->setLanguages(unserialize($this->getStorage()->get('languages',serialize($this->languages))));
		$this->setLogLevel($this->getStorage()->get('log_level',$this->log_level));
	}
}
?>
