<?php
/**
 * Middleware/auth/local/password/redeem/form.php
 *
 * Displays a form to allow user to reset password
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
                    <?= $this->formVariables['id']->render() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label"><b>Email address</b></label>
                        <?= $this->formVariables['email']->renderElement(array('form-control')) ?>
                        <?php
                            if (! $this->formVariables['email']->valid) {
                                $this->formVariables['email']->renderErr('invalid-feedback');
                            } 
                        ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label"><b>Password</b></label>
                        <?= $this->formVariables['password']->renderElement(array('form-control')) ?>
                        <?php
                            if (! $this->formVariables['password']->valid) {
                                $this->formVariables['password']->renderErr('invalid-feedback');
                            } 
                        ?>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label"><b>Confirm Password</b></label>
                        <?= $this->formVariables['confirm_password']->renderElement(array('form-control')) ?>
                        <?php
                            if (! $this->formVariables['confirm_password']->valid) {
                                $this->formVariables['confirm_password']->renderErr('invalid-feedback');
                            } 
                        ?>
                    </div>

                    <div class="row">
                        <div class="col-sm-10 offset-sm-2">
                            <a href="<?= \Framework\siteURL('auth/login') ?>" role="button" class="btn btn-outline-dark">Login</a>
                            <button type="submit" class="btn btn-primary float-end">Update Password</button>
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

