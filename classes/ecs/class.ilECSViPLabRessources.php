<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wrapper for single ecs ressource objects
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSViPLabRessources
{
	// Remove old resources after 48h
	const MAX_AGE_SECONDS = 48*3600;
        // Number of old resources to delete in each cron run
	const REMOVE_IN_EACH_CRONEXECUTION = 5000;

	/**
	 * Get ressources
	 * @return ilECSViPLabRessource[]
	 */
	public static function getRessources($a_age = null)
	{
		global $ilDB;
		
		$query = 'SELECT id from il_qpl_qst_viplab_res ' .
				'WHERE create_dt < ' . $ilDB->quote(time() - self::MAX_AGE_SECONDS, 'integer');
		$res = $ilDB->query($query);
		
		$ressources = array();
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$ressources[] = new ilECSViPLabRessource($row->id);
		}
		return $ressources;
	}

	public static function deleteDeprecated()
	{
		global $ilDB;
		
		$query = 'SELECT id from il_qpl_qst_viplab_res ' .
				'WHERE create_dt < ' . $ilDB->quote(time() - self::MAX_AGE_SECONDS, 'integer') .
                                ' limit ' .$ilDB->quote(self::REMOVE_IN_EACH_CRONEXECUTION, 'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$ressource = new ilECSViPLabRessource($row->id);
			try {
				self::doDeleteRessource($ressource);
				$ressource->delete();
			} 
			catch (Exception $ex) {
				;
			}
		}
	}
	
	/**
	 * Delete ressource
	 * @param ilECSViPLabRessource $ressource
	 * @throws Exception
	 */
	protected static function doDeleteRessource(ilECSViPLabRessource $ressource)
	{
		switch($ressource->getRessourceType())
		{
			case ilECSViPLabRessource::RES_SUBPARTICIPANT:
				try 
				{
					$connector = new ilECSSubParticipantConnector(
						ilViPLabSettings::getInstance()->getECSServer()
					);
					$connector->deleteSubParticipant($ressource->getRessourceId());
				} 
				catch (Exception $ex) 
				{
					ilLoggerFactory::getLogger('assviplab')->warning('Deleting subparticipant failed with message: ' . $ex->getMessage());
					throw $ex;
				}
				break;

			case ilECSViPLabRessource::RES_EXERCISE:
				try 
				{
					$connector = new ilECSExerciseConnector(
						ilViPLabSettings::getInstance()->getECSServer()
					);
					$connector->deleteExercise($ressource->getRessourceId());
				} 
				catch (Exception $ex) 
				{
					ilLoggerFactory::getLogger('assviplab')->warning('Deleting exercise failed with message: ' . $ex->getMessage());
					throw $ex;
				}
				break;

			case ilECSViPLabRessource::RES_EVALUATION:
				try 
				{
					$connector = new ilECSEvaluationConnector(
						ilViPLabSettings::getInstance()->getECSServer()
					);
					$connector->deleteEvaluation($ressource->getRessourceId());
				} 
				catch (Exception $ex) 
				{
					ilLoggerFactory::getLogger('assviplab')->warning('Deleting evaluation failed with message: ' . $ex->getMessage());
					throw $ex;
				}
				break;

			case ilECSViPLabRessource::RES_SOLUTION:
				try 
				{
					$connector = new ilECSSolutionConnector(
						ilViPLabSettings::getInstance()->getECSServer()
					);
					$connector->deleteSolution($ressource->getRessourceId());
				} 
				catch (Exception $ex) 
				{
					ilLoggerFactory::getLogger('assviplab')->warning('Deleting solution failed with message: ' . $ex->getMessage());
					throw $ex;
				}
				break;
		}
	}
}
?>
