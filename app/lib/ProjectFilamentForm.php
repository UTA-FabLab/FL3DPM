<?php
/**
 * app/lib/ProjectFilamentForm.php
 *
 * Defines a form for working with Project Filaments
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
 
class ProjectFilamentForm extends \App\Form
{
    protected $project;
    protected $projectFilament;
 
    public function __construct()
    {
        parent::__construct();
        $this->project = new \App\Table\ProjectsRecord();
        $this->projectFilament = new \App\Table\ProjectFilamentsRecord();
        $this->defaultCancelPage = 'projects/list';
        $this->cancelPage = \Framework\NavStack::getBackURL(inGetTop: true, inNoPrefix: true);
        if ($this->cancelPage == '') {
            $this->cancelPage = $this->defaultCancelPage;
        }
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

    protected function findRecord($inProjectFilamentID = false)
    {
       //-- if no project filament id passed in retrieve from the query parameters

            if ($inProjectFilamentID === false) {
                $inProjectFilamentID = \App\Request::getQP('id');
                if ($inProjectFilamentID === false) {
                    return false;
                }
            }

       //-- find the filament by id

            if (! $this->projectFilament->findByID($inProjectFilamentID)) {
                return false;
            }

        //-- find the project by id

            if (! $this->project->findByID($this->projectFilament->project_id)) {
                return false;
            }

        //-- check that project print status is ok

            switch($this->project->activePrintStatus()) {
                case PROJECT_PRINT_STATUS_WAITING:
                case PROJECT_PRINT_STATUS_PROBLEM:
                    break;
                default:
                    return false;
            }

       //-- make sure filament belongs to the project

           if ($this->projectFilament->project_id != $this->project->id) {
                return false;
           }

        $this->cancelPage = 'projects/view?id=' . $this->project->id . '&tab=filaments';
        return true;
    }

    protected function canEdit()
    {
        // access is controlled through userHasAccess and project status
        return true;
    }


    protected function buildFormVariables()
    {
        $this->formVariables['id'] = new \MiddleWare\Form\Variable\RecordID;
        $this->formVariables['id']->readOnly = true;

        $this->formVariables['project_id'] = new \App\Form\Variable\ProjectID;
        
        $this->formVariables['filament_id'] = new \App\Form\Variable\FilamentID;
        $this->formVariables['filament_id']->loadSelectOptions();
        $this->formVariables['filament_id']->readOnly = true;
    }
 
}


