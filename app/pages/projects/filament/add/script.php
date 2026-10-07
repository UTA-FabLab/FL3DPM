<?php
/**
 * app/pages/projects/filament/add/script.php
 *
 * Handles the process of adding a filament to a project
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

include(APP_ROOT . 'app/lib/ProjectFilamentForm.php');

class ProjectFilamentAddPage extends \App\ProjectFilamentForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'projects_filament_add_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Project - Add Filament';
        $this->formTitle = 'Project - Add Filament';
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

        //-- check that project print status is ok

            switch($this->project->activePrintStatus()) {
                case PROJECT_PRINT_STATUS_WAITING:
                case PROJECT_PRINT_STATUS_PROBLEM:
                    break;
                default:
                    return false;
            }

        $this->cancelPage = 'projects/view?id=' . $this->project->id . '&tab=filaments';
        return true;
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();

        $this->formVariables['id']->required = true;
        $this->formVariables['filament_id']->required = true;
    }

    protected function loadReadOnlyValues()
    {
        $this->formVariables['project_id']->setID($this->project->type,$this->project->year,$this->project->id);

        $this->formVariables['filament_id']->autoFocus = true;
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->project->id;
    }

    protected function postValidate()
    {
        return true;
    }

    protected function getRecordID()
    {
        return $this->project->id;
    }

    protected function scriptUpdate()
    {
        //-- find the next order #

            $pf = new \App\Table\ProjectFilamentsList;

            if (! $pf->findByProjectID($this->project->id)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            $nextOrderNumber = $pf->count() + 1;

        //-- add the filament

            $params = array(
                'project_id'     => $this->project->id,
                'filament_id'    => $this->formVariables['filament_id']->value,
                'filament_order' => $nextOrderNumber
            );

            if (! $this->projectFilament->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        $this->redirectPage = 'projects/view?id=' . $this->project->id . '&tab=filaments';
        return true;
    }

}

$page = new ProjectFilamentAddPage;
$page->process();

