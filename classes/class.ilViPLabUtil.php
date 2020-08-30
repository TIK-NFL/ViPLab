<?php
/**
 * ViPLab plugin definition 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilViPLabUtil
{
	protected static $languages = array ('C','C_P','C++','C++_P','DuMux','DuMux_P','Java','Java_P','Matlab','Matlab_P','Octave','Octave_P');

	/**
	 * Get available programming languages
	 *
	 * @return array
	 */
	public static function getAvailableLanguages()
	{
		return self::$languages;
	}

	/**
	 * Get ecs community id by mid
	 *
	 * @param ilECSSetting $server
	 * @param int $a_mid
	 * @return int
	 */
	public static function lookupCommunityByMid(ilECSSetting $server, int $a_mid)
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

	public static function extractJsonFromCustomZip($a_zip_string)
	{
		ilLoggerFactory::getLogger('viplab')->debug('Trying to decode ' . $a_zip_string);
		
		// check if custom zip format
		if (substr($a_zip_string, 0, 4) != 'ZIP:')
		{
			ilLoggerFactory::getLogger('viplab')->debug('No custom zip format given.');
			return $a_zip_string;
		}
		
		$zip_cleaned = substr($a_zip_string, 4);
		ilLoggerFactory::getLogger('viplab')->dump($zip_cleaned, ilLogLevel::DEBUG);
		
		// base64 decode
		$decoded = base64_decode($zip_cleaned);
		
		// save to temp file
		$tmp_name = ilUtil::ilTempnam();
		file_put_contents($tmp_name, $decoded);
		
		$zip = new ZipArchive();
		if ($zip->open($tmp_name) === true)
		{
			ilLoggerFactory::getLogger('viplab')->debug('Successfully decoded zip');
			$json = $zip->getFromName('json');
			ilLoggerFactory::getLogger('viplab')->dump($json, ilLogLevel::DEBUG);
			
			unlink($tmp_name);
			return $json;
		}
		else
		{
			ilLoggerFactory::getLogger('viplab')->warning('Failed opening zip archive');
		}
		return; 
	}
}
?>
