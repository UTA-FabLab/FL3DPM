<?php
/**
 * Middleware/auth/msoauth/clear/script.php
 *
 * Handles the process of clearing msoauth information 
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

$auth = new \MiddleWare\Auth;

$auth->clear();

\Framework\redirectIndex();

