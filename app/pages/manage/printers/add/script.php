<?php
/**
 * app/pages/manage/printers/add/script.php
 *
 * Handles the process of adding a new printer
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

include(APP_ROOT . 'app/lib/PrinterForm.php');

class PrinterAddPage extends \App\PrinterForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'manage_printer_add_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Printer - Add';
        $this->formTitle = 'Printer - Add';
        $this->postCollectID = false;
        $this->useTokenRecordID = false; 
    }

    protected function findRecord($inRecordID=false)
    {
        return true;
    }

    protected function canEdit()
    {
        return true;
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();
        $this->formVariables['id']->readOnly = true;
    }

    protected function postValidate()
    {
        return true;
    }

    protected function scriptUpdate()
    {
        //-- Make sure we don't already have a printer with that name

            $printer = new \App\Table\PrintersRecord;

            if ($printer->findByName($this->formVariables['printerName']->value)) {
                $this->formErrorMessage = ERROR_MESSAGE_007;
                return false;
            }

            if ($printer->error()) {
                $this->formErrorMessage = ERROR_MESSAGE_008;
                return false;
            }

        //-- Add the new printer

            $params = array(
                'name'    => $this->formVariables['printerName']->value,
                'enabled' => $this->formVariables['enabled']->value,
            );

            if (! $printer->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_008;
                return false;
            }

        $this->redirectPage = 'manage/printers/list';
        return true;
    }
}

$page = new PrinterAddPage;
$page->process();

