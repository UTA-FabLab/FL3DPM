<?php
/**
 * app/lib/ProjectPrintForm.php
 *
 * Defines a form for working with Project Prints
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

class ProjectPrintForm extends \App\Form
{
    protected $project;
    protected $projectPrint;

    public function __construct()
    {
        parent::__construct();

        $this->project = new \App\Table\ProjectsRecord();
        $this->projectPrint = new \App\Table\ProjectPrintsRecord();
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

    protected function findRecord($inProjectPrintID = false)
    {
       //-- if no project print id passed in retrieve from the query parameters

            if ($inProjectPrintID === false) {
                $inProjectPrintID = \App\Request::getQP('id');
                if ($inProjectPrintID === false) {
                    return false;
                }
            }

       //-- find the project print by id

            if (! $this->projectPrint->findByID($inProjectPrintID)) {
                return false;
            }

        //-- find the project by id

            if (! $this->project->findByID($this->projectPrint->project_id)) {
                return false;
            }

       //-- make sure project print belongs to the project

           if ($this->projectPrint->project_id != $this->project->id) {
                return false;
           }

        return true;
    }

    // should be overidden
    protected function canEdit()
    {
        return false;
    }

    protected function buildFormVariables()
    {
        // set fields as readonly/nocollect by default, specific form will set required

        $this->formVariables['id'] = new \MiddleWare\Form\Variable\RecordID;
        $this->formVariables['id']->readOnly = true;

        $this->formVariables['project_id'] = new \App\Form\Variable\ProjectID;

        $this->formVariables['ticket_number'] = new \App\Form\Variable\TicketNumber;
        $this->formVariables['ticket_number']->readOnly = true;

        $this->formVariables['printer_id'] = new \App\Form\Variable\PrinterID;
        $this->formVariables['printer_id']->readOnly = true;
    }

    protected function loadReadOnlyValues()
    {
        $this->formVariables['project_id']->setID($this->project->type,$this->project->year,$this->project->id);
    }

}


