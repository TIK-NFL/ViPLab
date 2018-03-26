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
	 * @return ilassViPLabPlugin
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
	
	public function handleCronJob()
	{
		return ilECSViPLabRessources::deleteDeprecated();
	}
	
	/**
	 * Handle ecs events
	 * @param type $a_event_type
	 * @param type $a_event
	 */
	public function handleEcsEvent($a_event_type, $a_event)
	{
		ilLoggerFactory::getLogger('viplab')->debug('Handling new event: ' . $a_event['event']['type']);
		
		if($a_event['event']['type'] == 'results')
		{
			ilLoggerFactory::getLogger('viplab')->info('Ignoring event of type results');
			return false;
			/*
			try {
				$connector = new ilECSVipResultConnector(ilViPLabSettings::getInstance()->getECSServer());
				$result = $connector->getResult($a_event['event']['id']);

				if($result instanceof ilECSResult)
				{
					ilLoggerFactory::getLogger('viplab')->debug($result->getPlainResultString());
				}
				return true;
			}
			catch(Exception $e) {
				ilLoggerFactory::getLogger('viplab')->warning($e->getMessage());
			}
			 */
			
		}
		
		try {
		
			$connector = new ilECSPointsConnector(ilViPLabSettings::getInstance()->getECSServer());
			$result = $connector->getPoints($a_event['event']['id']);
			if($result instanceof ilECSResult)
			{
				ilLoggerFactory::getLogger('viplab')->debug($result->getPlainResultString());
				$this->updateQuestionPoints($result);
				return true;
			}
		}
		catch(Exception $ex) {
			ilLoggerFactory::getLogger('viplab')->warning($ex->getMessage());
		}
		
	}
	
	/**
	 * Update scoring from ecs
	 * @param object $json
	 */
	protected function updateQuestionPoints(ilECSResult $result)
	{
		$points = $result->getResult();
		
		if(!is_object($points->Points))
		{
			ilLoggerFactory::getLogger('viplab')->warning('Expected json Points received: ');
			ilLoggerFactory::getLogger('viplab')->dump($points, ilLogLevel::WARNING);
			return false;
		}
		
		$identifier = (string) $points->Points->identifier;
		$received_points = (int) $points->Points->points;

		list($qid, $active_id, $pass) = explode('_', $identifier);
		
		if(isset($qid) && isset($active_id) && isset($pass))
		{
			include_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
			assQuestion::_setReachedPoints(
				$active_id,
				$qid,
				$received_points,
				assQuestion::_getMaximumPoints($qid),
				$pass,
				true,
				true
			);
			// todo lp status wrapper
		}
		else
		{
			ilLoggerFactory::getLogger('viplab')->warning('Cannot save scoring result');
			ilLoggerFactory::getLogger('viplab')->dump($points, ilLogLevel::WARNING);
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