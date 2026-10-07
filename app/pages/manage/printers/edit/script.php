<?php
/**
 * app/pages/manage/printers/edit/script.php
 *
 * Handles the process of editing a printer
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

class PrinterEditPage extends \App\PrinterForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'manage_printer_edit_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Printer - Edit';
        $this->formTitle = 'Printer - Edit';
    }

    protected function getRecordID()
    {
        return $this->printer->id;
    }

    protected function scriptUpdate()
    {
        //-- Make sure we don't already have a printer with that name

            $printerCheck = new \App\Table\PrintersRecord;

            if ($printerCheck->findByName($this->formVariables['printerName']->value)) {
                if ($printerCheck->id != $this->printer->id) {
                    $this->formErrorMessage = ERROR_MESSAGE_007;
                    return false;
                }
            }

            if ($printerCheck->error()) {
                $this->formErrorMessage = ERROR_MESSAGE_009;
                return false;
            }

        //-- Add the new printer

            $params = array(
                'name'    => $this->formVariables['printerName']->value,
                'enabled' => $this->formVariables['enabled']->value,
            );

            if (! $this->printer->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_009;
                return false;
            }

        $this->redirectPage = 'manage/printers/list';
        return true;
    }
}

$page = new PrinterEditPage;
$page->process();


