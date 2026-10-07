<?php
/**
 * app/pages/prints/jobticket/form.php
 *
 * Displays form to set a job ticket # for a print
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

<div class="app-form-spacing">
    <div class="row" role="region" aria-label="<?= $this->formTitle ?>">
        <div class="col-10 offset-1">
            <h2><?= $this->formTitle ?></h2>

            <div class="form-shadow-box">
                <form action="<?= \App\Request::pageURL() ?>" method="post" enctype="multipart/form-data">
                    <?= $this->formVariables['csrf']->render() ?>
                    <?= $this->formVariables['id']->render() ?>

                    <?= $this->formVariables['project_id']->render(); ?>
                    <?= $this->formVariables['ticket_number']->render(); ?>

                    <div class="row">
                        <div class="col-sm-10 offset-sm-2">
                            <?= \App\Render::cancelButton($this->cancelPage) ?>
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

