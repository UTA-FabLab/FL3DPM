<?php
/**
 * Middleware/lib/SettingsForm.php
 *
 * Class to handle setting value editing
 *
 * Class needs to be extended to edit value specific edit (amount, date, string,...)
 *
 * @package MiddleWare
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace MiddleWare;

class SettingsForm extends \App\Form
{
    protected $setting;
    protected $valueType;
    protected $otherFormVariables;

    public function __construct()
    {
        $this->csrfTokenName = 'settings_edit_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Settings - Edit';
        $this->formTitle = 'Settings - Edit';
        $this->cancelPage  = 'manage/settings/list';
        $this->redirectPage = 'manage/settings/list';
        $this->setting = new \MiddleWare\Table\SettingsRecord;
        $this->useBodyContentFunction = true;
    }

    protected function userHasAccess()
    {
        if (! \App\Request::$user->isRealUser()) {
            return false;
        }

        if (\App\Request::$user->hasACL(array(APP_ACL_MANAGE_SETTINGS))) {
            return true;
        }

        return false;
    }

    protected function findRecord($inRecordID=false)
    {
        //-- if no setting id passed in, then try to grab from query parameters

            if ($inRecordID === false) {
                $inRecordID = trim(strval(\App\Request::getQP('id')));
                if ($inRecordID === false) {
                    return false;
                }
            }

        //-- Find the setting record

            $this->setting = new \MiddleWare\Table\SettingsRecord;

            if (! $this->setting->findByID($inRecordID)) {
                return false;
            }

            if ($this->setting->editable != VALUE_YES) {
                return false;
            }

            if ($this->setting->type != $this->valueType) {
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

        //-- value form field needs to be already created by child class
        //-- since we don't know the specific type here
        $this->formVariables['value']->name     = 'value';
        $this->formVariables['value']->id       = 'value';
        $this->formVariables['value']->label    = '<b>Value</b>';
        $this->formVariables['value']->required = true;
    }


    protected function loadWriteValues() 
    {
        $this->formVariables['id']->value = $this->setting->id;
        $this->formVariables['value']->value = $this->setting->value;
        $this->formVariables['value']->autoFocus = true;
    }


    protected function getRecordID()
    {
        return $this->setting->id;
    }


    protected function postValidate()
    {
        return true;
    }


    protected function valueString()
    {
        return $this->formVariables['value']->value;
    }


    protected function scriptUpdate()
    {
        //-- User can only do this is they are the real user (if function exists);

            if (! \App\Request::$user->isRealUser()) {
                $this->formErrorMessage = 'Not allowed to update while impersonating';
                return false;
            }

        //-- Update the setting value

            $params = array(
                'value' => $this->valueString()
            );

            if (! $this->setting->update($params)) {
                $this->formErrorMessage = 'There was an error while updating the setting value';
                return false;
            }
 
        return true;
    }

    protected function renderExtraFields()
    {
        //-- can be extended by child class
    }

    public function bodyContent()
    {
        print '<div style="margin:30px;">' . PHP_EOL;
        print '    <div class="row" style="margin-bottom: 20px;" role="region" aria-label="' . $this->formTitle . '">' . PHP_EOL;
        print '        <h2>Edit ' .  $this->setting->tag_desc . '</h2>' . PHP_EOL;
        print '        <div class="form-shadow-box">' . PHP_EOL;
        print '            <form action="' . \App\Request::pageURL() . '" method="post">' . PHP_EOL;
        $this->formVariables['csrf']->render();
        $this->formVariables['id']->render();
        $this->formVariables['value']->render();
        $this->renderExtraFields();
        print '                <div class="row">' . PHP_EOL;
        print '                    <div class="col-sm-10 offset-sm-2">' . PHP_EOL;
        \App\Render::cancelButton($this->cancelPage);
        \App\Render::submitButton('Update');
        print '                    </div>' . PHP_EOL;
        print '                </div>' . PHP_EOL;
        if (strlen($this->formErrorMessage) > 0) {
            print '                    <div class="col-12" style="padding-top: 10px;">' . PHP_EOL;
            print '                        <span style="color: red"><?= $this->formErrorMessage ?></span>' . PHP_EOL;
            print '                    </div>' . PHP_EOL;
        }
        print '            </form>' . PHP_EOL;
        print '        </div>' . PHP_EOL;
        print '    </div>' . PHP_EOL;
        print '</div>' . PHP_EOL;
    }

}

