<?php
/**
 * ViPLab plugin definition 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilViPLabUtil
{
	protected static $languages = array('C','C++','DuMux','Java','Matlab','Octave');
	
	
	/**
	 * Get available programming languages
	 * @return array
	 */
	public static function getAvailableLanguages()
	{
		return self::$languages;
	}
	
	
	/**
	 * Get ecs community id by mid
	 * @param ilECSSetting $server
	 * @param type $a_mid
	 * @return int
	 */
	public static function lookupCommunityByMid(ilECSSetting $server, $a_mid)
	{
		try
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSCommunityReader.php';
			$com_reader = ilECSCommunityReader::getInstanceByServerId($server->getServerId());
			return $com_reader->getCommunityByMID($a_mid);
		}
		catch (ilECSConnectorException $e)
		{
			ilLoggerFactory::getLogger('viplab')->error('Reading community failed with message: ' . $e->getMessage());
			return 0;
		}
	}
	
	
	public static function lookupSubParticipant($a_cookie)
	{
		
	}
	
	public static function decodeSolution($a_solution)
	{
		ilLoggerFactory::getLogger('viplab')->debug('Trying to decode '. $a_solution);
		
		// check if custom zip format
		if(substr($a_solution, 0, 4) != 'ZIP:')
		{
			ilLoggerFactory::getLogger('viplab')->debug('No custom zip format given.');
		}
		
		$solution = substr($a_solution,4);
		// base64 decode
		$solution = base64_decode($solution);
		
		$start = strpos($solution, '{"Solution"');
		$end = strpos($solution,'}}}PK') + 3;
		
		$solution_json = substr($solution, $start, $end - $start);
		
		ilLoggerFactory::getLogger('viplab')->dump($solution_json, ilLogLevel::DEBUG);

		
		// decode json 
		
		return $solution_json;
		
	}
	
	public static function decodeEvaluation($a_evaluation)
	{
		ilLoggerFactory::getLogger('viplab')->debug('Trying to decode '. $a_evaluation);
		
		// check if custom zip format
		if(substr($a_evaluation, 0, 4) != 'ZIP:')
		{
			ilLoggerFactory::getLogger('viplab')->debug('No custom zip format given.');
		}
		
		$evaluation = substr($a_evaluation,4);
		// base64 decode
		$evaluation = base64_decode($evaluation);
		
		$start = strpos($evaluation, '{"Evaluation"');
		$end = strpos($evaluation,'}}PK') + 2;
		$evaluation_json = substr($evaluation, $start, $end - $start);
		ilLoggerFactory::getLogger('viplab')->dump($evaluation_json, ilLogLevel::DEBUG);

		// decode json 
		return $evaluation_json;
		
	}
}
?>
