<?php

include_once './Services/Table/classes/class.ilTable2GUI.php';


/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ViPLab config gui
 * General settings for vip lab plugin
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilEcsRessourcesTableGUI extends ilTable2GUI
{
	private $plugin;
	
	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
	{
		$this->plugin = ilassViPLabPlugin::getInstance();
		$this->setId('ilviplab_ecs_ressources');
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
	}
	
	/**
	 * 
	 * @return ilassViPLabPlugin
	 */
	public function getPlugin()
	{
		return $this->plugin;
	}
	
	/**
	 * Init table
	 */
	public function init()
	{
	 	$this->addColumn('','f',1);
	 	$this->addColumn($this->getPlugin()->txt('ecs_ressource_type_col'),'type',"60%");
	 	$this->addColumn($this->getPlugin()->txt('ecs_ressource_cdate'),'cdate',"40%");
	 	
		$this->setFormAction($GLOBALS['ilCtrl']->getFormAction($this->getParentObject()));
		
		
		$this->setRowTemplate($this->getPlugin()->getTemplate(
				'tpl.viplab_ecs_ressources_row.html'),
				substr($this->getPlugin()->getDirectory(),2)
		);
		$this->setDefaultOrderField("cdate_sort");
		$this->setDefaultOrderDirection("desc");
	}
	
	public function fillRow($set)
	{
		$this->tpl->setVariable('VAL_POSTNAME', 'ressources');
		$this->tpl->setVariable('VAL_ID', $set['id']);
		$this->tpl->setVariable('RESSOURCE_TYPE', $set['type']);
		#$this->tpl->setVariable('CDATE', ilDatePresentation::formatDate(new ilDateTime($set['cdate_sort'], IL_CAL_UNIX)));
		$this->tpl->setVariable('CDATE', 'Jan 1970');
	}
	
	/**
	 * Parse table content
	 */
	public function parse()
	{
		$rows[] = array();
		foreach(ilECSViPLabRessources::getRessources() as $ressource)
		{
			$row = array();
			$row['id'] = $ressource->getId();
			$row['type'] = $ressource->getRessourceType();
			$row['cdate_sort'] = $ressource->getCreationDate();
			
			$rows[] = $row;
		}
		$this->setData($rows);
	}
}
?>