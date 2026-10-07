<?php
/**
 * Middleware/acl/edit/form.php
 *
 * Displays form for editing user ACL
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

<div style="margin:30px;">
    <div class="row" style="margin-bottom: 20px;" role="region" aria-label="<?= $this->formTitle ?>">
        <h2>Edit Access for <?= $this->staffIdentity->last_name . ', ' . $this->staffIdentity->first_name  ?></h2>
        <div class="form-shadow-box">
            <form action="<?= \App\Request::pageURL() ?>" method="post">

                <?= $this->formVariables['csrf']->render() ?>

                <?= $this->formVariables['id']->render(); ?>

                <div class="row mb-3">
                    <?php $this->editRenderACLFormVariables(APP_ACL_TREE); ?>
                </div>

                <div class="row">
                    <div class="col-sm-10 offset-sm-2">
                        <?= \App\Render::cancelButton($this->cancelPage) ?>
                        <?= \App\Render::submitButton('Update') ?>
                    </div>
                </div>

                <?php if (strlen($this->formErrorMessage) > 0) { ?>
                    <div class="col-12" style="padding-top: 10px;">
                        <span style="color: red"><?= $this->formErrorMessage ?></span>
                    </div>
                <?php } ?>
            </form>
        </div>
    </div>
</div>

