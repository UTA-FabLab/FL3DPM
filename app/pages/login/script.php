<?php
/**
 * app/pages/login/script.php
 *
 * Constructs the login notice page for the user prior to authentication
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

class AppLoginPage extends \App\LoginPage
{
    public function __construct()
    {
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Login Notice';
    }

    protected function userHasAccess()
    {
        return true;
    }
}

$page = new AppLoginPage;

$page->render();

