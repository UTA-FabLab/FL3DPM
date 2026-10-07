<?php
/**
 * app/lib/CollegeForm.php
 *
 * Form for adding and editing colleges
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

class CollegeForm extends \App\Form
{
    protected $college;

    public function __construct()
    {
        parent::__construct();
        $this->college = new \App\Table\CollegesRecord;
        $this->defaultCancelPage = 'manage/colleges/list';
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

    protected function findRecord($inCollegeID = false)
    {
       //-- if no project college id passed in retrieve from the query parameters

            if ($inCollegeID === false) {
                $inCollegeID = \App\Request::getQP('id');
                if ($inCollegeID === false) {
                    return false;
                }
            }

       //-- find the colelge by id

            if (! $this->college->findByID($inCollegeID)) {
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

        $this->formVariables['collegeName'] = new \App\Form\Variable\CollegeName;
        $this->formVariables['collegeName']->required = true;
        $this->formVariables['collegeName']->autoFocus = true;

        $this->formVariables['enabled'] = new \MiddleWare\Form\Variable\Enabled;
        $this->formVariables['enabled']->required = true;
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->college->id;
        $this->formVariables['collegeName']->value = $this->college->college;
        $this->formVariables['enabled']->value = $this->college->enabled;
    }

    protected function postValidate()
    {
        return true;
    }

}

