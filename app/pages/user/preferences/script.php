<?php
/**
 * app/pages/user/preferences/script.php
 *
 * Handles the process of updating user preferences
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

class UserPreferencesPage extends \App\Form
{
    protected $userPreferences;

    public function __construct()
    {
        $this->csrfTokenName = 'user_preferences_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'User Preferences';
        $this->formTitle = 'User Preferences';
        $this->userPreferences = new \MiddleWare\Table\UserPreferencesRecord;
        $this->postCollectID = false; // we are using current user so need to collect idField
        $this->postCallFindRecord = true; // but we still need to find the userPreferences record
        $this->useTokenRecordID = false; 
    }

    protected function userHasAccess()
    {
        return true;
    }

    protected function findRecord($inRecordID=false)
    {
        $this->userPreferences = new \MiddleWare\Table\UserPreferencesRecord;
        $this->userPreferences->debug();

        if (! $this->userPreferences->findByID(\App\Request::$user->userID)) {
            if ($this->userPreferences->error) {
                return false;
            }
        }

        return true;
    }

    protected function canEdit()
    {
        return true;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['css_style'] = new \MiddleWare\Form\Variable\CSSStyle;
        $this->formVariables['css_style']->loadSelectOptions();
        $this->formVariables['css_style']->autoFocus = true;
    }

    protected function loadWriteValues()
    {
        if (! $this->userPreferences->empty()) {
            $this->formVariables['css_style']->value = $this->userPreferences->css_style;
        }
    }

    protected function postValidate()
    {
        return true;
    }

    protected function scriptUpdate()
    {
        $params = array(
            'user_id'   => \App\Request::$user->userID,
            'css_style' => $this->formVariables['css_style']->value,
        );

        if ($this->userPreferences->empty()) {
            if (! $this->userPreferences->insert($params)) {
                $this->formErrorMessage = 'There was error while updating the preferences';
                return false;
            }
        } else {
            if (! $this->userPreferences->update($params)) {
                $this->formErrorMessage = 'There was error while updating the preferences';
                return false;
            }
        }

        $_SESSION[APP_SESSION_KEY]['reload_session'] = 1;
        return true;
    }
}

$page = new UserPreferencesPage;
$page->process();

