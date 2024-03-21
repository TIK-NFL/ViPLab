<?php

include_once './Services/Component/classes/class.ilPluginConfigGUI.php';

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ViPLab config gui
 * General settings for vip lab plugin
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ilctrl_iscalledby ilassViPLabConfigGUI: ilObjComponentSettingsGUI
 */
class ilassViPLabConfigGUI extends ilPluginConfigGUI
{

	public ilGlobalPageTemplate $tpl;

	public function __construct()
	{
		global $DIC;
		$this->tpl = $DIC['tpl'];
	}

	/**
	 * Handles all commmands, default is "configure"
	 */
	public function performCommand($cmd): void
	{
		global $ilTabs;
		
		$ilTabs->addTab('tab_settings', ilassViPLabPlugin::getInstance()->txt('tab_settings'), $GLOBALS['ilCtrl']->getLinkTarget($this, 'configure'));
		
		switch ($cmd)
		{
			case 'configure':
			case 'save':
				$this->$cmd();
				break;
		
		}
	}

	/**
	 * Configure plugin
	 */
	protected function configure(ilPropertyFormGUI $form = null)
	{
		$GLOBALS['ilTabs']->activateTab('tab_settings');
		
		if (!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initConfigurationForm();
		}
		$GLOBALS['tpl']->setContent($form->getHTML());
	}

	/**
	 * Init configuration form
	 *
	 * @return ilPropertyFormGUI
	 */
	protected function initConfigurationForm()
	{
		$this->getPluginObject()->includeClass('class.ilViPLabSettings.php');
		$settings = ilViPLabSettings::getInstance();
		
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($GLOBALS['ilCtrl']->getFormAction($this));
		$form->setTitle($this->getPluginObject()->txt('form_tab_settings'));
		
		// ecs servers
		include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
		$servers = ilECSServerSettings::getInstance()->getServers(ilECSServerSettings::ACTIVE_SERVER);
		$options = array();
		$options[0] = $GLOBALS['lng']->txt('select_one');
		foreach ($servers as $server)
		{
			$options[$server->getServerId()] = $server->getTitle();
		}
		
		$ecs_select = new ilSelectInputGUI($this->getPluginObject()->txt('form_tab_settings_ecs_server'), 'ecs');
		$ecs_select->setValue($settings->getECSServerId());
		$ecs_select->setInfo($this->getPluginObject()->txt('form_tab_settings_ecs_server_info'));
		$ecs_select->setRequired(TRUE);
		$ecs_select->setOptions($options);
		$form->addItem($ecs_select);
		
		// evaluation backend
		$evaluation_backend = new ilSelectInputGUI($this->getPluginObject()->txt('form_tab_settings_eval_backend'), 'evaluation_mid');
		$evaluation_options = array();
		if ($settings->getECSServerId())
		{
			$evaluation_options = $this->readAvailabeMids($settings);
		}
		else
		{
			$evaluation_backend->setDisabled(true);
		}
		$evaluation_backend->setOptions($evaluation_options);
		$evaluation_backend->setValue($settings->getEvaluationMid());
		$form->addItem($evaluation_backend);
		
		// evaluation backend
		$own_mid = new ilSelectInputGUI($this->getPluginObject()->txt('form_tab_settings_mid'), 'evaluation_own_mid');
		$evaluation_options = array();
		if ($settings->getECSServerId())
		{
			$evaluation_options = $this->readAvailabeMids($settings, true);
		}
		else
		{
			$own_mid->setDisabled(true);
		}
		$own_mid->setOptions($evaluation_options);
		$own_mid->setValue($settings->getEvaluationReceiverMid());
		$form->addItem($own_mid);
		
		// log level
		$GLOBALS['lng']->loadLanguageModule('log');
		$level = new ilSelectInputGUI($this->getPluginObject()->txt('form_tab_settings_loglevel'), 'log_level');
		$level->setOptions(ilLogLevel::getLevelOptions());
		$level->setValue($settings->getLogLevel());
		$form->addItem($level);
		
		// languages
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->getPluginObject()->txt('form_tab_settings_section_languages'));
		$form->addItem($section);
		$this->getPluginObject()->includeClass('class.ilViPLabUtil.php');
		
		$configured = $settings->getLanguages();
		foreach (ilViPLabUtil::getAvailableLanguages() as $lng_key)
		{
			$lang_type = new ilSelectInputGUI($this->getPluginObject()->txt('plang_' . $lng_key), 'plang_' . $lng_key);
			
			$options = array();
			if ($settings->getECSServerId())
			{
				$options = $this->readAvailabeMids($settings);
			}
			else
			{
				$lang_type->setDisabled(TRUE);
			}
			$lang_type->setOptions($options);
			if (array_key_exists($lng_key, $configured))
			{
				$lang_type->setValue($configured[$lng_key]);
			}
			$form->addItem($lang_type);
		}
		
		$form->addCommandButton('save', $GLOBALS['lng']->txt('save'));
		return $form;
	}

	/**
	 * Read available mids
	 *
	 * @param ilViPLabSettings $settings
	 * @return array[string]
	 */
	protected function readAvailabeMids(ilViPLabSettings $settings, $a_only_self = false)
	{
        $options = array();
        $options[0] = $GLOBALS['lng']->txt('select_one');

		try {
			include_once './Services/WebServices/ECS/classes/class.ilECSCommunityReader.php';
			$reader = ilECSCommunityReader::getInstanceByServerId($settings->getECSServerId());
			$participants = $reader->getParticipants();

            foreach ($participants as $mid => $part) {
                if (($part->isSelf() && !$a_only_self) || (!$part->isSelf() && $a_only_self)) {
                    continue;
                }
                $options[$mid] = $part->getParticipantName() . ': ' . $this->getPluginObject()->txt('ecs_mid') . ' ' . $mid;
            }
		} catch (ilECSConnectorException $e) {
			$this->tpl->setOnScreenMessage('failure', 'Read from ecs server failed with message: ' . $e);
		}

		return $options;
	}

	/**
	 * Save settings
	 */
	protected function save()
	{
		$form = $this->initConfigurationForm();
		if ($form->checkInput() or 1)
		{
			$this->getPluginObject()->includeClass('class.ilViPLabSettings.php');
			$settings = ilViPLabSettings::getInstance();
			
			$settings->setLogLevel($form->getInput('log_level'));
			$settings->setECSServerId((int) $form->getInput('ecs'));
			$settings->setEvaluationMid($form->getInput('evaluation_mid'));
			$settings->setEvaluationReceiverMid($form->getInput('evaluation_own_mid'));
			
			$this->getPluginObject()->includeClass('class.ilViPLabUtil.php');
			
			$enabled_langs = array();
			foreach (ilViPLabUtil::getAvailableLanguages() as $lng_key)
			{
				$mid = $form->getInput('plang_' . $lng_key);
				if ($mid)
				{
					$enabled_langs[$lng_key] = $mid;
				}
			}
			$settings->setLanguages($enabled_langs);
			$settings->update();

			$this->tpl->setOnScreenMessage('success', $GLOBALS['lng']->txt('settings_saved'), true);
			$GLOBALS['ilCtrl']->redirect($this, 'configure');
			return TRUE;
		}
		
		$this->tpl->setOnScreenMessage('failure', $GLOBALS['lng']->txt('err_check_input'), true);
		$GLOBALS['ilCtrl']->redirect($this, 'configure');
		return TRUE;
	}
}