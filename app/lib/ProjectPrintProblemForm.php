<?php
/**
 * app/lib/ProjectPrintProblemForm.php
 *
 * Defines a form for working with Project Print Problems
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

require(APP_ROOT . 'app/lib/ProblemResolutions.php');

class ProjectPrintProblemForm extends \App\Form
{
    use ProblemResolutions;

    protected $targetProblem;
    protected $project;
    protected $projectPrint;
    protected $projectPrintProblem;
    protected $problemLocked;
    protected $fabAppServiceTicketURL;
    protected $findCheckFilaments;

    public function __construct()
    {
        parent::__construct();
        $this->defaultCancelPage = 'projects/list';
        $this->cancelPage = \Framework\NavStack::getBackURL(inGetTop: true, inNoPrefix: true);
        if ($this->cancelPage == '') {
            $this->cancelPage = $this->defaultCancelPage;
        }

        $this->targetProblem = PROJECT_PRINT_PROBLEM_NONE;
        $this->project = new \App\Table\ProjectsRecord();
        $this->projectPrint = new \App\Table\ProjectPrintsRecord();
        $this->projectPrintProblem = new \App\Table\ProjectPrintProblemsRecord();
        $this->problemLocked = true;
        $this->fabAppServiceTicketURL = \Middleware\getConfigValue('FABAPP_SERVICE_TICKET_URL');
        $this->findCheckFilaments = false;
    }

    protected function userHasAccess()
    {
        if (! \App\Request::$user->isRealUser()) {
            return false;
        }

        if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS, APP_ACL_STUDENT))) {
            return true;
        }

        return false;
    }

    protected function findRecord($inProjectPrintProblemID = false)
    {
       //-- if no project print problem id passed in retrieve from the query parameters

            if ($inProjectPrintProblemID === false) {
                $inProjectPrintProblemID = \App\Request::getQP('id');
                if ($inProjectPrintProblemID === false) {
                    return false;
                }
            }

       //-- find the problem by id

            if (! $this->projectPrintProblem->findByID($inProjectPrintProblemID)) {
                return false;
            }

       //-- find the print by id

            if (! $this->projectPrint->findByID($this->projectPrintProblem->print_id)) {
                return false;
            }

       //-- make sure problem belongs to the print

           if ($this->projectPrintProblem->print_id != $this->projectPrint->id) {
                return false;
           }

        //-- find the project by id

            if (! $this->project->findByID($this->projectPrint->project_id)) {
                return false;
            }

       //-- make sure print belongs to the project

            if ($this->projectPrint->project_id != $this->project->id) {
                return false;
            }

            //-- if called from prints view page, then select problems tab
            if (preg_match('/prints\/view\?id=\d+$/', $this->cancelPage) == 1) {
                $this->cancelPage .= '&tab=problems';
            }

        //-- check to make sure there are some filaments assigned

            if ($this->findCheckFilaments) {
                $filaments = new \App\Table\ProjectFilamentsList;
 
                if (! $filaments->findByProjectID($this->project->id)) {
                    return false;
                }

                if ($filaments->count() == 0) {
                    return false;
                }
            }

        return true;
    }

    protected function canEdit()
    {
        if ($this->project->status != PROJECT_STATUS_ACTIVE) {
            return false;
        }

        if ($this->projectPrint->status != PROJECT_PRINT_STATUS_PROBLEM) {
            return false;
        }

        if ($this->projectPrintProblem->action_needed != $this->targetProblem) {
            return false;
        }

        //-- get the problem lock count

            $problemLockCount = \Middleware\getConfigValue('PROBLEM_LOCK_COUNT');

            if ($problemLockCount === false) {
                return false;
            }

            if (! is_numeric($problemLockCount)) {
                return false;
            }

            $problemLockCount = intval($problemLockCount);

        //-- find a list of the problems, and make sure that a problem has not occurred multiple times

            if (\App\ProjectPrintProblems::lockCountable($this->projectPrintProblem->problem,'')) {

                $projectPrintProblems = new \App\Table\ProjectPrintProblemsList;

                $projectPrintProblems->selectExpressions(
                    array_merge(
                        $projectPrintProblems->selectFieldList(inAsArray: true),
                    )
                );

                $projectPrintProblems->joinClauses(array(
                    'LEFT JOIN ' . APP_MYSQL_DB . '.project_prints AS p ON p.id = ppp.print_id',
                ));

                $projectPrintProblems->whereExpressions(array('p.project_id = :project_id '));
 
                $projectPrintProblems->orderBy('action_needed_date DESC');

                $params = array(
                    'project_id' => $this->project->id
                );
 
                if (! $projectPrintProblems->query($params)) {
                    return false;
                }

                $problemCount = 0;

                while ($projectPrintProblems->next()) {
                    if (\App\ProjectPrintProblems::lockCountable($projectPrintProblems->problem, $projectPrintProblems->action_needed)) {
                        $problemCount++;
                    }
                }

                if ($problemLockCount == 0) {
                    $this->problemLocked = false;
                } else if ($problemCount < $problemLockCount) {
                    $this->problemLocked = false;
                } else {
                    if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS,APP_ACL_STUDENT_LEAD))) {
                        $this->problemLocked = false;
                    }
                }
            } else {
                $this->problemLocked = false;
            }

        return true;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['id'] = new \MiddleWare\Form\Variable\RecordID;
        $this->formVariables['id']->id = 'id';
        $this->formVariables['id']->name = 'id';
        $this->formVariables['id']->required = true;

        $this->formVariables['project_id'] = new \App\Form\Variable\ProjectID;
        $this->formVariables['project_id']->readOnly = true;

        $this->formVariables['print_id'] = new \MiddleWare\Form\Variable\RecordID;
        $this->formVariables['print_id']->id = 'print_id';
        $this->formVariables['print_id']->name = 'print_id';
        $this->formVariables['print_id']->readOnly = true;

        $this->formVariables['problem'] = new \App\Form\Variable\PrintProblem;
        $this->formVariables['problem']->readOnly = true;
        $this->formVariables['problem']->loadSelectOptions();

        $this->formVariables['action_needed'] = new \Framework\Form\Variable\Text;
        $this->formVariables['action_needed']->readOnly = true;
        $this->formVariables['action_needed']->label = 'Action Needed';
        $this->formVariables['action_needed']->boldLabel = true;

        $this->formVariables['resolution'] = new \App\Form\Variable\ProblemResolution;
        $this->formVariables['resolution']->required = true;
    }

    protected function loadReadOnlyValues()
    {
        $this->formVariables['project_id']->value = $this->project->normalize('id');
        $this->formVariables['problem']->setValue($this->projectPrintProblem->problem);
        $this->formVariables['action_needed']->setValue($this->projectPrintProblem->normalize('action_needed'));
        $this->formVariables['resolution']->autoFocus = true;
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->projectPrintProblem->id;
    }

    protected function postValidate()
    {
        if ($this->problemLocked) {
            return false;
        }

        return true;
    }
}

