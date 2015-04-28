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
			$GLOBALS['ilLog']->write(__METHOD__.': Reading community failed with message '.$e->getMessage());
			return 0;
		}
	}
	
	
	public static function lookupSubParticipant($a_cookie)
	{
		
	}
}
?>
