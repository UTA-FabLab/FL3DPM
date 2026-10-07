<?php
/**
 * Middleware/auth/local/login/form.php
 *
 * Displays a form to collect user credentials
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
                <form action="<?= \App\Request::pageURL() ?>" method="post">
                    <?= $this->formVariables['csrf']->render() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label"><b>Email address</b></label>
                        <?= $this->formVariables['email']->renderElement(array('form-control')) ?>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label"><b>Password</b></label>
                        <?= $this->formVariables['password']->renderElement(array('form-control')) ?>
                    </div>


                    <div class="row">
                        <div class="col-sm-10 offset-sm-2">
                            <a href="<?= \Framework\siteURL('auth/password/reset') ?>" role="button" class="btn btn-outline-dark">Reset Password</a>
                            <button type="submit" class="btn btn-primary float-end">Login</button>
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

