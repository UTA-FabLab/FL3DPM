<?php
/**
 * app/pages/projects/add/form.php
 *
 * Displays form to add a project
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

<div class="app-form-spacing"
    <div class="row" role="region" aria-label="<?= $this->formTitle ?>">
        <div class="col-10 offset-1">
            <h2><?= $this->formTitle ?></h2>
            <div class="form-shadow-box">
                <form action="<?= \App\Request::pageURL() ?>" method="post" enctype="multipart/form-data">
                    <?= $this->formVariables['csrf']->render() ?>


                    <?= $this->formVariables['is_affiliated']->render(); ?>
                    <?= $this->formVariables['college_id']->render(); ?>
                    <?= $this->formVariables['customer_id']->render(); ?>
                    <?= $this->formVariables['email']->render(); ?>
                    <?= $this->formVariables['first_name']->render(); ?>
                    <?= $this->formVariables['last_name']->render(); ?>
                    <?= $this->formVariables['category']->render(); ?>
                    <?= $this->formVariables['cancel_date']->render(); ?>
                    <?= $this->formVariables['printer_id']->render(); ?>
                <div class="row mb-3">
                    <label for="ticket_number" class="col-sm-2 col-form-label"><b>Ticket #</b></label>
                    <div class="col-sm-2">
                        <input type="text" id="ticket_number" name="ticket_number" class="form-control"  value="<?= $this->formVariables['ticket_number']->value ?>">
                    </div>
                    <div class="col-sm-3">
                        <fieldset>
                            <legend><span class="visually-hidden">Ticket Type</span></legend>
                        <input type="radio"
                            id="ticket_type0"
                            name="ticket_type"
                            class="form-check-input"
                            required
                            <?= ($this->formVariables['ticket_type']->value != 1 ? 'checked' : '') ?>
                            value="ticket_type0">
                        <label for="ticket_type0" class="form-check-label">Wait List #</label>
                        &nbsp; &nbsp;
                        <input type="radio"
                            id="ticket_type1"
                            name="ticket_type"
                            class="form-check-input
                            form-check-input"
                            required
                            <?= ($this->formVariables['ticket_type']->value == 1 ? 'checked' : '') ?>
                            value="ticket_type1">
                        <label for="ticket_type1" class="form-check-label">Job #</label>
                        </fieldset>
                    </div>
                </div>

                    <?= $this->formVariables['filament_id']->render(); ?>
                    <?= $this->formVariables['stl_file']->render(); ?>
                    <?= $this->formVariables['gcode_file']->render(); ?>



                    <div class="row">
                        <div class="col-sm-10 offset-sm-2">
                            <?= \App\Render::cancelButton($this->cancelPage) ?>
                            <?= \App\Render::submitButton('Add Project') ?>
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

