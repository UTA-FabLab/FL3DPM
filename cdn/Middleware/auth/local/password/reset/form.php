<?php
/**
 * Middleware/auth/local/password/reset/form.php
 *
 * Displays a form to collect user email to reset password
 *
 * @package Middleware
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace Middleware;
?>

<div class="app-form-spacing">
    <div class="row" role="region" aria-label="<?= $this->formTitle ?>">
        <div class="col-6 offset-3">
            <h2><?= $this->formTitle ?></h2>
            <div class="form-shadow-box">
                <p>Forgotten your password? Enter your email address below to reset your password</p>
                <form action="<?= \App\Request::pageURL() ?>" method="post">
                    <?= $this->formVariables['csrf']->render() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label"><b>Email address</b></label>
                        <?= $this->formVariables['email']->renderElement(array('form-control')) ?>
                    </div>


                    <div class="row">
                        <div class="col-sm-10 offset-sm-2">
                            <a href="<?= \Framework\siteURL('auth/logi') ?>" role="button" class="btn btn-outline-dark">Login</a>
                            <button type="submit" class="btn btn-primary float-end">Request Password Reset</button>
                        </div>
                    </div>


                    <?php if (strlen($this->formErrorMessage) > 0) { ?>
                        <div class="col-12 form-error-message">
                            <span class="form-error-message"><?= $this->formErrorMessage ?></span>
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
</div>

