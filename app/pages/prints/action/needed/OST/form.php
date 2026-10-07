<?php
/**
 * app/pages/prints/action/needed/OST/form.php
 *
 * Displays form to reprint action with required service ticket opened
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

<div style="margin:30px;">
    <div class="row" style="margin-bottom: 20px;" role="region" aria-label="<?= $this->formTitle ?>">
        <div class="col-10 offset-1">
            <h2><?= $this->formTitle ?></h2>

            <div class="form-shadow-box">
                <form action="<?= \App\Request::pageURL() ?>" method="post" enctype="multipart/form-data">

                    <?= $this->formVariables['csrf']->render() ?>
                    <?= $this->formVariables['id']->render() ?>
                    <?= $this->formVariables['project_id']->render(); ?>
                    <?= $this->formVariables['problem']->render(); ?>
                    <?= $this->formVariables['action_needed']->render(); ?>
                    <?= $this->formVariables['resolution']->render(); ?>

                    <div class="row mb-3">
                        <div>
                            <p>Click on the link below to open a support ticket.</p>
                            <p><a id="fabapp_open_service_ticket" href="<?= $this->fabAppServiceTicketURL ?>" target="FABAPP">Open service ticket</a></p>
                            <p>The Update buttons will be enabled once you have clicked on the link.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-10 offset-sm-2">
                            <?= \App\Render::cancelButton($this->cancelPage) ?>

                            <?php if ($this->problemLocked) { ?>
                                <span class="float-end form-error-message">Problem is locked: please have full time staff or a lead check file for errors</button>
                            <?php } else { ?>
                                <?= \App\Render::submitButton('Update & Add New Ticket', inNameID: 'button-update-ticket', inValue: 'button-update-ticket', inDisabled: true) ?>
                                <p class="float-end" style="padding-left: 10px;"></p>
                                <?= \App\Render::submitButton('Update', inNameID: 'button-update', inValue: 'button-update', inDisabled: true) ?>
                            <?php } ?>
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


