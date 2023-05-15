<?php

use Monolog\Handler\NullHandler;
use Monolog\Logger;

include_once "./Modules/TestQuestionPool/classes/class.ilQuestionsPlugin.php";

/**
 * ViPLab plugin definition
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilassViPLabPlugin extends ilQuestionsPlugin
{
	const CTYPE = 'Modules';
	const CNAME = 'TestQuestionPool';
	const SLOT_ID = 'qst';
	const PNAME = 'assViPLab';

    const QUESTION_TYPE = "assViPLab";

	private static $instance = null;
	
	/**
	 * Get singelton instance
	 *
	 * @global ilPluginAdmin $ilPluginAdmin
	 * @return ilassViPLabPlugin
	 */
	public static function getInstance()
	{
		if (self::$instance)
		{
			return self::$instance;
		}
		
        global $DIC;

        $component_factory = $DIC['component.factory'];
        $instance = $component_factory->getPlugin('assviplab');

        return self::$instance = $instance;
	}
	
	/**
	 * called from the ViPLabCron plugin https://github.com/TIK-NFL/ViPLabCron
	 */
	public function handleCronJob()
	{
		ilECSViPLabRessources::deleteDeprecated();
	}
	
	/**
	 * Handle ecs events.
	 * called from the ViPLabEvent plugin https://github.com/TIK-NFL/ViPLabEvent
	 *
	 * @param
	 *        	event event
	 * @param
	 *        	array array of event specific parameters
	 */
	public function handleEcsEvent($a_event_type, $a_event)
	{
		$event = $a_event['event'];
		
		ilLoggerFactory::getLogger('viplab')->debug('Handling new event: ' . $event['type']);
		
		
		if ($event['type'] == 'points')
		{
			try
			{
				$connector = new ilECSPointsConnector(ilViPLabSettings::getInstance()->getECSServer());
				$result = $connector->getPoints($event['id']);
				if ($result instanceof ilECSResult)
				{
					ilLoggerFactory::getLogger('viplab')->debug($result->getResult());
					$this->updateQuestionPoints($result);
					return true;
				}
			}
			catch (Exception $ex)
			{
				ilLoggerFactory::getLogger('viplab')->warning($ex->getMessage());
			}
		}
		return false;
	}
	
	/**
	 * Update scoring from ecs
	 *
	 * @param object $json
	 */
	protected function updateQuestionPoints(ilECSResult $result)
	{
		$points = $result->getResult();
		
		if (!is_object($points->Points))
		{
			ilLoggerFactory::getLogger('viplab')->warning('Expected json Points received: ');
			ilLoggerFactory::getLogger('viplab')->dump($points, ilLogLevel::WARNING);
			return false;
		}
		
		$identifier = (string) $points->Points->identifier;
		$received_points = (int) $points->Points->points;
		
		list ( $qid, $active_id, $pass ) = explode('_', $identifier);
		
		if (isset($qid) && isset($active_id) && isset($pass))
		{
			include_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
			assQuestion::_setReachedPoints($active_id, $qid, $received_points, assQuestion::_getMaximumPoints($qid), $pass, true, true);
			// todo lp status wrapper
		}
		else
		{
			ilLoggerFactory::getLogger('viplab')->warning('Cannot save scoring result');
			ilLoggerFactory::getLogger('viplab')->dump($points, ilLogLevel::WARNING);
		}
	}
	
	public function getPluginName(): string
	{
		return self::PNAME;
	}
	
	public function getQuestionType()
	{
		return ilassViPLabPlugin::QUESTION_TYPE;
	}
	
	public function getQuestionTypeTranslation(): string
	{
		return $this->txt('viplab_qst_type');
	}
	
	/**
	 * Init auto load
	 */
	protected function init(): void
	{
		$this->initAutoLoad();
	}
	
	/**
	 * Init auto loader
	 *
	 * @return void
	 */
	protected function initAutoLoad()
	{
		spl_autoload_register(array($this,'autoLoad'));
	}
	
	/**
	 * Auto load implementation
	 *
	 * @param
	 *        	string class name
	 */
	private final function autoLoad($a_classname)
	{
		$class_file = $this->getClassesDirectory() . '/class.' . $a_classname . '.php';
		if (@include_once ($class_file))
		{
			return;
		}
		
		$class_file = $this->getClassesDirectory() . '/ecs/class.' . $a_classname . '.php';
		if (@include_once ($class_file))
		{
			return;
		}
	}

    protected function getClassesDirectory() : string
    {
        return $this->getDirectory() . "/classes";
    }

    public function includeClass($a_class_file_name)
    {
        include_once($this->getClassesDirectory() . "/" . $a_class_file_name);
    }

}