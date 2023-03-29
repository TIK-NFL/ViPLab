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

			// Accept only HTTP/1.1 or HTTP/2 200 responses.
			$http1_resp = strstr($ret, 'HTTP/1.1 200 OK');
			$http2_resp = strstr($ret, 'HTTP/2 200');
			$ret = $http1_resp . $http2_resp;

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
		$this->curl->parseResponse($return_str);
		$ecs_result = new ilECSResult($this->curl->getResponseBody());

		$location = $this->getResponseHeaderFieldValue($this->curl->getResponseHeaderArray(), 'Location');
		$id = end(explode('/', $location));
		
		ilLoggerFactory::getLogger('viplab')->debug('Location:' . $location);
		ilLoggerFactory::getLogger('viplab')->debug('id:' . $id);
		ilLoggerFactory::getLogger('viplab')->debug('result-json' . print_r($ecs_result->getResult(), true));
		$sub = new ilECSSubParticipant($ecs_result->getResult());
		$sub->setId($id);
		return $sub;
	}

	public function getResponseHeaderFieldValue($header_array, $header_field)
	{
		foreach ($header_array as $key => $value) {
			if (strcasecmp($header_field, $key) == 0) {
				return trim($value);
			}
		}
	}

}
?>
