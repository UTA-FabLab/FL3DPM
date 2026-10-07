<?php
/**
 * app/pages/projects/note/script.php
 *
 * Handles the process of adding a note to a project
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

class ProjectNotePage extends \App\ProjectForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'projects_note_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Project - Add Note';
        $this->formTitle = 'Project - Add Note';
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();

        $this->formVariables['id']->required = true;
        $this->formVariables['category']->loadSelectOptions();
        $this->formVariables['college_id']->loadSelectOptions();

        $this->formVariables['note'] = new \MiddleWare\Form\Variable\Note;
        $this->formVariables['note']->required = true;
        $this->formVariables['note']->autoFocus = true;
    }

    protected function loadReadOnlyValues()
    {
        $this->formVariables['customer_id']->value = $this->project->customer_id;
        $this->formVariables['email']->value = $this->project->email;
        $this->formVariables['first_name']->value = $this->project->first_name;
        $this->formVariables['last_name']->value = $this->project->last_name;
        $this->formVariables['category']->value = $this->project->category;
        $this->formVariables['college_id']->value = $this->project->college_id;

        $this->formVariables['project_id']->setID($this->project->type,$this->project->year,$this->project->id);
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->project->id;
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
        $projectNote = new \App\Table\ProjectNotesRecord;

        $params = array(
            'project_id' => $this->project->id,
            'print_id'   => 0,
            'note'       => substr($this->formVariables['note']->value,0,9999),
            'posted'     => \time(),
            'posted_by'  => \App\Request::$user->userID,
        );

        if (! $projectNote->insert($params)) {
            $this->formErrorMessage = ERROR_MESSAGE_011;
            return false;
        }

        $this->redirectPage  = \Framework\NavStack::getBackURL(true,true);
        return true;
    }
}

$page = new ProjectNotePage;
$page->process();


