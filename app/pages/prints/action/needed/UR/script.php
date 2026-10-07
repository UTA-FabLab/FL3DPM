<?php
/**
 * app/pages/prints/action/needed/UR/script.php
 *
 * Handles the process of taking action due to re-slice needed from learner
 *
 * @package App
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace App;

require(APP_ROOT . 'app/lib/ProjectPrintProblemForm.php');

class PrintProblemForm extends \App\ProjectPrintProblemForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'prints_action_needed_UR_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Project Print Problem - Resolve';
        $this->formTitle = 'Project Print Problem - Resolve';

        $this->targetProblem = PROJECT_PRINT_ACTION_NEEDED_UPLOAD_RESLICE;
        $this->findCheckFilaments = true;
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();

        $optionsArray = array(
            PROJECT_PRINT_ACTION_TAKEN_UPLOAD_RESLICE => \App\ProjectPrintActionTaken::getText(PROJECT_PRINT_ACTION_TAKEN_UPLOAD_RESLICE)
        );

        $this->formVariables['resolution']->loadSelectOptions($optionsArray);

        $this->formVariables['gcode_file'] = new \App\Form\Variable\GCodeFile;
        $this->formVariables['gcode_file']->required = true;

        $this->formVariables['stl_file'] = new \App\Form\Variable\STLFile;
        $this->formVariables['stl_file']->optional = true;
        $this->formVariables['stl_file']->label = 'STL File (optional)';
    }

    protected function getRecordID()
    {
        return $this->projectPrintProblem->id;
    }

    protected function scriptUpdate()
    {
        $this->redirectPage = 'prints/view?id=' . $this->projectPrint->id . '&tab=problems';

        switch($this->formVariables['resolution']->value) {

            case PROJECT_PRINT_ACTION_TAKEN_UPLOAD_RESLICE:
                if (! $this->problemResolutionUploadReslice()) {
                    return false;
                }
                break;

            default:
               $this->formErrorMessage = ERROR_MESSAGE_011;
               return false;
               break;
        }

        return true;
    }

}

$form = new PrintProblemForm();
$form->process();

