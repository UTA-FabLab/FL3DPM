<?php
/**
 * app/pages/projects/edit/script.php
 *
 * Handles the process of editing a project
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

class ProjectEditPage extends \App\ProjectForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'projects_edit_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Project - Edit';
        $this->formTitle = 'Project - Edit';
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();

        $this->formVariables['id']->required = true;
        $this->formVariables['customer_id']->required = true;
        $this->formVariables['email']->required = true;
        $this->formVariables['first_name']->required = true;
        $this->formVariables['last_name']->required = true;
        $this->formVariables['cancel_date']->optional = true;
        $this->formVariables['cancel_date']->optionalCallValidate = true;
        $this->formVariables['category']->required = true;
        $this->formVariables['college_id']->required = true;

        $this->formVariables['category']->loadSelectOptions();
        $this->formVariables['college_id']->loadSelectOptions();

    }

    protected function loadReadOnlyValues()
    {
        $this->formVariables['college_id']->autoFocus = true;
        $this->formVariables['project_id']->setID($this->project->type,$this->project->year,$this->project->id);
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->project->id;
        $this->formVariables['customer_id']->value = $this->project->customer_id;
        $this->formVariables['email']->value = $this->project->email;
        $this->formVariables['first_name']->value = $this->project->first_name;
        $this->formVariables['last_name']->value = $this->project->last_name;
        $this->formVariables['category']->value = $this->project->category;
        $this->formVariables['college_id']->value = $this->project->college_id;
        if ($this->project->cancel_date != DEFAULT_UNDEFINED_DATE) {
            $this->formVariables['cancel_date']->value = date('Y-m-d',$this->project->cancel_date);
        }
    }

    protected function getRecordID()
    {
        return $this->project->id;
    }

    protected function postValidate()
    {
        //-- check that cancel date is in the future if provided

            if ($this->formVariables['cancel_date']->ts != DEFAULT_UNDEFINED_DATE) {
                if ($this->formVariables['cancel_date']->ts < \time()) {
                    $this->formVariables['cancel_date']->setInvalid('Cancel date must be in the future');
                    return false;
                }
            }

        //-- make sure there is not already an open project using this customer id

            $projects = new \App\Table\ProjectsList();
            $projects->whereExpressions(array('customer_id = :customer_id'));

            $params = array(
                'customer_id' => $this->formVariables['customer_id']->value
            );

            if (! $projects->query($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            while ($projects->next()) {
                switch($projects->status) {
                    case PROJECT_STATUS_CLOSED:
                        break;
                    default:
                        if ($projects->id != $this->project->id) {
                            $this->formErrorMessage = ERROR_MESSAGE_012 . ' (' . $projects->normalize('id') . ')';
                            return false;
                        }
                }
            }

        //-- make sure there is not already an open project using this email

            $projects = new \App\Table\ProjectsList();
            $projects->whereExpressions(array('email= :email'));

            $params = array(
                'email' => $this->formVariables['email']->value
            );

            if (! $projects->query($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            while ($projects->next()) {
                switch($projects->status) {
                    case PROJECT_STATUS_CLOSED:
                        break;
                    default:
                        if ($projects->id != $this->project->id) {
                            $this->formErrorMessage = ERROR_MESSAGE_012 . ' (' . $projects->normalize('id') . ')';
                            return false;
                        }
                }
            }

        return true;
    }


    protected function scriptUpdate()
    {
        $params = array(
            'customer_id' => $this->formVariables['customer_id']->value,
            'email'       => $this->formVariables['email']->value,
            'first_name'  => $this->formVariables['first_name']->value,
            'last_name'   => $this->formVariables['last_name']->value,
            'cancel_date' => $this->formVariables['cancel_date']->ts,
            'college_id'  => $this->formVariables['college_id']->value,
            'category'    => $this->formVariables['category']->value,
        );

        if (! $this->project->update($params)) {
            $this->formErrorMessage = ERROR_MESSAGE_011;
            return false;
        }

        $this->redirectPage  = 'projects/view?id=' . $this->project->id;
        return true;
    }
}

$page = new ProjectEditPage;
$page->process();


