<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';

/**
 * Question GUI for viPLab questions
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ingroup ModulesTestQuestionPool
 * @ilctrl_iscalledby assViPLabGUI: ilObjQuestionPoolGUI, ilObjTestGUI, ilQuestionEditGUI, ilTestExpressPageObjectGUI
 * 
 */
class assViPLabGUI extends assQuestionGUI
{
	/**
	 * Constructor
	 * @param integer $a_id The database id of a question object
	 */
	public function __construct($a_id = -1)
	{
		parent::__construct($a_id);
		$this->object = new assViPLab();
		$this->newUnitId = null;
		
		if ($a_id >= 0)
		{
			$this->object->loadFromDb($a_id);
		}
	}
	
	/**
	 * Get question tabs
	 */
	public function setQuestionTabs()
	{
		global $ilAccess, $ilTabs;
		
		$this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$q_type = $this->object->getQuestionType();

		if (strlen($q_type))
		{
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
		}

		if ($_GET["q_id"])
		{
			if ($ilAccess->checkAccess('write', '',$_GET["ref_id"]))
			{
				// edit page
				$ilTabs->addTarget("edit_content",
					$this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "edit"),
					array("edit", "insert", "exec_pg"),
					"", "");
			}
	
			// preview page
            $ilTabs->addTarget("preview",
          	$this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "preview"),
                array("preview"),
                "ilAssQuestionPageGUI", "");
         }

		$force_active = false;
		if ($ilAccess->checkAccess('write', '', $_GET["ref_id"]))
		{
			$url = "";
			if ($classname) 
			{
				$url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			}
			$commands = $_POST["cmd"];
			if (is_array($commands))
			{
				foreach ($commands as $key => $value)
				{
					if (preg_match("/^suggestrange_.*/", $key, $matches))
					{
						$force_active = true;
					}
				}
			}
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "cancel", "addSuggestedSolution",
					"cancelExplorer", "linkChilds", "removeSuggestedSolution",
					"parseQuestion", "saveEdit", "suggestRange"),
				$classname, "", $force_active);
		}

        // add tab for question feedback within common class assQuestionGUI
        $this->addTab_QuestionFeedback($ilTabs);

        // add tab for question hint within common class assQuestionGUI
        $this->addTab_QuestionHints($ilTabs);

		// Assessment of questions sub menu entry
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass($classname, "assessment"),
				array("assessment"),
				$classname, "");
		}
		
		if (($_GET["calling_test"] > 0) || ($_GET["test_ref_id"] > 0))
		{
			$ref_id = $_GET["calling_test"];
			if (strlen($ref_id) == 0) $ref_id = $_GET["test_ref_id"];
			$ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
		}
		
	}
	
	/**
	 * @return ilassViPLabPlugin
	 */
	protected function getPlugin()
	{
		return ilassViPLabPlugin::getInstance();
	}
	
	/**
	 * Get viplab object
	 * @return assViPLab
	 */
	protected function getViPLabQuestion()
	{
		return $this->object;
	}
	
	protected function getSelfAssessmentEditingMode()
	{
		return FALSE;
	}
	
	protected function getDefaultNrOfTries()
	{
		return 1;
	}
	
	/**
	 * Init question form
	 * return ilPropertyFormGUI
	 */
	protected function initQuestionForm($a_show_editor = FALSE)
	{
		global $lng;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("viplabquestion");

		// title
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setValue($this->object->getTitle());
		$title->setRequired(TRUE);
		$form->addItem($title);

		if (!$this->getSelfAssessmentEditingMode())
		{
			// author
			$author = new ilTextInputGUI($this->lng->txt("author"), "author");
			$author->setValue($this->object->getAuthor());
			$author->setRequired(TRUE);
			$form->addItem($author);

			// description
			$description = new ilTextInputGUI($this->lng->txt("description"), "comment");
			$description->setValue($this->object->getComment());
			$description->setRequired(FALSE);
			$form->addItem($description);
		}
		else
		{
			// author as hidden field
			$hi = new ilHiddenInputGUI("author");
			$author = ilUtil::prepareFormOutput($this->object->getAuthor());
			if (trim($author) == "")
			{
				$author = "-";
			}
			$hi->setValue($author);
			$form->addItem($hi);
		}
		
		
		$lang = new ilSelectInputGUI($this->getPlugin()->txt('editor_lang'),'language');
		$lang->setValue($this->object->getVipLang());
		$options[''] = $this->lng->txt('select_one');
		foreach(ilViPLabSettings::getInstance()->getLanguages() as $lang_key => $mid)
		{
			$options[$lang_key] = $this->getPlugin()->txt('plang_'.$lang_key);
		}
		$lang->setOptions($options);
		$lang->setRequired(TRUE);
		$form->addItem($lang);

		// questiontext
		$this->object->getPlugin()->includeClass("class.ilViPLabTextAreaInputGUI.php");
		$question = new ilViPLabTextAreaInputGUI($this->lng->txt("question"), "question");
		$question->setValue($this->object->prepareTextareaOutput($this->object->getQuestion()));
		$question->setRequired(TRUE);
		$question->setRows(10);
		$question->setCols(80);
		if (!$this->getSelfAssessmentEditingMode())
		{
			$question->setUseRte(TRUE);
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$tags = ilObjAdvancedEditing::_getUsedHTMLTags("assessment");
			array_push($tags, 'input');
			array_push($tags, 'select');
			array_push($tags, 'option');
			array_push($tags, 'button');
			$question->setRteTags($tags);
			$question->addPlugin("latex");
			$question->addButton("latex");
			$question->addButton("pastelatex");
			$question->setRTESupport($this->object->getId(), "qpl", "assessment");
		}
		$form->addItem($question);

		if (!$this->getSelfAssessmentEditingMode())
		{
			// duration
			$duration = new ilDurationInputGUI($this->lng->txt("working_time"), "Estimated");
			$duration->setShowHours(TRUE);
			$duration->setShowMinutes(TRUE);
			$duration->setShowSeconds(TRUE);
			$ewt = $this->object->getEstimatedWorkingTime();
			$duration->setHours($ewt["h"]);
			$duration->setMinutes($ewt["m"]);
			$duration->setSeconds($ewt["s"]);
			$duration->setRequired(FALSE);
			$form->addItem($duration);
		}
		else
		{
			// number of tries
			if (strlen($this->object->getNrOfTries()))
			{
				$nr_tries = $this->object->getNrOfTries();
			}
			else
			{
				$nr_tries = $this->getDefaultNrOfTries();
			}
			if ($nr_tries <= 0)
			{
				$nr_tries = 1;
			}
			$ni = new ilNumberInputGUI($this->lng->txt("qst_nr_of_tries"), "nr_of_tries");
			$ni->setValue($nr_tries);
			$ni->setMinValue(1);
			$ni->setSize(5);
			$ni->setMaxLength(5);
			$ni->setRequired(true);
			$form->addItem($ni);
		}

		// points
		$points = new ilNumberInputGUI($lng->txt("points"), "points");
		$points->setValue($this->object->getPoints());
		$points->setRequired(TRUE);
		$points->setSize(3);
		$points->setMinValue(0.0);
		$form->addItem($points);
		
		// results
		$results = new ilCheckboxInputGUI($this->getPlugin()->txt('store_results'),'result_storing');
		$results->setInfo($this->getPlugin()->txt('store_results_info'));
		$results->setValue(1);
		$results->setChecked($this->getViPLabQuestion()->getVipResultStorage());
		$form->addItem($results);
		
		$scoring = new ilCheckboxInputGUI($this->getPlugin()->txt('auto_scoring'),'auto_scoring');
		$scoring->setInfo($this->getPlugin()->txt('auto_scoring_info'));
		$scoring->setValue(1);
		$scoring->setChecked($this->getViPLabQuestion()->getVipAutoScoring());
		$form->addItem($scoring);
		

		if ($this->object->getId())
		{
			$hidden = new ilHiddenInputGUI("", "ID");
			$hidden->setValue($this->object->getId());
			$form->addItem($hidden);
		}
		
		// add hidden exercise
		$hidden_exc = new ilHiddenInputGUI('vipexercise');
		$hidden_exc->setValue($this->getViPLabQuestion()->getVipExercise());
		$form->addItem($hidden_exc);
		
		// add evaluation
		$hidden_eval = new ilHiddenInputGUI('vipevaluation');
		$hidden_eval->setValue($this->getViPLabQuestion()->getVipEvaluation());
		$form->addItem($hidden_eval);

		#$this->addQuestionFormCommandButtons($form);
		$form->addCommandButton("save", $this->lng->txt("save"));

		$settings = ilViPLabSettings::getInstance();
		
		$GLOBALS['tpl']->addJavaScript($this->getPlugin()->getDirectory().'/js/editor_init.js');
		$applet = $this->getPlugin()->getTemplate('tpl.applet_editor.html',TRUE,TRUE);
		
		if($this->getViPLabQuestion()->getVipSubId() && $a_show_editor)
		{
			$eva_id = $this->createEvaluation();
			
			$applet->setVariable('VIP_ECS_URL', $settings->getECSServer()->getServerURI());
			$applet->setVariable('VIP_COOKIE',$this->getViPLabQuestion()->getVipCookie());
			$applet->setVariable('VIP_MID',$settings->getLanguageMid($this->getViPLabQuestion()->getVipLang()));
			$applet->setVariable('VIP_LANG',$this->getViPLabQuestion()->getVipLang(true));
			$applet->setVariable('VIP_EXERCISE',  ilECSExerciseConnector::RESOURCE_PATH.'/'.$this->getViPLabQuestion()->getVipExerciseId());
			$applet->setVariable('VIP_EVALUATION',$eva_id);
			$applet->setVariable('INITJS',$this->getPlugin()->getDirectory().'/templates');
		}
		else
		{
			$applet->setCurrentBlock('incomplete');
			$applet->setVariable('EDITOR_INIT',$this->getPlugin()->txt('editor_start'));
			$applet->parseCurrentBlock();
		}
		
		$applet_form = new ilCustomInputGUI($this->getPlugin()->txt('editor'),'editor');
		$applet_form->setHtml($applet->get());
		
		$form->addItem($applet_form);
		return $form;
	}

	/**
	 * Initialize applet editor
	 * @return type
	 */
	protected function initEditor()
	{
		ilECSViPLabRessources::deleteDeprecated();
		
		
		$form = $this->initQuestionForm();
		
		if(!$form->checkInput())
		{
			$form->setValuesByPost();
			ilUtil::sendFailure($GLOBALS['lng']->txt('err_check_input'),TRUE);
			$this->editQuestion($form);
			return TRUE;
		}

		// form valid
		$this->writePostFromForm($form);
		
		$this->getViPLabQuestion()->deleteSubParticipant();
		$this->addSubParticipant();
		
		$this->getViPLabQuestion()->deleteExercise();
		$this->createExercise();
		
		// initialize form again with editor
		$form = $this->initQuestionForm(TRUE);

		$this->getViPLabQuestion()->saveToDb();
		
		
		return $this->editQuestion($form);
	}
	
	/**
	 * Create a new solution
	 * @param int active_id
	 * @param int pass
	 * @param bool force solution generation even for empty solutions
	 * @return int
	 */
	protected function createSolution($a_active_id, $a_pass = null, $a_force_empty_solution = true)
	{
		// always create solution
		//$a_force_empty_solution = true;
		
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		if(!ilObjTest::_getUsePreviousAnswers($a_active_id, true))
		{
			if(is_null($a_pass))
			{
				$a_pass = ilObjTest::_getPass($a_active_id);
			}
		}
		$sol_arr = $this->getViPLabQuestion()->getUserSolutionPreferingIntermediate($a_active_id, $a_pass);
		
		ilLoggerFactory::getLogger('viplab')->debug(print_r($sol_arr,true));
		
		$sol = (string) $sol_arr[0]['value2'];

		if(!strlen($sol) and !$a_force_empty_solution)
		{
			return 0;
		}
		return $this->getViPLabQuestion()->createSolution($sol);
	}

	/**
	 * Create a new solution
	 * @return int
	 */
	protected function createEvaluation()
	{
		return $this->getViPLabQuestion()->createEvaluation();
	}
	
	/**
	 * Create a new solution
	 * @return int
	 */
	protected function createResult($a_active_id, $a_pass)
	{
		$this->getViPLabQuestion()->createResult($a_active_id, $a_pass);
	}

	/**
	 * Create exercise
	 */
	protected function createExercise()
	{
		$this->getViPLabQuestion()->createExercise();
	}
	

	protected function addSubParticipant()
	{
		return $this->getViPLabQuestion()->addSubParticipant();
	}

	/**
	 * Show edit question form
	 * @param ilPropertyFormGUI $form
	 */
	protected function editQuestion(ilPropertyFormGUI $form = null)
	{
		$this->getQuestionTemplate();

		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initQuestionForm();

		}
		$this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
	}
	
	/**
	 * Save question
	 */
	public function save()
	{
		$this->getViPLabQuestion()->deleteSubParticipant();
		$this->getViPLabQuestion()->deleteExercise();
		
		$form = $this->initQuestionForm();
		if($form->checkInput())
		{
			$this->writePostFromForm($form);
			parent::save();
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('err_check_input'));
			$form->setValuesByPost();
			$this->editQuestion($form);
		}		
	}
	
	/**
	 * Save and return
	 */
	public function saveReturn()
	{
		$this->getViPLabQuestion()->deleteSubParticipant();
		$this->getViPLabQuestion()->deleteExercise();

		$form = $this->initQuestionForm();
		if($form->checkInput())
		{
			$this->writePostFromForm($form);
			parent::saveReturn();
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('err_check_input'));
			$form->setValuesByPost();
			$this->editQuestion($form);
		}
	}
	
	/**
	 * Write post from form
	 */
	public function writePostFromForm(ilPropertyFormGUI $form)
	{
		$this->getViPLabQuestion()->setTitle($form->getInput('title'));
		$this->getViPLabQuestion()->setComment($form->getInput('comment'));
		$this->getViPLabQuestion()->setAuthor($form->getInput('author'));
		$this->getViPLabQuestion()->setQuestion($form->getInput('question'));
		$this->getViPLabQuestion()->setPoints($form->getInput('points'));
		$this->getViPLabQuestion()->setVipExercise($form->getInput('vipexercise'));
		
		$evaluation = ilViPLabUtil::extractJsonFromCustomZip($form->getInput('vipevaluation'));
		$this->getViPLabQuestion()->setVipEvaluation($evaluation);
		
		
		
		$this->getViPLabQuestion()->setVipResultStorage($form->getInput('result_storing'));
		$this->getViPLabQuestion()->setVipAutoScoring($form->getInput('auto_scoring'));
		
		ilLoggerFactory::getLogger('viplab')->debug(print_r($form->getInput('vipexercise'),true));
		
		$this->getViPLabQuestion()->setEstimatedWorkingTime(
			$_POST["Estimated"]["hh"],
			$_POST["Estimated"]["mm"],
			$_POST["Estimated"]["ss"]
		);
		
		$this->getViPLabQuestion()->setVipLang($form->getInput('language'));
		return TRUE;
	}
	
	/**
	 * Write post 
	 * @param type $always
	 * @return int
	 */
	public function writePostData($always = false)
	{
		return 0;
	}

	// preview 
	/**
	 * Preview of question
	 * @param type $a_show_question_only
	 * @param type $showInlineFeedback
	 * @return type
	 */
	public function getPreview($a_show_question_only = FALSE, $showInlineFeedback = FALSE)
	{
		include_once './Services/UICore/classes/class.ilTemplate.php';
		$template = $this->getPlugin()->getTemplate('tpl.il_as_viplab_preview.html');
		
		$template->setVariable(
			'QUESTIONTEXT', 
			$this->getViPLabQuestion()->prepareTextareaOutput(
				$this->getViPLabQuestion()->getQuestion(), 
				true)
		);
		
		$template->setCurrentBlock('complete');
		
		$settings = ilViPLabSettings::getInstance();
		$this->addSubParticipant();
		$this->createExercise();
		
		$template->setVariable('VIP_ID', $this->getViPLabQuestion()->getId());
		$template->setVariable('VIP_EXERCISE',  ilECSExerciseConnector::RESOURCE_PATH.'/'.$this->getViPLabQuestion()->getVipExerciseId());
		$template->setVariable('VIP_ECS_URL', $settings->getECSServer()->getServerURI());
		$template->setVariable('VIP_COOKIE',$this->getViPLabQuestion()->getVipCookie());
		$template->setVariable('VIP_MID',$settings->getLanguageMid($this->getViPLabQuestion()->getVipLang()));
		$template->setVariable('INITJS',$this->getPlugin()->getDirectory().'/templates');
		
		$template->parseCurrentBlock();
		
		
		$preview = $template->get();
		if(!$a_show_question_only)
		{
			$preview = $this->getILIASPage($preview);
		}
		
		
		$GLOBALS['tpl']->addJavaScript($this->getPlugin()->getDirectory().'/js/question_init.js');
		
		return $preview;
	}
	
	/**
	 * New implementation get testoutput
	 * @param type $active_id
	 * @param type $pass
	 * @param type $is_question_postponed
	 * @param type $user_post_solutions
	 * @param type $show_specific_inline_feedback
	 * @return type
	 */
	public function getTestOutput($active_id, $pass, $is_question_postponed, $user_post_solutions, $show_specific_inline_feedback)
	{

		$settings = ilViPLabSettings::getInstance();
		$this->addSubParticipant();
		$this->createExercise();

		ilLoggerFactory::getLogger('viplab')->debug('VipCookie: '. $this->getViPLabQuestion()->getVipCookie());
		
		$atpl = $this->getPlugin()->getTemplate('tpl.applet_question.html');

		// What happens if has no solution, answers questions => and clicks "Calculate"?
		$sol_id = $this->createSolution($active_id, $pass, false);

		
		$atpl->setVariable('QUESTIONTEXT', $this->getViPLabQuestion()->prepareTextareaOutput($this->getViPLabQuestion()->getQuestion(), TRUE));
		$atpl->setVariable('VIP_ECS_URL', $settings->getECSServer()->getServerURI());
		$atpl->setVariable('VIP_COOKIE',$this->getViPLabQuestion()->getVipCookie());
		$atpl->setVariable('VIP_MID',$settings->getLanguageMid($this->getViPLabQuestion()->getVipLang()));
		$atpl->setVariable('VIP_EXERCISE',  ilECSExerciseConnector::RESOURCE_PATH.'/'.$this->getViPLabQuestion()->getVipExerciseId());
		
		if($sol_id)
		{
			$atpl->setVariable('VIP_SOLUTION', ilECSSolutionConnector::RESOURCE_PATH.'/'.$sol_id);
		}
		

		$atpl->setVariable('INITJS',$this->getPlugin()->getDirectory().'/templates');
		
		
		$atpl->setVariable('VIP_STORED_EXERCISE', $this->getViPLabQuestion()->getVipExerciseId());
		$atpl->setVariable('VIP_STORED_PARTICIPANT',$this->getViPLabQuestion()->getVipSubId());
		
		// add solution 
		if($sol_id)
		{
			$atpl->setVariable('VIP_STORED_SOLUTION',$sol_id);
		}
		
		$pageoutput = $this->outQuestionPage("", $is_question_postponed, $active_id, $atpl->get());
		
		$GLOBALS['tpl']->addJavaScript($this->getPlugin()->getDirectory().'/js/question_init.js');
		return $pageoutput;
	}
	

	/**
	 * Show solution output
	 * @param integer $active_id             The active user id
	 * @param integer $pass                  The test pass
	 * @param boolean $graphicalOutput       Show visual feedback for right/wrong answers
	 * @param boolean $result_output         Show the reached points for parts of the question
	 * @param boolean $show_question_only    Show the question without the ILIAS content around
	 * @param boolean $show_feedback         Show the question feedback
	 * @param boolean $show_correct_solution Show the correct solution instead of the user solution
	 * @param boolean $show_manual_scoring   Show specific information for the manual scoring output
	 * @param boolean $show_question_text
	 * @return string The solution output of the question as HTML code
	 */
	public function getSolutionOutput($active_id, $pass = NULL, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = TRUE, $show_feedback = FALSE, $show_correct_solution = FALSE, $show_manual_scoring = FALSE, $show_question_text = TRUE)
	{
		if((int) $_REQUEST['viplab_editor_initialized_'.$this->getViPLabQuestion()->getId()] == '1')
		{
			$initialized = TRUE;
		}
		if(!$show_manual_scoring)
		{
			return '';
		}
		
		$soltpl = $this->getPlugin()->getTemplate('tpl.viplab_solution_output.html');
		$soltpl->setVariable('SOLUTION_TXT', $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));
		
		// show editor button
		if(!$initialized)
		{			
			$soltpl->setCurrentBlock('incomplete');
			$soltpl->setVariable('EDITOR_INIT',$this->getPlugin()->txt('editor_start'));
			$soltpl->setVariable('VIPEDITOR_ID',$this->getViPLabQuestion()->getId());
			$soltpl->parseCurrentBlock();
		}
		// show viplab applet
		else
		{
			$this->getViPLabQuestion()->deleteSubParticipant();
			$this->addSubParticipant();
		
			$this->getViPLabQuestion()->deleteExercise();
			$this->createExercise();
			
			$sol_id = $this->createSolution($active_id, $pass, true);
			$eva_id = $this->createEvaluation();
			
			$settings = ilViPLabSettings::getInstance();
			
			$soltpl->setCurrentBlock('complete');
			$soltpl->setVariable('VIP_APP_ID',$this->getViPLabQuestion()->getId());
			$soltpl->setVariable('VIP_ECS_URL', $settings->getECSServer()->getServerURI());
			$soltpl->setVariable('VIP_ECS_URL', $settings->getECSServer()->getServerURI());
			$soltpl->setVariable('VIP_COOKIE',$this->getViPLabQuestion()->getVipCookie());
			$soltpl->setVariable('VIP_MID',$settings->getLanguageMid($this->getViPLabQuestion()->getVipLang()));
			$soltpl->setVariable('VIP_EXERCISE',  ilECSExerciseConnector::RESOURCE_PATH.'/'.$this->getViPLabQuestion()->getVipExerciseId());
			$soltpl->setVariable('VIP_SOLUTION',  ilECSSolutionConnector::RESOURCE_PATH.'/'.$sol_id);
			$soltpl->setVariable('VIP_EVALUATION', ilECSEvaluationConnector::RESOURCE_PATH.'/'.$eva_id);
			$soltpl->setVariable('INITJS',$this->getPlugin()->getDirectory().'/templates'); 
			
			if($this->getViPLabQuestion()->getVipResultStorage())
			{
				$res_id = $this->createResult($active_id, $pass);
				$soltpl->setVariable('VIP_RESULT', ilECSVipResultConnector::RESOURCE_PATH.'/'.$res_id);
			}
			else
			{
				$soltpl->setVariable('VIP_RESULT', '');
			}
			
			$soltpl->setVariable('VIP_LANG',$this->getViPLabQuestion()->getVipLang(true));

			$soltpl->setVariable('VIP_STORED_EXERCISE', $this->getViPLabQuestion()->getVipExerciseId());
			$soltpl->setVariable('VIP_STORED_PARTICIPANT',$this->getViPLabQuestion()->getVipSubId());
		}
		
		$qst_txt = $soltpl->get();
		
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $qst_txt);
		$solutionoutput = $solutiontemplate->get(); 
		if (!$show_question_only)
		{
			// get page object output
			$solutionoutput = $this->getILIASPage($solutionoutput);
		}

		return $solutionoutput;
	}

	public function getSpecificFeedbackOutput($active_id, $pass)
	{
	}


}
?>
