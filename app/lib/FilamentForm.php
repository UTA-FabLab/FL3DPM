<?php
/**
 * app/lib/FilamentForm.php
 *
 * Handles the process of editing/adding a filament
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

class FilamentForm extends \App\Form
{
    protected $filament;

    public function __construct()
    {
        parent::__construct();
        $this->filament = new \App\Table\FilamentsRecord;
        $this->defaultCancelPage = 'manage/filaments/list';
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

    protected function findRecord($inFilamentID = false)
    {
       //-- if no project filament id passed in retrieve from the query parameters

            if ($inFilamentID === false) {
                $inFilamentID = \App\Request::getQP('id');
                if ($inFilamentID === false) {
                    return false;
                }
            }

       //-- find the filament by id

            if (! $this->filament->findByID($inFilamentID)) {
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

        $this->formVariables['filamentName'] = new \App\Form\Variable\FilamentName;
        $this->formVariables['filamentName']->required = true;
        $this->formVariables['filamentName']->autoFocus = true;

        $this->formVariables['enabled'] = new \MiddleWare\Form\Variable\Enabled;
        $this->formVariables['enabled']->required = true;
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->filament->id;
        $this->formVariables['filamentName']->value = $this->filament->name;
        $this->formVariables['enabled']->value = $this->filament->enabled;
    }

    protected function postValidate()
    {
        return true;
    }

}

