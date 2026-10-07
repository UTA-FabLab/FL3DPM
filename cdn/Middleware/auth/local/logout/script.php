<?php
/**
 * Middleware/auth/local/logout/script.php
 *
 * Logs current user out, and redirects to login page
 *
 * @package Middleware
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace App;

\Framework\NavStack::clear();

\App\Request::$user->forgetUser();

\Framework\redirectIndex();

