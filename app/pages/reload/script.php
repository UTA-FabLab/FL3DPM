<?php
/**
 * app/pages/reload/script.php
 *
 * Forces a reload of the user's session information
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

$_SESSION[APP_SESSION_KEY]['reload_session'] = 1;

\Framework\redirectIndex();

