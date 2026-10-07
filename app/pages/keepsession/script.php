<?php
/**
 * app/pages/keepsession/script.php
 *
 * Used by javascript trigger to reset session timeout.
 * This is needed for WCAG 2.1 compliance
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

print '[session timeout reset]';

