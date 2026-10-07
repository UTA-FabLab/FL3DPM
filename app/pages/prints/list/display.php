<?php
/**
 * app/pages/prints/list/display.php
 *
 * Displays a page to view the list of projects that are in an active state
 *
 * @package App
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

switch($this->viewMode) {

    case VIEW_PRINTS_COMBINED:
        require(dirname(__FILE__) . '/display-combined.php');
        break;

    case VIEW_PRINTS_SEPARATE:
    default:
        require(dirname(__FILE__) . '/display-separate.php');
        break;

}


