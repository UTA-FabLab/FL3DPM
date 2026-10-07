<?php
/**
 * app/pages/manage/colleges/add/script.php
 *
 * Handles the process of adding a new college
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

class CollegeAddPage extends \App\CollegeForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'manage_college_add_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'College - Add';
        $this->formTitle = 'College - Add';
        $this->postCollectID = false;
        $this->cancelPage = $this->defaultCancelPage;
        $this->useTokenRecordID = false; 
    }

    protected function findRecord($inRecordID=false)
    {
        return true;
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();

        $this->formVariables['id']->readOnly = true;
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
                $this->formErrorMessage = ERROR_MESSAGE_002;
                return false;
            }

        //-- Add the new college

            $params = array(
                'college' => $this->formVariables['collegeName']->value,
                'enabled' => $this->formVariables['enabled']->value,
            );

            if (! $college->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_002;
                return false;
            }

        $this->redirectPage = 'manage/colleges/list';
        return true;
    }
}

$page = new CollegeAddPage;
$page->process();

