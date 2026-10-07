<?php
/**
 * app/pages/projects/view/tab_prints.php
 *
 * Displays project prints tab content
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

<div id="printsInfo" 
    role="tabpanel" 
    aria-labelledby="prints-tab"
    class="tab-pane app-view-tab <?= ($this->currentTab == 'prints' ? 'active' : '') ?>">

    <div class="app-div-table">
        <div class="row app-div-table-row-header">
            <div class="col-md-2">
                <b>Print Status</b>
            </div>
            <div class="col-md-2">
                <b>File Name</b>
            </div>
            <div class="col-md-2">
                <b>File Type</b>
            </div>
            <div class="col-md-1">
                <b>File Size</b>
            </div>
            <div class="col-md-1">
                <b>Wait Ticket #</b>
            </div>
            <div class="col-md-1">
                <b>Print Ticket #</b>
            </div>
            <div class="col-md-2">
                <b>Created</b>
            </div>
            <div class="col-md-1">
                &nbsp;
            </div>
        </div>
        <?php
            $this->projectPrints->rewind();
            while ($this->projectPrints->next()) {
        ?>
                <div class="row app-div-table-row-striped">
                    <div class="col-md-2">
                        <?php
                            \App\Render::textButton(
                                'viewPrint' . $this->projectPrints->id,
                                'prints/view?id=' . $this->projectPrints->id,
                                'btn btn-warning',
                                'View <span class="visually-hidden"> Print ' . $this->projectPrints->id . '</span>');
                        ?>
                        &nbsp;
                        <?= $this->projectPrints->normalize('status') ?>
                    </div>
                    <div class="col-md-2">
                        <?= $this->projectPrints->file_name ?>
                    </div>
                    <div class="col-md-2">
                        <?= $this->projectFiles->normalize('file_type', $this->projectPrints->file_type) ?>
                    </div>
                    <div class="col-md-1">
                        <?= $this->projectFiles->normalize('file_size', $this->projectPrints->file_size) ?>
                    </div>
                    <div class="col-md-1">
                        <?= $this->projectPrints->wait_list_number ?>
                    </div>
                    <div class="col-md-1">
                        <?php
                            if ($this->projectPrints->status == PROJECT_PRINT_STATUS_WAITING) {
                                if ($this->project->status == PROJECT_STATUS_ACTIVE) {
                                    if ($this->projectFilaments->count() > 0) {
                                        \App\Render::textButton(
                                            'viewJob' . $this->projectPrints->id,
                                            'prints/jobticket?id=' . $this->projectPrints->id,
                                            'btn btn-primary',
                                            'Job Ticket <span class="visually-hidden"> ' . $this->projectPrints->id . '</span>');
                                    } else {
                                        \App\Render::textButton(
                                            'needFilament' . $this->project->id,
                                            'projects/view?id=' . $this->project->id . '&tab=filaments',
                                            'btn btn-primary',
                                            'Need Filament <span class="visually-hidden"> ' . $this->project->id . '</span>');
                                    }
                                }
                            } else {
                                print $this->projectPrints->job_ticket_number;
                            }
                        ?>
                    </div>
                    <div class="col-md-2">
                        <?= $this->projectPrints->normalize('created') ?>
                    </div>
                    <div class="col-md-1">
                    </div>
                </div>
        <?php
            }

            if ($this->projectPrints->count() == 0) {
                print '<div class="row app-div-table-row-striped">';
                print '<div class="col-md-12">';
                print 'No prints found';
                print '</div>';
                print '</div>';
            }
        ?>

    </div>

</div> <!-- end prints tag -->

