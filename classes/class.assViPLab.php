<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/
include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for Mathematik Online based questions
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assViPLab extends assQuestion
{
	const ADDITIONAL_TBL_NAME = 'il_qpl_qst_viplab';
	
	/**
	 * 
	 * @var integer
	 */
	private $vip_sub_id = 0;
	private $vip_cookie = '';
	
	/**
	 * 
	 * @var string
	 */
	private $vip_lang = '';
	private $vip_exercise = '';
	private $vip_evaluation = '';
	private $vip_exercise_id = 0;
	private $vip_auto_scoring = true;

	private $vip_result_storage = '';

	private $plugin;

	/**
	* vip lab question 
	*
	* The constructor takes possible arguments an creates an instance of the assMathematikOnline object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the single choice question
	* @access public
	* @see assQuestion:assQuestion()
	*/
	public function __construct(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	)
	{
		parent::__construct($title, $comment, $author, $owner, $question);
		$this->plugin = ilassViPLabPlugin::getInstance();
	}
	
	/**
	 * @return ilassViPLabPlugin The plugin object
	 */
	public function getPlugin()
	{
		return $this->plugin;
	}
	
	public function setVipSubId($a_id)
	{
		$this->vip_sub_id = $a_id;
	}
	
	public function getVipSubId()
	{
		return $this->vip_sub_id;
	}
	
	public function setVipCookie($a_cookie)
	{
		$this->vip_cookie = $a_cookie;
	}
	
	public function getVipCookie()
	{
		return $this->vip_cookie;
	}
	
	/**
	 * 
	 * @param string $a_lang
	 */
	public function setVipLang($a_lang)
	{
		$this->vip_lang = $a_lang;
	}
	
	/**
	 * Get vip lang
	 * @param bool $a_shortened
	 * @return string
	 */
	public function getVipLang($a_shortened = false)
	{
		if($a_shortened && stristr($this->vip_lang, '_P'))
		{
			return substr($this->vip_lang, 0, -2);
		}
		
		return $this->vip_lang;
	}
	
	public function setVipExercise($a_exc)
	{
		$this->vip_exercise = $a_exc;
	}
	
	public function getVipExercise()
	{
		return $this->vip_exercise;
	}
	public function setVipExerciseId($a_exc)
	{
		$this->vip_exercise_id = $a_exc;
	}
	
	public function getVipExerciseId()
	{
		return $this->vip_exercise_id;
	}
	
	public function setVipEvaluation($a_eval)
	{
		$this->vip_evaluation = $a_eval;
	}
	
	public function getVipEvaluation()
	{
		return $this->vip_evaluation;
	}
	
	public function getVipResultStorage()
	{
		return $this->vip_result_storage;
	}
	
	public function setVipResultStorage($a_res)
	{
		$this->vip_result_storage = $a_res;
	}
	
	public function setVipAutoScoring($a_auto_scoring)
	{
		$this->vip_auto_scoring = $a_auto_scoring;
	}		
	
	public function getVipAutoScoring()
	{
		return $this->vip_auto_scoring;
	}

	/**
	 * Returns true, if a single choice question is complete for use
	 *
	 * @return boolean True, if the single choice question is complete for use, otherwise false
	 * @access public
	 */
	function isComplete(): bool
	{
		if (($this->title) and ($this->author) and ($this->question) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Saves a assMathematikOnline object to a database
	*
	* @access public
	*/
	function saveToDb($original_id = -1): void
	{
		global $ilDB;

		$this->saveQuestionDataToDb($original_id);

		// save additional data
		$ilDB->manipulateF("DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s", 
			array("integer"),
			array($this->getId())
		);
		
		$ilDB->insert(
				$this->getAdditionalTableName(),
				array(
					'question_fi'	=> array('integer',(int) $this->getId()),
					'vip_sub_id'	=> array('integer',(int) $this->getVipSubId()),
					'vip_cookie'	=> array('text',(string) $this->getVipCookie()),
					'vip_exercise'	=> array('clob',(string) $this->getVipExercise()),
					'vip_lang'		=> array('text', (string) $this->getVipLang()),
					'vip_exercise_id'	=> array('integer',(string) $this->getVipExerciseId()),
					'vip_evaluation' => array('clob',(string) $this->getVipEvaluation()),
					'vip_result_storage' => array('integer',(string) $this->getVipResultStorage()),
					'vip_auto_scoring' => array('integer', (int) $this->getVipAutoScoring())
				)
		);
		parent::saveToDb($original_id);
	}

	/**
	* Loads a viplab object from a database
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id): void
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT qpl_questions.* FROM qpl_questions WHERE question_id = %s",
			array('integer'),
			array($question_id)
		);
		if ($result->numRows() == 1)
		{
			$data = $ilDB->fetchAssoc($result);
			$this->setId($question_id);
			$this->setTitle($data["title"] ?? "");
			$this->setComment($data["description"] ?? "");
			$this->setSuggestedSolution($data["solution_hint"] ?? "");
			$this->setOriginalId($data["original_id"]);
			$this->setObjId($data["obj_fi"] ?? 0);
			$this->setAuthor($data["author"] ?? "");
			$this->setOwner($data["owner"] ?? -1);
			$this->setPoints($data["points"] ?? 0);

			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"] ?? "", 1));
			
			$this->setEstimatedWorkingTimeFromDurationString($data["working_time"] ?? "");

			// load additional data
			$result = $ilDB->queryF("SELECT * FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
				array('integer'),
				array($question_id)
			);
			
			if ($result->numRows() == 1)
			{
				$data = $ilDB->fetchAssoc($result);
				
				$this->setVipSubId((int) $data['vip_sub_id']);
				$this->setVipCookie((string) $data['vip_cookie']);
				$this->setVipExercise((string) $data['vip_exercise']);
				$this->setVipLang((string) $data['vip_lang']);
				$this->setVipExerciseId((int) $data['vip_exercise_id']);
				$this->setVipEvaluation((string) $data['vip_evaluation']);
				$this->setVipResultStorage((int) $data['vip_result_storage']);
				$this->setVipAutoScoring((int) $data['vip_auto_scoring']);
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	 * Duplicates a viplab question
	 *
	 * Duplicates an assMathematikOnline
	 *
	 * @access public
	 */
	public function duplicate($for_test = true, $title = "", $author = "", $owner = "", $a_test_obj_id = null): int
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return -1;
		}
		// duplicate the question in database
		$this_id = $this->getId();
        $clone = clone $this;
        include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        $original_id = assQuestion::_getOriginalId($this->id);
		$clone->id = -1;
		
		if ((int) $a_test_obj_id > 0)
        {
        	$clone->setObjId($a_test_obj_id);
		}
				
		if ($title)
		{
			$clone->setTitle($title);
		}

		if ($author)
		{
			$clone->setAuthor($author);
		}
		if ($owner)
		{
			$clone->setOwner($owner);
		}

		if ($for_test)
		{
			$clone->saveToDb($original_id);
		}
		else
		{
			$clone->saveToDb();
		}

        // copy question page content
        $clone->copyPageOfQuestion($this_id);

        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($this_id);

        $clone->onDuplicate($a_test_obj_id, $this_id, $clone->getObjId(), $clone->getId());

		return $clone->id;
	}
	
	
	public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}

		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");

		$sourceQuestionId = $this->id;
		$sourceParentId = $this->getObjId();

		// duplicate the question in database
		$clone = $this;
		$clone->id = -1;

		$clone->setObjId($targetParentId);

		if ($targetQuestionTitle)
		{
			$clone->setTitle($targetQuestionTitle);
		}

		$clone->saveToDb();
		// copy question page content
		$clone->copyPageOfQuestion($sourceQuestionId);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($sourceQuestionId);

		$clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

		return $clone->id;
	}
	

	/**
	* Copies an assMathematikOnline object
	*
	* Copies an assMathematikOnline object
	*
	* @access public
	*/
	function copyObject($target_questionpool, $title = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$clone = $this;
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$original_id = assQuestion::_getOriginalId($this->id);
		$clone->id = -1;
		$source_questionpool = $this->getObjId();
		$clone->setObjId($target_questionpool);
		if ($title)
		{
			$clone->setTitle($title);
		}
		$clone->saveToDb();

		// copy question page content
		$clone->copyPageOfQuestion($original_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);
		// duplicate the generic feedback
		// TODO figure out new way for feedback copy in question pools
		//$clone->duplicateGenericFeedback($original_id);

		return $clone->id;
	}

	/**
	* Returns the maximum points, a learner can reach answering the question
	*
	* @access public
	* @see $points
	*/
	function getMaximumPoints(): float
	{
		return $this->points;
	}

	/**
	 * Returns the points, a learner has reached answering the question
	 * The points are calculated from the given answers including checks
	 * for all special scoring options in the test container.
	 *
	 * @param integer $user_id The database ID of the learner
	 * @param integer $test_id The database Id of the test containing the question
	 * @param boolean $returndetails (deprecated !!)
	 * @access public
	 */
	function calculateReachedPoints($active_id, $pass = NULL, $authorizedSolution = true, $returndetails = FALSE)
	{
		global $ilDB;
		
		$found_values = array();
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		$result = $ilDB->queryF("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			array('integer','integer','integer'),
			array($active_id, $this->getId(), $pass)
		);

		$points = 0;
		while ($data = $ilDB->fetchAssoc($result))
		{
			$points += $data["points"];
		}

		return $points;
	}

	protected function calculateReachedPointsForSolution($solution)
	{
		return 0;  // since instant feedback is not supported by ViPLab
	}
	

	/**
	 * Saves the learners input of the question to the database
	 *
	 * @param integer $test_id The database id of the test containing this question
	 * @return boolean Indicates the save status (true if saved successful, false otherwise)
	 * @access public
	 * @see $answers
	 */
	public function saveWorkingData($active_id, $pass = NULL, $authorized = true): bool
	{
		global $ilDB;
		
		ilLoggerFactory::getLogger('viplab')->debug('++++ save working data');

		if(is_null($pass))
		{
			include_once './Modules/Test/classes/class.ilObjTest.php';
			$pass = ilObjTest::_getPass($active_id);
		}
		
		// do not delete exercise and sub paritipant due to "auto saving" feature
		#$this->deleteExercise((int) $_POST['vipexercise']);
		#$this->deleteSubParticipant((int) $_POST['vipparticipant']);
		
		$ilDB->manipulateF(
				'DELETE FROM tst_solutions '.
				'WHERE active_fi = %s '.
				'AND question_fi = %s '.
				'AND pass = %s '.
				'AND value1 != %s',
			array('integer', 'integer', 'integer', 'text'),
			array($active_id, $this->getId(), $pass, "0")
		);
		
		$solution = ilViPLabUtil::extractJsonFromCustomZip(ilUtil::stripSlashes($_POST['vipsolution']));

		$next_id = $ilDB->nextId('tst_solutions');
		$ilDB->insert(
			"tst_solutions", 
			array(
				'solution_id' => array("integer", $next_id),
				"active_fi" => array("integer", $active_id),
				"question_fi" => array("integer", $this->getId()),
				"value1" => array("clob", 'vipsolution'),
				"value2" => array("clob", $solution),
				"pass" => array("integer", $pass),
				"tstamp" => array("integer", time())
			)
		);
		
		// create evaluation job 
		$this->createEvaluationJob(
			$solution,
			$active_id,
			$pass
		);
		
		
		if($this->getVipResultStorage() or 1)
		{
			$result = ilUtil::stripSlashes($_POST['vipresult']);

			$next_id = $ilDB->nextId('tst_solutions');
			$ilDB->insert(
				"tst_solutions", 
				array(
					'solution_id' => array("integer", $next_id),
					"active_fi" => array("integer", $active_id),
					"question_fi" => array("integer", $this->getId()),
					"value1" => array("clob", 'vipresult'),
					"value2" => array("clob", $result),
					"pass" => array("integer", $pass),
					"tstamp" => array("integer", time())
				)
			);
		}

		if(strlen($solution))
		{
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}
		else
		{
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_not_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}
		return TRUE;
	}
	

	/**
	 * 
	 * @param type $active_id
	 * @param type $pass
	 * @param type $obligationsAnswered
	 * @param type $authorized
	 */
	public function reworkWorkingData($active_id, $pass, $obligationsAnswered, $authorized)
	{
		;
	}
	
	/**
	 * Returns the question type of the question
	 *
	 * Returns the question type of the question
	 *
	 * @return integer The question type of the question
	 * @access public
	 */
	function getQuestionType(): string
	{
		return $this->getPlugin()->getQuestionType();
	}
	
	/**
	 * Returns the name of the additional question data table in the database
	 *
	 * Returns the name of the additional question data table in the database
	 *
	 * @return string The additional table name
	 * @access public
	 */
	function getAdditionalTableName()
	{
		return self::ADDITIONAL_TBL_NAME;
	}
	
	/**
	 * Returns the name of the answer table in the database
	 *
	 * Returns the name of the answer table in the database
	 *
	 * @return string The answer table name
	 * @access public
	 */
	function getAnswerTableName()
	{
		return "";
	}
	
	/**
	 * Deletes datasets from answers tables
	 *
	 * @param integer $question_id The question id which should be deleted in the answers table
	 * @access public
	 */
	public function deleteAnswers($question_id): void
	{
	}

	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	function getRTETextWithMediaObjects(): string
	{
		$text = parent::getRTETextWithMediaObjects();
		return $text;
	}

	/**
	 * required method stub
	 * 
	 * @param object $worksheet    Reference to the parent excel worksheet
	 * @param object $startrow     Startrow of the output in the excel worksheet
	 * @param object $active_id    Active id of the participant
	 * @param object $pass         Test pass
	 */
	public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass): int
	{
		return parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);
	}
	
	/**
	* Creates a question from a QTI file
	*
	* Receives parameters from a QTI parser and creates a valid ILIAS question object
	*
	* @param object $item The QTI item object
	* @param integer $questionpool_id The id of the parent questionpool
	* @param integer $tst_id The id of the parent test if the question is part of a test
	* @param object $tst_object A reference to the parent test object
	* @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
	* @param array $import_mapping An array containing references to included ILIAS objects
	* @access public
	*/
	public function fromXML($item, int $questionpool_id, ?int $tst_id, &$tst_object, int &$question_counter, array $import_mapping, array &$solutionhints = []): array
	{
		$this->getPlugin()->includeClass("./import/qti12/class." . $this->getQuestionType() . "Import.php");
		$classname = $this->getQuestionType() . "Import";
		$import = new $classname($this);
		$import_mapping = $import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);

		foreach ($solutionhints as $hint) {
			$h = new ilAssQuestionHint();
			$h->setQuestionId($import->getQuestionId());
			$h->setIndex($hint['index']);
			$h->setPoints($hint['points']);
			$h->setText($hint['txt']);
			$h->save();
		}
		return $import_mapping;
	}
	
	/**
	* Returns a QTI xml representation of the question and sets the internal
	* domxml variable with the DOM XML representation of the QTI xml representation
	*
	* @return string The QTI xml representation of the question
	* @access public
	*/
	function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false): string
	{
		$this->getPlugin()->includeClass("./export/qti12/class." . $this->getQuestionType() . "Export.php");
		$classname = $this->getQuestionType() . "Export";
		$export = new $classname($this);
		return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
	}

	/**
	* Returns the best solution for a given pass of a participant
	*
	* @return array An associated array containing the best solution
	* @access public
	*/
	public function getBestSolution($active_id, $pass)
	{
		$user_solution = array();
		return $user_solution;
	}
	

	public function getSolutionSubmit() {
		return array();  // instant feedback preview is not supported by ViPLab
	}

	/**
	 * Delete exercise
	 * @param type $a_sent_id
	 */
	public function deleteExercise($a_sent_id = 0)
	{
		$exc_id = $a_sent_id ? $a_sent_id : $this->getVipExerciseId();
		
		if($exc_id)
		{
			ilLoggerFactory::getLogger('viplab')->debug('Deleting exercise');
			try
			{
				$connector = new ilECSExerciseConnector(
					ilViPLabSettings::getInstance()->getECSServer()
				);
				$connector->deleteExercise($exc_id);
				
				// reset exercise
				if(!$a_sent_id)
				{
					$this->setVipExerciseId(0);
				}
			}
			catch(ilECSConnectorException $e)
			{
				ilLoggerFactory::getLogger('viplab')->error('Deleting exercise failed with message: '. $e->getMessage());
			}
		}
	}
	
	/**
	 * Delete subparticipant
	 */
	public function deleteSubParticipant($a_sub_id = 0)
	{
		$sub_id = $a_sub_id ? $a_sub_id : $this->getVipSubId();
		
		if($sub_id)
		{
			ilLoggerFactory::getLogger('viplab')->debug('Deleting subparticipant');
			try
			{
				$connector = new ilECSSubParticipantConnector(
					ilViPLabSettings::getInstance()->getECSServer()
				);
				$connector->deleteSubParticipant($sub_id);
				
				// reset sub id
				if($a_sub_id)
				{
					$this->setVipSubId(0);
				}
			}
			catch(ilECSConnectorException $e)
			{
				ilLoggerFactory::getLogger('viplab')->error('Deleting subparticipant failed with message: ' . $e->getMessage());
			}
		}
	}
	
	/**
	 * Create a new solution
	 * @return int
	 */
	public function createEvaluation($a_decode = true, $a_computational_backend = true)
	{
		if($a_decode and strlen($this->getVipEvaluation()))
		{
			$eva = ilViPLabUtil::extractJsonFromCustomZip($this->getVipEvaluation());
		}
		else
		{
			$eva = $this->getVipEvaluation();
		}
		try 
		{
			$scon = new ilECSEvaluationConnector(
				ilViPLabSettings::getInstance()->getECSServer()
			);
			
			if($a_computational_backend)
			{
				// mantis: #15974
				$targets = $this->getVipSubId();
				/**
				$targets = array(
					ilViPLabSettings::getInstance()->getLanguageMid($this->getVipLang()),
					$this->getVipSubId()
				);
				 */
			}
			else
			{
				$targets = ilViPLabSettings::getInstance()->getEvaluationMid();
			}
			
			$new_id = $scon->addEvaluation($eva,$targets);
			ilLoggerFactory::getLogger('viplab')->debug('Received new evaluation id ' . $new_id);
			return $new_id;
		}
		catch (ilECSConnectorException $exception)
		{
			ilLoggerFactory::getLogger('viplab')->error('Creating evaluation failed with message: '. $exception);
		}
	}
	
	/**
	 * Create a new solution
	 * @return int
	 */
	public function createResult($a_active_id, $a_pass)
	{
		$result_arr = $this->getSolutionValues($a_active_id, $a_pass);
		if(isset($result_arr[1]))
		{
			$result_string = $result_arr[1]['value2'];
		}
		try 
		{
			$scon = new ilECSVipResultConnector(
				ilViPLabSettings::getInstance()->getECSServer()
			);
			
			$new_id = $scon->addResult($result_string,
					array(
						ilViPLabSettings::getInstance()->getLanguageMid($this->getVipLang()),
						$this->getVipSubId()
					)
			);
			ilLoggerFactory::getLogger('viplab')->debug('Received new result id ' . $new_id);
			return $new_id;
		}
		catch (ilECSConnectorException $exception)
		{
			ilLoggerFactory::getLogger('viplab')->error('Creating result failed with message: '. $exception);
		}
	}
	
	/**
	 * Create exercise
	 */
	public function createExercise($a_computational_backend = true)
	{
		if(strlen($this->getVipExercise()))
		{
			$exc = $this->getVipExercise();
		}
		else
		{
			$exc = '';
		}
		try
		{
			$econ = new ilECSExerciseConnector(
						ilViPLabSettings::getInstance()->getECSServer()
			);
			
			if($a_computational_backend)
			{
				$targets = array(
					ilViPLabSettings::getInstance()->getLanguageMid($this->getVipLang()),
					$this->getVipSubId()
				);
			}
			else
			{
				$targets = array(
					ilViPLabSettings::getInstance()->getEvaluationMid()
				);
			}
			ilLoggerFactory::getLogger('viplab')->debug($exc);
			$new_id = $econ->addExercise($exc,$targets);
			$this->setVipExerciseId($new_id);
			return $new_id;
		}
		catch (ilECSConnectorException $exception)
		{
			ilLoggerFactory::getLogger('viplab')->error('Creating exercise failed with message: '. $exception);
		}
	}
	
	public function createSolution($a_solution, $a_computational_backend = true)
	{
		try 
		{
			ilLoggerFactory::getLogger('viplab')->info($a_solution);
			
			$scon = new ilECSSolutionConnector(
				ilViPLabSettings::getInstance()->getECSServer()
			);
			
			if($a_computational_backend)
			{
				$targets = $this->getVipSubId();
				/**
				$targets = array(
					ilViPLabSettings::getInstance()->getLanguageMid($this->getVipLang()),
					$this->getVipSubId()
				);
				 * 
				 */
			}
			else
			{
				$targets = ilViPLabSettings::getInstance()->getEvaluationMid();
			}
			
			$new_id = $scon->addSolution($a_solution,$targets);
			ilLoggerFactory::getLogger('viplab')->debug('Received new solution id ' . $new_id);
			return $new_id;
		}
		catch (ilECSConnectorException $exception)
		{
			ilLoggerFactory::getLogger('viplab')->error('Creating solution failed with message: '. $exception);
		}
		
	}
	
	protected function createEvaluationJob($a_solution_json, $a_active_id, $a_pass)
	{
		if(!$this->getVipAutoScoring())
		{
			return false;
		}
		
		ilLoggerFactory::getLogger('viplab')->debug('---------------- New evaluation job ------------------');
		
		
		//$this->addSubParticipant();
		
		$job = new ilECSEvaluationJob();
		$job->setName('Evaluation job for active_id: ' . $a_active_id);
		$dt = new ilDateTime(time(), IL_CAL_UNIX);
		$job->setPostTime($dt);
		
		$job->setIdentifier(
			$this->getId().'_'.(int) $a_active_id.'_'.(int) $a_pass
		);

		$exc_id = $this->createExercise(false);
		$job->setExercise($exc_id);
		
		$evaluation_id = $this->createEvaluation(true,false);
		$job->setEvaluation($evaluation_id);
		
		$solution_id = $this->createSolution($a_solution_json,false);
		$job->setSolution($solution_id);
		$job->setMid(ilViPLabSettings::getInstance()->getLanguageMid($this->getVipLang()));
		
		ilLoggerFactory::getLogger('viplab')->debug('--------------------- '. $job->getJson().' ---------------------------');
		
		try
		{
			$scon = new ilECSEvaluationJobConnector(
				ilViPLabSettings::getInstance()->getECSServer()
			);
			$new_id = $scon->addEvaluationJob(
				$job,
				ilViPLabSettings::getInstance()->getEvaluationMid()
			);
			ilLoggerFactory::getLogger('viplab')->debug('Received new evaluation job id ' . $new_id);
			return $new_id;
			
		} 
		catch (ilECSConnectorException $exception)
		{
			ilLoggerFactory::getLogger('viplab')->error('Creating evaluation job failed with message: '. $exception);
		}
	}
	
	public function addSubParticipant()
	{
		if(TRUE)
		#if(!$this->getViPLabQuestion()->getVipSubId())
		{
			$sub = new ilECSSubParticipant();
			$com = ilViPLabUtil::lookupCommunityByMid(
				ilViPLabSettings::getInstance()->getECSServer(),
				ilViPLabSettings::getInstance()->getLanguageMid($this->getVipLang())
			);
			if($com instanceof ilECSCommunity)
			{
				ilLoggerFactory::getLogger('viplab')->debug('Current community = ' . $com->getId());
				$sub->addCommunity($com->getId());
			}
			else
			{
				$this->tpl->setOnScreenMessage('failure', 'Cannot assign subparticipant.');
				return false;
			}
			
			try 
			{
				$connector = new ilECSSubParticipantConnector(
					ilViPLabSettings::getInstance()->getECSServer()
				);
				$res = $connector->addSubParticipant($sub);
			}
			catch(ilECSConnectorException $e)
			{
				ilLoggerFactory::getLogger('viplab')->error('Failed with message: '. $e->getMessage());
				exit;
			}
			
			
			// save cookie and sub_id
			$this->setVipSubId($res->getMid());
			$this->setVipCookie($res->getCookie());
			ilLoggerFactory::getLogger('viplab')->debug('Recieved new cookie '. $res->getCookie());
			ilLoggerFactory::getLogger('viplab')->debug('Recieved new  mid '. $res->getMid());
		}		
	}
	
	/**
	 * Lookup if an authorized or intermediate solution exists
	 * @param 	int 		$activeId
	 * @param 	int 		$pass
	 * @return 	array		['authorized' => bool, 'intermediate' => bool]
	 */
	public function lookupForExistingSolutions($activeId, $pass): array
	{
		global $ilDB;
		
		$state = parent::lookupForExistingSolutions($activeId, $pass);
		$state['intermediate'] = true;
		return $state;
	}
}
