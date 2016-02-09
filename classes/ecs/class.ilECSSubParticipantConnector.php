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
		global $ilLog;
		
		$ilLog->write(__METHOD__.': Add new sub participant resource...');

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
	
			$ilLog->write(__METHOD__.': Checking HTTP status...');
			if($info != self::HTTP_CODE_CREATED && $info != self::HTTP_CODE_OK)
			{
				$ilLog->write(__METHOD__.': Cannot create sub participant, did not receive HTTP 201. ');
				$ilLog->write(__METHOD__.': POST was: '. json_encode($sub));
				$ilLog->write(__METHOD__.': HTTP code: '.$info);
				throw new ilECSConnectorException('Received HTTP status code: '.$info);
			}
			$ilLog->write(__METHOD__.': ... got HTTP 201 (created)');

			#$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($ret,TRUE));
			
			$result = $this->parseResponse($ret);
			
			// store new ressource
			$ressource = new ilECSViPLabRessource();
			$ressource->setRessourceId($result->getMid());
			$ressource->setRessourceType(ilECSViPLabRessource::RES_SUBPARTICIPANT);
			$ressource->create();
			
			
			#$ilLog->write(__METHOD__.': ... got cookie: '.$result->getCookie());
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
		$GLOBALS['ilLog']->write(__METHOD__.': Delete subparticipant with id '. $a_sub_id  );
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
		
		#$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($headers,TRUE));
		#$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($ecs_result->getPlainResultString(),TRUE));
		#$GLOBALS['ilLog']->write(__METHOD__.': '.print_r(end(split('/',$location)),TRUE));
		
		$id = end(split('/',$location));
		
		$sub = new ilECSSubParticipant($ecs_result->getResult());
		$sub->setId($id);
		return $sub;
	}

	
}
?>
