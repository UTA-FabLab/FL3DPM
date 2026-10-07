<?php
/**
 * Middleware/acl/add/form.php
 *
 * Displays form for selecting user
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
        <div class="col-10 offset-1">
            <h2><?= $this->formTitle ?></h2>
            <div class="form-shadow-box">
                <form action="<?= \App\Request::pageURL() ?>" method="post">
                    <?= $this->formVariables['csrf']->render() ?>

                    <?= $this->formVariables['id']->render() ?>

                    <div class="row">
                        <div class="col-sm-10 offset-sm-2">
                            <?= \App\Render::cancelButton('manage/acl/list') ?>
                            <?= \App\Render::submitButton('Update') ?>
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

