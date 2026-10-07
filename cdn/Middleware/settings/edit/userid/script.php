<?php
/**
 * Middlware/settings/edit/userid/script.php
 *
 * Handles the updating of a "userid" config value
 *
 * @package Middleware
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace MiddleWare;

require(MIDDLEWARE_ROOT . 'lib/SettingsForm.php');

class SettingsUserIDForm extends \MiddleWare\SettingsForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'settings_edit_userid_csrf';
        parent::__construct();
        $this->valueType = SETTING_TYPE_USERID;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['value'] = new \Framework\Form\Variable\Select();
        parent::buildFormVariables();
        $this->formVariables['value']->label = '<b>Staff Member</b>';
        $this->formVariables['value']->setInitialOption('-','Select a staff member');
        $this->formVariables['value']->setSelectOptions( \MiddleWare\getActiveStaff(INDEX_BY_USER_ID) );
    }

}


