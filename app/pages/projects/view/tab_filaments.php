<?php
/**
 * app/pages/projects/view/tab_filaments.php
 *
 * Displays project filaments tab content
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

<div id="filamentsInfo" 
    role="tabpanel" 
    aria-labelledby="filaments-tab"
    class="tab-pane app-view-tab <?= ($this->currentTab == 'filaments' ? 'active' : '') ?>">

    <div class="float-end">
        <?php
            if ($this->project->canManageFilaments()) {
                \App\Render::addButton('projects/filament/add?id=' . $this->project->id, 'Add Filament', 'Filament');
            }
        ?>
    </div>

    <div class="clearfix"></div>

    <div class="app-div-table">
        <div class="row app-div-table-row-header">
            <div class="col-md-1">
                <b>&nbsp;</b>
            </div>
            <div class="col-md-10">
                <b>Filament</b>
            </div>
            <div class="col-md-1">
            </div>
        </div>
        <?php $lastFilament = $this->projectFilaments->count(); ?>
        <?php $this->projectFilaments->rewind(); ?>
        <?php while ($this->projectFilaments->next()) { ?>
            <div class="row app-div-table-row-striped">
                <div class="col-md-1">
                    <?php
                        switch($this->project->activePrintStatus()) {
                            case PROJECT_PRINT_STATUS_WAITING:
                            case PROJECT_PRINT_STATUS_PROBLEM:
                                if ($this->projectFilaments->filament_order > 1) {
                                    \App\Render::upButton(
                                        'projects/filament/reorder?id=' . $this->projectFilaments->id . '&up=1',
                                        'Filament Up ' . $this->projectFilaments->id,
                                        strval($this->projectFilaments->id));
                                }
                                if ($this->projectFilaments->filament_order < $lastFilament) {
                                    \App\Render::downButton(
                                        'projects/filament/reorder?id=' . $this->projectFilaments->id . '&down=1',
                                        'Filament Down ' . $this->projectFilaments->id,
                                        strval($this->projectFilaments->id));
                                }
                                break;
                        }
                    ?>
                </div>
                <div class="col-md-10">
                    <b><?= $this->projectFilaments->name ?></b>
                </div>
                <div class="col-md-1">
                    <?php
                        if ($this->project->canManageFilaments()) {
                            \App\Render::trashButton(
                                'projects/filament/remove?id=' . $this->projectFilaments->id,
                                'Remove Filament' . $this->projectFilaments->id,
                                strval($this->projectFilaments->id));
                        }
                    ?>
                </div>
            </div>
        <?php
            }

            if ($this->projectFilaments->count() == 0) {
                print '<div class="row app-div-table-row-striped">';
                print '<div class="col-md-12">';
                print 'No filaments located';
                print '</div>';
                print '</div>';
            }
        ?>
    </div>

</div> <!-- end filaments tag -->

