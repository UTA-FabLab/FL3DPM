<?php
/**
 * app/pages/prints/action/needed/OST/script.php
 *
 * Handles the process of taking action to open support ticket before reprint
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
        $this->csrfTokenName = 'prints_action_needed_OST_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Project Print Problem - Resolve';
        $this->formTitle = 'Project Print Problem - Resolve';

        $this->targetProblem = PROJECT_PRINT_ACTION_NEEDED_OPEN_SUPPORT_TICKET;
        $this->findCheckFilaments = false;
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();

        $this->formVariables['resolution']->loadSelectOptions(array(
            PROJECT_PRINT_ACTION_TAKEN_REPRINT => \App\ProjectPrintActionTaken::getText(PROJECT_PRINT_ACTION_TAKEN_REPRINT)
        ));
    }

    protected function getRecordID()
    {
        return $this->projectPrintProblem->id;
    }

    protected function scriptUpdate()
    {
        $this->redirectPage = 'prints/view?id=' . $this->projectPrint->id . '&tab=problems';

        switch($this->formVariables['resolution']->value) {

            case PROJECT_PRINT_ACTION_TAKEN_REPRINT:
                if (! $this->problemResolutionReprint()) {
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

    public function render()
    {
        $this->footIncludeJS(\Framework\siteURL('assets/js/prints-action-needed-OST.js'));
        parent::render();
    }

}

$form = new PrintProblemForm();
$form->process();


