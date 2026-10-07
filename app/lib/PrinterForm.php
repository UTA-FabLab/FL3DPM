<?php
/**
 * app/lib/PrinterForm.php
 *
 * Handles the process of editing/adding a printer
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

class PrinterForm extends \App\Form
{
    protected $printer;

    public function __construct()
    {
        parent::__construct();
        $this->printer = new \App\Table\PrintersRecord;
        $this->defaultCancelPage = 'manage/printers/list';
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

        if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS))) {
            return true;
        }

        return false;
    }

    protected function findRecord($inPrinterID = false)
    {
       //-- if no printer id passed in retrieve from the query parameters

            if ($inPrinterID === false) {
                $inPrinterID = \App\Request::getQP('id');
                if ($inPrinterID === false) {
                    return false;
                }
            }

       //-- find the printer by id

            if (! $this->printer->findByID($inPrinterID)) {
                return false;
            }

        return true;
    }

    protected function canEdit()
    {
        return true;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['id'] = new \MiddleWare\Form\Variable\RecordID;
        $this->formVariables['id']->required = true;

        $this->formVariables['printerName'] = new \App\Form\Variable\PrinterName;
        $this->formVariables['printerName']->required = true;
        $this->formVariables['printerName']->autoFocus = true;

        $this->formVariables['enabled'] = new \MiddleWare\Form\Variable\Enabled;
        $this->formVariables['enabled']->required = true;
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->printer->id;
        $this->formVariables['printerName']->value = $this->printer->name;
        $this->formVariables['enabled']->value = $this->printer->enabled;
    }

    protected function postValidate()
    {
        return true;
    }

}

