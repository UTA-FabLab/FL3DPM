<?php
/**
 * app/pages/prints/jobticket/script.php
 *
 * Handles the process of creating a print job with a job ticket
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

include(APP_ROOT . 'app/lib/ProjectPrintForm.php');

class PrintJobTicketForm extends \App\ProjectPrintForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'prints_jobticket_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Print - Job Ticket';
        $this->formTitle = 'Print - Job Ticket';
    }

    protected function findRecord($inProjectPrintID = false)
    {

        if (! parent::findRecord($inProjectPrintID)) {
            return false;
        }

        $filaments = new \App\Table\ProjectFilamentsList;

        if (! $filaments->findByProjectID($this->project->id)) {
            return false;
        }

        if ($filaments->count() == 0) {
            return false;
        }

        return true;
    }

    protected function canEdit()
    {
        if ($this->project->status == PROJECT_STATUS_ACTIVE) {
            switch($this->project->activePrintStatus()) {
                case PROJECT_PRINT_STATUS_WAITING:
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
        $this->formVariables['ticket_number']->required = true;
    }

    protected function loadReadOnlyValues()
    {
        parent::loadReadOnlyValues();
        $this->formVariables['ticket_number']->autoFocus = true;
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->projectPrint->id;
        if ($this->formVariables['ticket_number']->value != '') {
            $this->formVariables['ticket_number']->value = $this->projectPrint->job_ticket_number;
        }
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
        //-- make sure there is not another print with that job ticket number

            $print = new \App\Table\ProjectPrintsRecord;

            if ($print->findByJobTicket($this->formVariables['ticket_number']->value)) {
                if ($print->id != $this->projectPrint->id) {
                    $this->formErrorMessage = ERROR_MESSAGE_016;
                    return false;
                }
            }

        //-- Update the Project Print Job Ticket #

            $params = array(
                'status'            => PROJECT_PRINT_STATUS_PRINTING,
                'job_ticket_number' => $this->formVariables['ticket_number']->value
            );

            if (! $this->projectPrint->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Go back to the print view

            $this->redirectPage = 'prints/view?id=' . $this->projectPrint->id;
            return true;
    }
}

$page = new PrintJobTicketForm;
$page->process();

