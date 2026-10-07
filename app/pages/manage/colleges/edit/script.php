<?php
/**
 * app/pages/manage/colleges/edit/script.php
 *
 * Handles the process of editing a college
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

include(APP_ROOT . 'app/lib/CollegeForm.php');

class CollegeEditPage extends \App\CollegeForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'manage_college_edit_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'College - Edit';
        $this->formTitle = 'College - Edit';
        $this->cancelPage = $this->defaultCancelPage;
    }

    protected function getRecordID()
    {
        return $this->college->id;
    }

    protected function scriptUpdate()
    {
        //-- Make sure we don't already have a college with that name

            $college = new \App\Table\CollegesRecord;

            if ($college->findByCollege($this->formVariables['collegeName']->value)) {
                $this->formErrorMessage = ERROR_MESSAGE_001;
                return false;
            }

            if ($college->error()) {
                $this->formErrorMessage = ERROR_MESSAGE_003;
                return false;
            }

        //-- Add the new college

            $params = array(
                'college' => $this->formVariables['collegeName']->value,
                'enabled' => $this->formVariables['enabled']->value,
            );

            if (! $this->college->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_003;
                return false;
            }

        $this->redirectPage = 'manage/colleges/list';
        return true;
    }
}

$page = new CollegeEditPage;
$page->process();


