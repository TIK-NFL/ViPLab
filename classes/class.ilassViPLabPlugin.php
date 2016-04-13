<?php


include_once "./Modules/TestQuestionPool/classes/class.ilQuestionsPlugin.php";

/**
 * ViPLab plugin definition 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilassViPLabPlugin extends ilQuestionsPlugin
{
	const CTYPE = 'Modules';
	const CNAME = 'TestQuestionPool';
	const SLOT_ID = 'qst';
	const PNAME = 'assViPLab';

	private static $instance = null;
	
	/**
	 * Get singelton instance
	 * @global ilPluginAdmin $ilPluginAdmin
	 * @return ilViteroPlugin
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		
		include_once './Services/Component/classes/class.ilPluginAdmin.php';
		return self::$instance = ilPluginAdmin::getPluginObject(
			self::CTYPE,
			self::CNAME,
			self::SLOT_ID,
			self::PNAME
		);
	}
	
	/**
	 * Handle ecs events
	 * @param type $a_event_type
	 * @param type $a_event
	 */
	public function handleEcsEvent($a_event_type, $a_event)
	{
		ilLoggerFactory::getLogger('viplab')->dump($a_event,  ilLogLevel::INFO);
		
		try {
		
			$connector = new ilECSVipResultConnector(ilECSSetting::getInstanceByServerId(ilViPLabSettings::getInstance()->getECSServer()));
			$result = $connector->getResult($a_event['event']['id']);
			
			ilLoggerFactory::getLogger('viplab')->debug(print_r($result,true));
		}
		catch(Exception $ex) {
			ilLoggerFactory::getLogger('viplab')->warning($ex->getMessage());
		}
		
	}
	
	public function getPluginName()
	{
		return self::PNAME;
	}
		
	public function getQuestionType()
	{
		return "assViPLab";
	}
		
	public function getQuestionTypeTranslation()
	{
		return $this->txt('viplab_qst_type');
	}
	
	/**
	 * Init auto load
	 */
	protected function init()
	{
		$this->initAutoLoad();
		// set configured log level
		foreach(ilLoggerFactory::getLogger('viplab')->getLogger()->getHandlers() as $handler)
		{
			$handler->setLevel(ilViPLabSettings::getInstance()->getLogLevel());
		}
	}
		
	/**
	 * Init auto loader
	 * @return void
	 */
	protected function initAutoLoad()
	{
		spl_autoload_register(
			array($this,'autoLoad')
		);
	}

	/**
	 * Auto load implementation
	 *
	 * @param string class name
	 */
	private final function autoLoad($a_classname)
	{
		$class_file = $this->getClassesDirectory().'/class.'.$a_classname.'.php';
		if(@include_once($class_file))
		{
			return;
		}
			
		$class_file = $this->getClassesDirectory().'/ecs/class.'.$a_classname.'.php';
		if(@include_once($class_file))
		{
			return;
		}
	}
}
?>