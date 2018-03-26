<?php
require_once ('./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php');
/**
 * This class represents a ViPLabEditor in a property form.
 *
 * @author Leon Kiefer <leon.kiefer@tik.uni-stuttgart.de>
 */
class ilViPLabEditorFormGUI extends ilFormPropertyGUI
{
	/**
	 *
	 * @var bool
	 */
	protected $show_editor = false;
	
	/**
	 *
	 * @var assViPLab
	 */
	protected $viPLabQuestion;

	/**
	 * Constructor
	 *
	 * @param string $a_title
	 *        	Title
	 * @param string $a_postvar
	 *        	Post Variable
	 * @param assViPLab $a_ViPLabQuestion
	 */
	function __construct($a_title, $a_postvar, $a_ViPLabQuestion)
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("custom");
		$this->viPLabQuestion = $a_ViPLabQuestion;
	}

	public function showEditor($a_show_editor)
	{
		$this->show_editor = $a_show_editor;
	}

	/**
	 * Set value by array
	 *
	 * @param array $a_values
	 *        	value array
	 */
	function setValueByArray($a_values)
	{
	}

	/**
	 * Get Html.
	 *
	 * @return string Html
	 */
	function getHtml()
	{
		$settings = ilViPLabSettings::getInstance();
		
		$applet = $this->viPLabQuestion->getPlugin()->getTemplate('tpl.applet_editor.html', TRUE, TRUE);
		
		if ($this->show_editor)
		{
			$eva_id = $this->viPLabQuestion->createEvaluation();
			
			$applet->setVariable('VIP_ECS_URL', $settings->getECSServer()->getServerURI());
			$applet->setVariable('VIP_COOKIE', $this->viPLabQuestion->getVipCookie());
			$applet->setVariable('VIP_MID', $settings->getLanguageMid($this->viPLabQuestion->getVipLang()));
			$applet->setVariable('VIP_LANG', $this->viPLabQuestion->getVipLang(true));
			$applet->setVariable('VIP_EXERCISE', ilECSExerciseConnector::RESOURCE_PATH . '/' . $this->viPLabQuestion->getVipExerciseId());
			$applet->setVariable('VIP_EVALUATION', $eva_id);
			$applet->setVariable('INITJS', $this->viPLabQuestion->getPlugin()->getDirectory() . '/templates');
		}
		else
		{
			$applet->setCurrentBlock('incomplete');
			$applet->setVariable('EDITOR_INIT', $this->viPLabQuestion->getPlugin()->txt('editor_start'));
			$applet->parseCurrentBlock();
		}
		return $applet->get();
	}

	/**
	 * Insert property html
	 */
	function insert($a_tpl)
	{
		$a_tpl->setCurrentBlock("prop_custom");
		$a_tpl->setVariable("CUSTOM_CONTENT", $this->getHtml());
		$a_tpl->parseCurrentBlock();
		$a_tpl->addJavaScript($this->viPLabQuestion->getPlugin()->getDirectory() . '/js/editor_init.js');
	}

	/**
	 * Check input, strip slashes etc.
	 * set alert, if input is not ok.
	 *
	 * TODO: this is a copy of the checkInput function from ilCustomInputGUI
	 *
	 * @return boolean Input ok, true/false
	 */
	function checkInput()
	{
		global $DIC;
		
		if ($this->getPostVar())
		{
			$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
			if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
			{
				$this->setAlert($DIC->language()->txt("msg_input_is_required"));
				return false;
			}
		}
		return true;
	}
}