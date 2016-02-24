<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSConnector.php';
include_once './Services/WebServices/ECS/classes/class.ilECSConnectorException.php';

/*
 * Handler for ecs subparticipant ressources
 * 
 */
class ilECSSubParticipantConnector extends ilECSConnector
{
	const RESOURCE_PATH = '/sys/subparticipants';
	const MEMBERSHIP_PATH = '/sys/memberships';
	
	/**
	 * Constructor
	 * @param ilECSSetting $settings 
	 */
	public function __construct(ilECSSetting $settings = null)
	{
		parent::__construct($settings);
	}
	
	
	/**
	 * Add subparticipant
	 * @param ilECSSubParticipant $sub
	 * @param type $a_mid
	 */
	public function addSubParticipant(ilECSSubParticipant $sub)
	{
		ilLoggerFactory::getLogger('viplab')->debug('Add new sub participant resource...');

	 	$this->path_postfix = self::RESOURCE_PATH;
	 	
	 	try 
	 	{
	 		$this->prepareConnection();

			$this->addHeader('Content-Type', 'application/json');
			$this->addHeader('Accept', 'application/json');

			$this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
			$this->curl->setOpt(CURLOPT_HEADER,TRUE);
	 		$this->curl->setOpt(CURLOPT_POST,TRUE);
	 		$this->curl->setOpt(CURLOPT_POSTFIELDS,  json_encode($sub));
			$ret = $this->call();

			$info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
	
			ilLoggerFactory::getLogger('viplab')->debug('Checking HTTP status...');
			if($info != self::HTTP_CODE_CREATED && $info != self::HTTP_CODE_OK)
			{
				ilLoggerFactory::getLogger('viplab')->error('Cannot create subparticipant, did not recieve HTTP 201.');
				ilLoggerFactory::getLogger('viplab')->error('POST was: ' . json_encode($sub));
				ilLoggerFactory::getLogger('viplab')->error('HTTP code was: ' . $info);
				throw new ilECSConnectorException('Received HTTP status code: '.$info);
			}
			
			ilLoggerFactory::getLogger('viplab')->debug('...got HTTP 201 (created)');

			$result = $this->parseResponse($ret);
			
			// store new ressource
			$ressource = new ilECSViPLabRessource();
			$ressource->setRessourceId($result->getMid());
			$ressource->setRessourceType(ilECSViPLabRessource::RES_SUBPARTICIPANT);
			$ressource->create();
			
			return $result;
	 	}
	 	catch(ilCurlConnectionException $exc)
	 	{
	 		throw new ilECSConnectorException('Error calling ECS service: '.$exc->getMessage());
	 	}
		
	}
	
	/**
	 * Delete sub participant
	 * @param type $a_sub_id
	 */
	public function deleteSubParticipant($a_sub_id)
	{
		ilLoggerFactory::getLogger('viplab')->debug('Delete subparticipant with id: '. $a_sub_id);
	 	$this->path_postfix = self::RESOURCE_PATH;
	 	
	 	if($a_sub_id)
	 	{
	 		$this->path_postfix .= ('/'.(int) $a_sub_id);
	 	}
	 	else
	 	{
	 		throw new ilECSConnectorException('Error calling deleteSubParticipant: No sub id given.');
	 	}
	
	 	try 
	 	{
	 		$this->prepareConnection();
	 		$this->curl->setOpt(CURLOPT_CUSTOMREQUEST,'DELETE');
			$res = $this->call();
			return new ilECSResult($res);
	 	}
	 	catch(ilCurlConnectionException $exc)
	 	{
	 		throw new ilECSConnectorException('Error calling ECS service: '.$exc->getMessage());
	 	}
	}
	
	/**
	 * Parse response string
	 * @param type $return_str
	 * @return ilECSSubParticipant
	 */
	protected function parseResponse($return_str)
	{
		$ecs_result = new ilECSResult($return_str,TRUE);

		// parse location
		$headers = $ecs_result->getHeaders();
		$location  = $headers['Location'];
		
		$id = end(split('/',$location));
		
		$sub = new ilECSSubParticipant($ecs_result->getResult());
		$sub->setId($id);
		return $sub;
	}

	
}
?>
