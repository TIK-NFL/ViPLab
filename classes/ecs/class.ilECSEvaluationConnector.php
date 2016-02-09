<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSConnector.php';
include_once './Services/WebServices/ECS/classes/class.ilECSConnectorException.php';

/*
 * Handler for ecs subparticipant ressources
 * 
 */
class ilECSEvaluationConnector extends ilECSConnector
{
	const RESOURCE_PATH = '/numlab/evaluations';
	
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
	public function addEvaluation($exercise, $a_receiver_com)
	{
		global $ilLog;
		
		$ilLog->write(__METHOD__.': Add new evalaution resource for subparticipant: '.$a_receiver_com);

	 	$this->path_postfix = self::RESOURCE_PATH;
	 	
	 	try 
	 	{
	 		$this->prepareConnection();

			$this->addHeader('Content-Type', 'application/json');
			$this->addHeader('Accept', 'application/json');
			$this->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, $a_receiver_com);

			$this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
			$this->curl->setOpt(CURLOPT_HEADER,TRUE);
	 		$this->curl->setOpt(CURLOPT_POST,TRUE);
			
			if(strlen($exercise))
			{
				$this->curl->setOpt(CURLOPT_POSTFIELDS, $exercise);
			}
			else
			{
				$this->curl->setOpt(CURLOPT_POSTFIELDS, json_encode(NULL));
			}
			$ret = $this->call();
			$info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
	
			$ilLog->write(__METHOD__.': Checking HTTP status...');
			if($info != self::HTTP_CODE_CREATED)
			{
				$ilLog->write(__METHOD__.': Cannot create evaluation, did not receive HTTP 201. ');
				$ilLog->write(__METHOD__.': '.print_r($ret,TRUE));
				
				throw new ilECSConnectorException('Received HTTP status code: '.$info);
			}
			$ilLog->write(__METHOD__.': ... got HTTP 201 (created)');			

			$eid =  self::_fetchEContentIdFromHeader($this->curl->getResponseHeaderArray());
			
			// store new ressource
			$ressource = new ilECSViPLabRessource();
			$ressource->setRessourceId($eid);
			$ressource->setRessourceType(ilECSViPLabRessource::RES_EVALUATION);
			$ressource->create();
			
			
			return $eid;
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
	public function deleteEvaluation($a_exc_id)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Delete evaluation with id '. $a_exc_id);
	 	$this->path_postfix = self::RESOURCE_PATH;
	 	
	 	if($a_exc_id)
	 	{
	 		$this->path_postfix .= ('/'.(int) $a_exc_id);
	 	}
	 	else
	 	{
	 		throw new ilECSConnectorException('Error calling evaluation: No evaluation id given.');
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
