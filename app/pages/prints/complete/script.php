<?php
/**
 * app/pages/prints/complete/script.php
 *
 * Handles the process of completing a print job
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

class PrintCompleteForm extends \App\ProjectPrintForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'prints_complete_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Print - Complete';
        $this->formTitle = 'Print - Complete';
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

        $this->formVariables['location'] = new \App\Form\Variable\PrintLocation;
        $this->formVariables['location']->loadSelectOptions();
    }

    protected function loadReadOnlyValues()
    {
        parent::loadReadOnlyValues();
        $this->formVariables['project_id']->value = $this->project->normalize('id');
        $this->formVariables['location']->autoFocus = true;
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

    protected function scriptUpdate()
    {
        //-- Attempt to get a lock

            $record = \App\Request::$db->getRowLock('project_prints','id', $this->projectPrint->id);

            if ($record === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            if ($record['status'] != PROJECT_PRINT_STATUS_PRINTING) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Attempt to send the email

            if ($this->formVariables['location']->value == LOCATION_PICKUP) {
                if (! \App\Mail(MAIL_TYPE_PRINT_PICKEDUP, $this->project->email, $this->project->normalize('id'))) {
                    $this->formErrorMessage = ERROR_MESSAGE_022;
                    return false;
                }
            } else {
                if (! \App\Mail(MAIL_TYPE_PRINT_STORED, $this->project->email, $this->project->normalize('id'))) {
                    $this->formErrorMessage = ERROR_MESSAGE_022;
                    return false;
                }
            }

        //-- Update the Project Print job to complete

            $params = array(
                'status' => PROJECT_PRINT_STATUS_COMPLETE
            );

            if (! $this->projectPrint->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- update the status of the project

            $params = array(
                'status' => PROJECT_STATUS_CLOSED,
            );

            if (! $this->project->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Go back to the print view

            $this->redirectPage = 'prints/view?id=' . $this->projectPrint->id;
            return true;
    }
}

$form = new PrintCompleteForm();
$form->process();

