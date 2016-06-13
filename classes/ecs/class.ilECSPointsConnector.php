<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSConnector.php';
include_once './Services/WebServices/ECS/classes/class.ilECSConnectorException.php';

/*
 * Handler for ecs points ressources
 * 
 */
class ilECSPointsConnector extends ilECSConnector
{
	const RESOURCE_PATH = '/numlab/points';
	
	/**
	 * Constructor
	 * @param ilECSSetting $settings 
	 */
	public function __construct(ilECSSetting $settings = null)
	{
		parent::__construct($settings);
	}
	
	
	/**
	 * Read result
	 * @param type $a_id
	 * @return \ilECSResult
	 * @throws ilECSConnectorException
	 */
	public function getPoints($a_id)
	{
		$this->path_postfix = self::RESOURCE_PATH.'/'.$a_id;

		try {
			$this->prepareConnection();
			$this->addHeader('Content-Type', 'application/json');
			$this->addHeader('Accept', 'application/json');

			$res = $this->call();

			// Checking status code
			$info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
			if($info != self::HTTP_CODE_OK)
			{
				ilLoggerFactory::getLogger('viplab')->warning('Cannot read viplab points, did not receive HTTP 200');
				throw new ilECSConnectorException('Received HTTP status code: '.$info);
			}
			$result = new ilECSResult($res);
			return $result;
	 	}
	 	catch(ilCurlConnectionException $exc)
	 	{
	 		throw new ilECSConnectorException('Error calling ECS service: '.$exc->getMessage());
	 	}
	}
	
	/**
	 * Add Header
	 * @param string $a_name
	 * @param string $a_value
	 */
	public function addHeader($a_name,$a_value)
	{
		if(is_array($a_value))
		{
			$header_value = implode(',', $a_value);
		}
		else
		{
			$header_value = $a_value;
		}
		parent::addHeader($a_name, $header_value);
	}
	
}
?>