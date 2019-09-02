<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSConnector.php';
include_once './Services/WebServices/ECS/classes/class.ilECSConnectorException.php';

/*
 * Handler for ecs subparticipant ressources
 * 
 */
class ilECSEvaluationJobConnector extends ilECSConnector
{
	const RESOURCE_PATH = '/numlab/evaluation_jobs';
	
	private $vip_settings = null;
	
	/**
	 * Constructor
	 * @param ilECSSetting $settings 
	 */
	public function __construct(ilECSSetting $settings = null)
	{
		parent::__construct($settings);
		
		$this->vip_settings = ilViPLabSettings::getInstance();
		
	}
	
	/**
	 * @return ilViPLabSettings
	 */
	public function getVipSettings()
	{
		return $this->vip_settings;
	}
	
	
	/**
	 * Add subparticipant
	 * @param ilECSSubParticipant $sub
	 * @param type $a_mid
	 */
	public function addEvaluationJob(ilECSEvaluationJob $a_evaluation_job, $a_targets)
	{
		ilLoggerFactory::getLogger('viplab')->debug('Add new evaluation job ressource for ' . print_r($a_targets,true));

	 	$this->path_postfix = self::RESOURCE_PATH;
	 	
	 	try 
	 	{
	 		$this->prepareConnection();

			$this->addHeader('Content-Type', 'application/json');
			$this->addHeader('Accept', 'application/json');
			$this->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, $a_targets);

			$this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
			$this->curl->setOpt(CURLOPT_HEADER,TRUE);
	 		$this->curl->setOpt(CURLOPT_POST,TRUE);
			
			$this->curl->setOpt(CURLOPT_POSTFIELDS, $a_evaluation_job->getJson());
			
			
			$ret = $this->call();
			$info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
	
			ilLoggerFactory::getLogger('viplab')->debug('Checking HTTP status...');
			if($info != self::HTTP_CODE_CREATED)
			{
				ilLoggerFactory::getLogger('viplab')->error('Cannot create evaluation job ressource, did not receive HTTP 201');
				ilLoggerFactory::getLogger('viplab')->error('Return value: '. print_r($ret, true));
				throw new ilECSConnectorException('Received HTTP status code: '.$info);
			}
			ilLoggerFactory::getLogger('viplab')->debug('... got HTTP 201 (created)');

			$eid =  self::_fetchEContentIdFromHeader($this->curl->getResponseHeaderArray());
			return $eid;
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
	 * @deprecated
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
