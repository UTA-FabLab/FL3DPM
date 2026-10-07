<?php
/**
 * Middleware/auth/local/init/script.php
 *
 * Handles the process of initializing local login process
 *
 * @package Middleware
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

\Framework\NavStack::clear();

\App\Request::$user->forgetUser();

\Framework\redirectPage('auth/login');

