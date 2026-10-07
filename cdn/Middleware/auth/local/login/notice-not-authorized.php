<?php
/**
 * Middleware/auth/local/login/notice-not-authorized.php
 *
 * Handles the display of a page indicating that the user is not authorized to login
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
?>

<div role="region" aria-label="Authorization Error Page" style="margin-top: 50px;">
    <div class="row justify-content-center">
        <div class="col-12 d-flex justify-content-center">
            <h1>We were unable to authorize your login.</h1>
        </div>
    </div>

    <div class="row justify-content-center" style="margin-top: 30px;">
        <div class="col-12 d-flex justify-content-center">
            <p style="font-size: 1.5em;">We apologize for the inconvenience.</p>
        </div>
    </div>

    <div class="d-flex justify-content-center" style="margin-top: 30px;">
        <div class="col-12 d-flex justify-content-center">
            <p style="font-size: 1.5em;">Please click the button below to attempt to login again</p>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        <p class="text-center"><?= \App\Render::clearLoginButton() ?></p>
    </div>

    <?php if ($this->debug) { ?>
        <p class="text-center"><span style="font-size: 25px;"><?= $this->errMsg ?></p>
    <?php } ?>

</div>

