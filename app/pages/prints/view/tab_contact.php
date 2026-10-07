<?php
/**
 * app/pages/projects/view/tab_contact.php
 *
 * Displays project print contact tab content
 *
 * @package App
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);
?>

<div id="contactInfo"
    role="tabpanel"
    aria-labelledby="contact-tab"
    class="tab-pane app-view-tab <?= ($this->currentTab == 'contact' ? 'active' : '') ?>">

    <div class="app-div-table app-view-tab">
        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                Email:
            </div>
            <div class="col-md-11">
                <?= $this->project->email ?>
            </div>
        </div>
        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                Last Name:
            </div>
            <div class="col-md-11">
                <?= $this->project->last_name ?>
            </div>
        </div>
        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                First Name:
            </div>
            <div class="col-md-11">
                <?= $this->project->first_name ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                <?= APP_UNIVERSITY_SHORT_NAME ?> ID:
            </div>
            <div class="col-md-11">
                <span id="customer_id_hidden">******** <span id="customer_id" style="display: none"><?= $this->project->customer_id ?></span></span>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                Created:
            </div>
            <div class="col-md-11">
                <?= $this->project->normalize('created') ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                Cancel Date:
            </div>
            <div class="col-md-11">
                <?= $this->project->normalize('cancel_date') ?>
            </div>
        </div>

    </div>

</div> <!-- end contact tag -->

