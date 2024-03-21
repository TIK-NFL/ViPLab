<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php";

/**
* Class for ViPLab question imports
*/
class assViPLabImport extends assQuestionImport
{
    /**
     * @var assViPLab
     */
    public $object;

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
    public function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, $import_mapping): array
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        // empty session variable for imported xhtml mobs
        unset($_SESSION["import_mob_xhtml"]);

        $presentation = $item->getPresentation();
        $duration = $item->getDuration();
        $now = getdate();
        $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);

        // -------------------------------------------------------------------------------------------------------------
        // QTI ViPLab presentation

        foreach ($presentation->order as $entry) {
            if ($entry["type"] == "material") {
                $material = $presentation->material[$entry["index"]];
                for ($i = 0; $i < $material->getMaterialCount(); $i++) {
                    $mat = $material->getMaterial($i);
                    if ($mat["type"] == "mattext") {
                        $mattext = $mat["material"];
                        if ($mattext->getLabel() == "points") {
                            $this->object->setPoints($mattext->getContent());
                        } else if ($mattext->getLabel() == "vipLang") {
                            $this->object->setVipLang($mattext->getContent());
                        } else if ($mattext->getLabel() == "vipAutoScoring") {
                            $this->object->setVipAutoScoring($mattext->getContent());
                        } else if ($mattext->getLabel() == "vipResultStorage") {
                            $this->object->setVipResultStorage($mattext->getContent());
                        } else if ($mattext->getLabel() == "vipExercise") {
                            $vipExercise = base64_decode($mattext->getContent());
                            $this->object->setVipExercise($vipExercise);
                        } else if ($mattext->getLabel() == "vipEvaluation") {
                            $vipEvaluation = base64_decode($mattext->getContent());
                            $this->object->setVipEvaluation($vipEvaluation);
                        }
                    }
                }
            }
        }

        // -------------------------------------------------------------------------------------------------------------
        // QTI feedback

        $feedbacksgeneric = array();
        foreach ($item->resprocessing as $resprocessing) {
            foreach ($resprocessing->respcondition as $respcondition) {
                foreach ($respcondition->displayfeedback as $feedbackpointer) {
                    if (strlen($feedbackpointer->getLinkrefid())) {
                        foreach ($item->itemfeedback as $ifb) {
                            if ($ifb->getIdent() == "response_allcorrect") {
                                // found a feedback for the identifier
                                if (count($ifb->material)) {
                                    foreach ($ifb->material as $material) {
                                        $feedbacksgeneric[1] = $material;
                                    }
                                }
                                if ((count($ifb->flow_mat) > 0)) {
                                    foreach ($ifb->flow_mat as $fmat) {
                                        if (count($fmat->material)) {
                                            foreach ($fmat->material as $material) {
                                                $feedbacksgeneric[1] = $material;
                                            }
                                        }
                                    }
                                }
                            } elseif ($ifb->getIdent() == "response_onenotcorrect") {
                                // found a feedback for the identifier
                                if (count($ifb->material)) {
                                    foreach ($ifb->material as $material) {
                                        $feedbacksgeneric[0] = $material;
                                    }
                                }
                                if ((count($ifb->flow_mat) > 0)) {
                                    foreach ($ifb->flow_mat as $fmat) {
                                        if (count($fmat->material)) {
                                            foreach ($fmat->material as $material) {
                                                $feedbacksgeneric[0] = $material;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // -------------------------------------------------------------------------------------------------------------
        // QTI general metadata

        $this->addGeneralMetadata($item);
        $this->object->setTitle($item->getTitle());
        $this->object->setNrOfTries($item->getMaxattempts());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($ilUser->getId());
        $this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
        $this->object->setObjId($questionpool_id);
        $this->object->setAdditionalContentEditingMode($this->fetchAdditionalContentEditingModeInformation($item));
        $this->object->saveToDb();

        // -------------------------------------------------------------------------------------------------------------
        // MOB replacements

        foreach ($feedbacksgeneric as $correctness => $material) {
            $m = $this->object->QTIMaterialToString($material);
            $feedbacksgeneric[$correctness] = $m;
        }

        $questiontext = $this->object->getQuestion();
        $feedbacks = $this->getFeedbackAnswerSpecific($item);

        if (is_array($_SESSION["import_mob_xhtml"])) {
            include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
            include_once "./Services/RTE/classes/class.ilRTE.php";
            foreach ($_SESSION["import_mob_xhtml"] as $mob) {
                if ($tst_id > 0) {
                    $importfile = $this->getTstImportArchivDirectory() . '/' . $mob["uri"];
                } else {
                    $importfile = $this->getQplImportArchivDirectory() . '/' . $mob["uri"];
                }

                global $DIC; /* @var ILIAS\DI\Container $DIC */
                $DIC['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);

                $media_object = &ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
                ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());
                $questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
                foreach ($feedbacksgeneric as $correctness => $material) {
                    $feedbacksgeneric[$correctness] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
                }
            }
        }
        $this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
        foreach ($feedbacksgeneric as $correctness => $material) {
            $this->object->feedbackOBJ->importGenericFeedback(
                $this->object->getId(),
                $correctness,
                ilRTE::_replaceMediaObjectImageSrc($material, 1)
            );
        }

        $this->object->saveToDb();

        // -------------------------------------------------------------------------------------------------------------
        // Import mapping

        if ($tst_id > 0) {
            $q_1_id = $this->object->getId();
            $question_id = $this->object->duplicate(true, null, null, null, $tst_id);
            $tst_object->questions[$question_counter++] = $question_id;
            $import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
            $this->object->setId($question_id);
        } else {
            $import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
        }

        return $import_mapping;
    }
}
