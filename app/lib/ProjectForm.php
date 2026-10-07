<?php
/**
 * app/lib/ProjectForm.php
 *
 * Common Form for Projects
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

class ProjectForm extends \App\Form
{
    protected $project;

    public function __construct()
    {
        $this->defaultCancelPage = 'projects/list';
        parent::__construct();
        $this->project = new \App\Table\ProjectsRecord();
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

    protected function buildFormVariables()
    {
        $this->formVariables['id'] = new \MiddleWare\Form\Variable\RecordID;
        $this->formVariables['id']->readOnly = true;

        //-- this is used to display normalized representation of the project id
        $this->formVariables['project_id'] = new \App\Form\Variable\ProjectID;

        $this->formVariables['customer_id'] = new \MiddleWare\Form\Variable\UniversityID;
        $this->formVariables['customer_id']->readOnly = true;

        $this->formVariables['email'] = new \MiddleWare\Form\Variable\UniversityEmail;
        $this->formVariables['email']->readOnly = true;

        $this->formVariables['first_name'] = new \MiddleWare\Form\Variable\FirstName;
        $this->formVariables['first_name']->readOnly = true;

        $this->formVariables['last_name'] = new \MiddleWare\Form\Variable\LastName;
        $this->formVariables['last_name']->readOnly = true;

        $this->formVariables['cancel_date'] = new \App\Form\Variable\CancelDate;
        $this->formVariables['cancel_date']->readOnly = true;

        $this->formVariables['consult_date_time'] = new \App\Form\Variable\ConsultDateTime;
        $this->formVariables['consult_date_time']->readOnly = true;

        $this->formVariables['contacted'] = new \App\Form\Variable\Contacted;
        $this->formVariables['contacted']->readOnly = true;

        $this->formVariables['college_id'] = new \App\Form\Variable\CollegeID;
        $this->formVariables['college_id']->readOnly = true;

        $this->formVariables['printer_id'] = new \App\Form\Variable\PrinterID;
        $this->formVariables['printer_id']->readOnly = true;

        $this->formVariables['category'] = new \App\Form\Variable\ProjectCategory;
        $this->formVariables['category']->readOnly = true;
    }

    protected function findRecord($inProjectID = false)
    {
       //-- if no project id passed in retrieve from the query parameters

            if ($inProjectID === false) {
                $inProjectID = \App\Request::getQP('id');
                if ($inProjectID === false) {
                    return false;
                }
            }

       //-- find the project by id

            if (! $this->project->findByID($inProjectID)) {
                return false;
            }

            $this->cancelPage = \Framework\NavStack::getBackURL(true,true);

        return true;
    }

    protected function canEdit()
    {
        if ($this->project->canEditProject()) {
            return true;
        }

        return false;
    }

}


