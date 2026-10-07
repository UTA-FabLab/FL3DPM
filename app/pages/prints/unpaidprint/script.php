<?php
/**
 * app/pages/prints/unpaidprint/script.php
 *
 * Handles the process of reporting a problem with a print job whena student has not paid for previous print
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

require(APP_ROOT . 'app/lib/ProjectPrintForm.php');

class UnpaidPrintForm extends \App\ProjectPrintForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'prints_unpaidprint_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Print - Unpaid Print';
        $this->formTitle = 'Print - Unpaid Print';
    }

    protected function canEdit()
    {
        if ($this->project->status == PROJECT_STATUS_ACTIVE) {
            switch($this->project->activePrintStatus()) {
                case PROJECT_PRINT_STATUS_WAITING:
                    return true;
                    break;
            }
        }
        return false;
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();
        $this->formVariables['id']->required = true;

        $this->formVariables['project_id']->readOnly = true;

        $this->formVariables['problem'] = new \App\Form\Variable\PrintProblem;
        $this->formVariables['problem']->readOnly = true;
        $this->formVariables['problem']->setInitialOption(PROJECT_PRINT_PROBLEM_PREVIOUS_PRINT,\App\ProjectPrintProblems::getText(PROJECT_PRINT_PROBLEM_PREVIOUS_PRINT));
        $this->formVariables['problem']->setSelectOptions(array(
            PROJECT_PRINT_PROBLEM_PREVIOUS_PRINT => \App\ProjectPrintProblems::getText(PROJECT_PRINT_PROBLEM_PREVIOUS_PRINT),
        ));
    }

    protected function loadReadOnlyValues()
    {
        parent::loadReadOnlyValues();
        $this->formVariables['project_id']->value = $this->project->normalize('id');
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->projectPrint->id;
    }

    protected function getRecordID()
    {
        return $this->projectPrint->id;
    }

    protected function postValidate()
    {
        return true;
    }

    protected function updateRecordEmailPickup()
    {
        //-- Attempt to get a lock

            $record = \App\Request::$db->getRowLock('project_prints','id', $this->projectPrint->id);

            if ($record === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            if ($record['status'] == PROJECT_PRINT_STATUS_PROBLEM) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Update the Project Print job to problem

            $params = array(
                'status' => PROJECT_PRINT_STATUS_PROBLEM
            );

            if (! $this->projectPrint->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Create a problem record that learner was emailed

            $problem = new \App\Table\ProjectPrintProblemsRecord;

            $params = array(
                'print_id'            => $this->projectPrint->id,
                'problem'             => PROJECT_PRINT_PROBLEM_PREVIOUS_PRINT,
                'action_needed'       => PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_PICKUP,
                'action_needed_date'  => \time(),
                'action_taken'        => PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_PICKUP,
                'action_taken_date'   => \time()
            );

            if (! $problem->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Create a problem record that needs Waiting on Learner Pickup

            $problem = new \App\Table\ProjectPrintProblemsRecord;

            $params = array(
                'print_id'            => $this->projectPrint->id,
                'problem'             => PROJECT_PRINT_PROBLEM_PREVIOUS_PRINT,
                'action_needed'       => PROJECT_PRINT_ACTION_NEEDED_LEARNER_PICKUP,
                'action_needed_date'  => \time(),
                'action_taken'        => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'action_taken_date'   => DEFAULT_UNDEFINED_DATE
            );

            if (! $problem->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Attempt to send the email

            if (! \App\Mail(MAIL_TYPE_PROBLEM_UNPAID, $this->project->email, $this->project->normalize('id'))) {
                $this->formErrorMessage = ERROR_MESSAGE_022;
                return false;
            }

        return true;
    }

    protected function scriptUpdate()
    {
        //-- Make sure we can find the action needed

            $actionNeeded = \App\ProjectPrintProblems::getActionNeeded(PROJECT_PRINT_PROBLEM_PREVIOUS_PRINT);

            if ($actionNeeded === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Make sure we can find the action taken

            $actionTaken = \App\ProjectPrintProblems::getActionTaken(PROJECT_PRINT_PROBLEM_PREVIOUS_PRINT);

            if ($actionTaken === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Call correct sub-update function

            switch ($actionNeeded) {
                case PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_PICKUP:
                    if (! $this->updateRecordEmailPickup()) {
                        return false;
                    }
                    break;

                default:
                    $this->formErrorMessage = ERROR_MESSAGE_011;
                    return false;
                    break;
            }

        $this->redirectPage = 'prints/view?id=' . $this->projectPrint->id . '&tab=problems';
        return true;
    }

}

$form = new UnpaidPrintForm();
$form->process();

