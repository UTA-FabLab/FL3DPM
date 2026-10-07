<?php
/**
 * app/pages/login/display.php
 *
 * Display a login notice to the user before authentication
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
?>

<div class="login-page">
    <div class="row justify-content-center">
        <div class="col-12">
            <p class="text-center login-page-notice">This application is for <?= APP_ENTITY ?> staff only.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-6">
            <div class="border login-banner">
                <span class="login-banner">
                    <?php include(APP_ROOT . 'app/instance/loginNotice.php'); ?>
                </span>
            </div>
        </div>
    </div>


    <div class="d-flex justify-content-center login-button-container">
        <p class="text-center"><?= \App\Render::loginButton() ?></p>
    </div>
</div>
