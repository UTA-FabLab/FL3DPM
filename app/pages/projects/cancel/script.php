<?php
/**
 * app/pages/projects/cancel/script.php
 *
 * Handles the process of cancelling a project
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

include(APP_ROOT . 'app/lib/ProjectForm.php');

class ProjectCancelPage extends \App\ProjectForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'projects_cancel_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Project - Cancel';
        $this->formTitle = 'Project - Cancel';
    }

    protected function userHasAccess()
    {
        if (! \App\Request::$user->isRealUser()) {
            return false;
        }

        if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS, APP_ACL_STUDENT_LEAD))) {
            return true;
        }

        return false;
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();
        $this->formVariables['id']->required = true;

        $this->formVariables['college_id'] = new \Framework\Form\Variable\Text;
        $this->formVariables['college_id']->readOnly = true;
        $this->formVariables['college_id']->name = 'college_id';
        $this->formVariables['college_id']->label = '<b>College</b>';

        $this->formVariables['category'] = new \Framework\Form\Variable\Text;
        $this->formVariables['category']->readOnly = true;
        $this->formVariables['category']->name = 'category';
        $this->formVariables['category']->label = '<b>Category</b>';

        $this->formVariables['cancel_reason'] = new \App\Form\Variable\CancelReason;
        $this->formVariables['cancel_reason']->required = true;
        $this->formVariables['cancel_reason']->loadSelectOptions();
    }

    protected function loadReadOnlyValues()
    {
        $this->formVariables['customer_id']->value = $this->project->customer_id;
        $this->formVariables['email']->value = $this->project->email;
        $this->formVariables['first_name']->value = $this->project->first_name;
        $this->formVariables['last_name']->value = $this->project->last_name;
        $this->formVariables['category']->value = $this->project->normalize('category');
        $this->formVariables['college_id']->value = $this->project->normalize('college_id');
        if ($this->project->cancel_date != DEFAULT_UNDEFINED_DATE) {
            $this->formVariables['cancel_date']->value = date('Y-m-d',$this->project->cancel_date);
        }

        $this->formVariables['project_id']->setID($this->project->type,$this->project->year,$this->project->id);
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->project->id;
        $this->formVariables['cancel_reason']->autoFocus = true;
    }

    protected function getRecordID()
    {
        return $this->project->id;
    }

    protected function postValidate()
    {
        return true;
    }

    protected function scriptUpdate()
    {
        //-- Attempt to get a lock to prevent inconsistency

            $record = \App\Request::$db->getRowLock('projects','id', $this->project->id);

            if ($record === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            switch($record['status']) {
                case PROJECT_STATUS_ACTIVE:
                    break;
                default:
                    return true;
                    
            }

        //-- Attempt to get a lock to prevent inconsistency
        //-- Cancel the project

            $params = array(
                'status'        => PROJECT_STATUS_CLOSED,
                'cancel_reason' => $this->formVariables['cancel_reason']->value,
            );

            if (! $this->project->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- close all of the prints

            $projectPrints = new \App\Table\ProjectPrintsList;

            $projectPrints->whereExpressions(array('pp.project_id = :project_id'));

            $params = array(
                'project_id' => $this->project->id
            );

            if (! $projectPrints->query($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            while ($projectPrints->next()) {

                switch($projectPrints->status) {
                    case PROJECT_PRINT_STATUS_CANCELLED:
                    case PROJECT_PRINT_STATUS_RESOLVED:
                    case PROJECT_PRINT_STATUS_COMPLETE:
                        continue 2;
                        break;
                }


                $printRecord = new \App\Table\ProjectPrintsRecord();

                if (! $printRecord->findByID($projectPrints->id)) {
                    $this->formErrorMessage = ERROR_MESSAGE_011;
                    return false;
                }

                $params = array(
                    'status' => PROJECT_PRINT_STATUS_CANCELLED,
                    'cancel_reason' => $this->formVariables['cancel_reason']->value
                );

                if (! $printRecord->update($params)) {
                    $this->formErrorMessage = ERROR_MESSAGE_011;
                    return false;
                } 

                $projectPrintProblems = new \App\Table\ProjectPrintProblemsList;

                $sql = $projectPrintProblems->fetchSQL() . ' WHERE ppp.print_id = :print_id AND ppp.action_taken = :action_taken';

                $params = array(
                    'print_id'     => $printRecord->id,
                    'action_taken' => PROJECT_PRINT_ACTION_TAKEN_NONE
                );

                if (! $projectPrintProblems->query($params, $sql)) {
                    $this->formErrorMessage = ERROR_MESSAGE_011;
                    return false;
                }

                while ($projectPrintProblems->next()) {

                    $problem = new \App\ProjectPrintProblemsRecord;

                    if (! $problem->findByID($projectPrintProblems->id)) {
                        $this->formErrorMessage = ERROR_MESSAGE_011;
                        return false;
                    }

                    $params = array(
                        'action_taken'        => PROJECT_PRINT_ACTION_TAKEN_CANCEL_PROJECT,
                        'action_taken_date'   => \time()
                    );

                    if (! $problem->update($params)) {
                        $this->formErrorMessage = ERROR_MESSAGE_011;
                        return false;
                    }

                }

            }

        //-- redirect to the project page

            $this->redirectPage  = 'projects/view?id=' . $this->project->id;
            return true;
    }
}

$page = new ProjectCancelPage;
$page->process();


