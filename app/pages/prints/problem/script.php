<?php
/**
 * app/pages/prints/problem/script.php
 *
 * Handles the process of reporting a problem with a print job
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

class PrintProblemForm extends \App\ProjectPrintForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'prints_problem_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Print - Problem';
        $this->formTitle = 'Print - Problem';
    }

    protected function canEdit()
    {
        if ($this->project->status == PROJECT_STATUS_ACTIVE) {
            switch($this->project->activePrintStatus()) {
                case PROJECT_PRINT_STATUS_PRINTING:
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
        $this->formVariables['problem']->required = true;
        $this->formVariables['problem']->loadSelectOptions();

        $this->formVariables['note'] = new \MiddleWare\Form\Variable\Note;
        $this->formVariables['note']->optional = true;
    }

    protected function loadReadOnlyValues()
    {
        parent::loadReadOnlyValues();
        $this->formVariables['project_id']->value = $this->project->normalize('id');
        $this->formVariables['problem']->autoFocus = true;
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
        if ($this->formVariables['problem']->value == PROJECT_PRINT_PROBLEM_OTHER) {
            $noteText = trim($this->formVariables['note']->value);
            if ($noteText == '') {
                $this->formErrorMessage = ERROR_MESSAGE_023;
                return false;
                
            }
        }

        return true;
    }

    protected function updateRecordEmailColor()
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

        //-- Attempt to send the email

            if (! \App\Mail(MAIL_TYPE_PROBLEM_COLOR, $this->project->email, $this->project->normalize('id'))) {
                $this->formErrorMessage = ERROR_MESSAGE_022;
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

            $updateTime = \time();

            $problem = new \App\Table\ProjectPrintProblemsRecord;

            $params = array(
                'print_id'            => $this->projectPrint->id,
                'problem'             => $this->formVariables['problem']->value,
                'action_needed'       => PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_COLOR,
                'action_needed_date'  => $updateTime,
                'action_taken'        => PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_COLOR,
                'action_taken_date'   => $updateTime,
            );

            if (! $problem->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Create a problem record that user needs to pick color

            $updateTime++;

            $problem = new \App\Table\ProjectPrintProblemsRecord;

            $params = array(
                'print_id'            => $this->projectPrint->id,
                'problem'             => $this->formVariables['problem']->value,
                'action_needed'       => PROJECT_PRINT_ACTION_NEEDED_REPRINT,
                'action_needed_date'  => $updateTime,
                'action_taken'        => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'action_taken_date'   => DEFAULT_UNDEFINED_DATE
            );

            if (! $problem->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }


        return true;
    }

    protected function updateRecordEmailReslice()
    {
        //-- Attempt to get a lock to prevent duplication of new print

            $record = \App\Request::$db->getRowLock('project_prints','id', $this->projectPrint->id);

            if ($record === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            if ($record['status'] == PROJECT_PRINT_STATUS_PROBLEM) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Attempt to send the email

            if (! \App\Mail(MAIL_TYPE_PROBLEM_RESLICE, $this->project->email, $this->project->normalize('id'))) {
                $this->formErrorMessage = ERROR_MESSAGE_022;
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

            $updateTime = \time();

            $problem = new \App\Table\ProjectPrintProblemsRecord;

            $params = array(
                'print_id'            => $this->projectPrint->id,
                'problem'             => $this->formVariables['problem']->value,
                'action_needed'       => PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_RESLICE,
                'action_needed_date'  => $updateTime,
                'action_taken'        => PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_RESLICE,
                'action_taken_date'   => \time()
            );

            if (! $problem->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Create a problem record that needs Reslice Uploaded

            $updateTime++;

            $problem = new \App\Table\ProjectPrintProblemsRecord;

            $params = array(
                'print_id'            => $this->projectPrint->id,
                'problem'             => $this->formVariables['problem']->value,
                'action_needed'       => PROJECT_PRINT_ACTION_NEEDED_UPLOAD_RESLICE,
                'action_needed_date'  => $updateTime,
                'action_taken'        => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'action_taken_date'   => DEFAULT_UNDEFINED_DATE
            );

            if (! $problem->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        return true;
    }

    protected function updateRecordOther($inActionNeeded, $inActionTaken)
    {
        //-- Attempt to get a lock to prevent duplication of new print

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

        //-- Create a problem record

            $updateTime = \time();

            $problem = new \App\Table\ProjectPrintProblemsRecord;

            $params = array(
                'print_id'            => $this->projectPrint->id,
                'problem'             => $this->formVariables['problem']->value,
                'action_needed'       => $inActionNeeded,
                'action_needed_date'  => $updateTime,
                'action_taken'        => $inActionTaken,
                'action_taken_date'   => DEFAULT_UNDEFINED_DATE
            );

            if (! $problem->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- If problem is Other then store the explanation

            if ($this->formVariables['problem']->value == PROJECT_PRINT_PROBLEM_OTHER) {

                $updateTime++;

                $projectNote = new \App\Table\ProjectNotesRecord;

                $params = array(
                    'project_id' => $this->projectPrint->project_id,
                    'print_id'   => 0,
                    'note'       => trim(substr($this->formVariables['note']->value,0,9999)),
                    'posted'     => $updateTime,
                    'posted_by'  => \App\Request::$user->userID
                );

                if (! $projectNote->insert($params)) {
                    $this->formErrorMessage = ERROR_MESSAGE_011;
                    return false;
                }

            }

            $this->redirectPage = 'prints/view?id=' . $this->projectPrint->id . '&tab=problems';
            return true;
    }

    protected function scriptUpdate()
    {
        //-- Make sure we can find the action needed

            $actionNeeded = \App\ProjectPrintProblems::getActionNeeded($this->formVariables['problem']->value);

            if ($actionNeeded === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Make sure we can find the action taken

            $actionTaken = \App\ProjectPrintProblems::getActionTaken($this->formVariables['problem']->value);

            if ($actionTaken === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Call correct sub-update function

            switch ($actionNeeded) {
                case PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_COLOR:
                    if (! $this->updateRecordEmailColor()) {
                        return false;
                    }
                    break;

                case PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_RESLICE:
                    if (! $this->updateRecordEmailReslice()) {
                        return false;
                    }
                    break;

                case PROJECT_PRINT_ACTION_NEEDED_REPRINT:
                    if (! $this->updateRecordOther($actionNeeded,$actionTaken)) {
                        return false;
                    }
                    break;

                default:
                    if (! $this->updateRecordOther($actionNeeded, $actionTaken)) {
                        return false;
                    }
                    break;
            }


        //-- Go back to the print view

            $this->redirectPage = 'prints/view?id=' . $this->projectPrint->id . '&tab=problems';
            return true;
    }

    public function render()
    {
        $this->footIncludeJS(\Framework\siteURL('assets/js/prints-problem.js'));
        parent::render();
    }

}

$form = new PrintProblemForm();
$form->process();

