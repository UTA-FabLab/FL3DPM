<?php
/**
 * app/pages/projects/view/display.php
 *
 * Displays information about a project
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

<div class="app-view-container">
    <div class="row" role="region" aria-label="View Project">
        <div class="col-12">
            <div class="clearfix app-view-title">
                <div class="float-start">
                    <?php \App\Render::goBackButton(); ?>
                </div>
                <div class="float-start app-view-title-text">
                    <h3>Project: <?= $this->project->normalize('id') ?></h3>
                </div>
            </div>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($this->currentTab == 'status' ? 'active' : '') ?>" id="status-tab" data-bs-toggle="tab" data-bs-target="#statusInfo" type="button" role="tab" aria-controls="statusInfo" aria-selected="<?= ($this->currentTab == 'status' ? 'true' : 'false') ?>">Status</button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($this->currentTab == 'files' ? 'active' : '') ?>" id="files-tab" data-bs-toggle="tab" data-bs-target="#filesInfo" type="button" role="tab" aria-controls="filesInfo" aria-selected="<?= ($this->currentTab == 'files' ? 'true' : 'false') ?>">Files</button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($this->currentTab == 'filaments' ? 'active' : '') ?>" id="filaments-tab" data-bs-toggle="tab" data-bs-target="#filamentsInfo" type="button" role="tab" aria-controls="filamentsInfo" aria-selected="<?= ($this->currentTab == 'filaments' ? 'true' : 'false') ?>">Filaments<?= ($this->projectFilaments->count() == 0 ? ' <i class="' . ICON_FLAG . '"></i>' : '') ?></button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($this->currentTab == 'prints' ? 'active' : '') ?>" id="prints-tab" data-bs-toggle="tab" data-bs-target="#printsInfo" type="button" role="tab" aria-controls="printsInfo" aria-selected="<?= ($this->currentTab == 'prints' ? 'true' : 'false') ?>">Prints</button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($this->currentTab == 'notes' ? 'active' : '') ?>" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notesInfo" type="button" role="tab" aria-controls="notesInfo" aria-selected="<?= ($this->currentTab == 'notes' ? 'true' : 'false') ?>">Notes<?= ($this->projectNotes->count() > 0 ? ' <i class="' . ICON_FLAG . '"></i>' : '') ?></button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($this->currentTab == 'history' ? 'active' : '') ?>" id="history-tab" data-bs-toggle="tab" data-bs-target="#historyInfo" type="button" role="tab" aria-controls="historyInfo" aria-selected="<?= ($this->currentTab == 'history' ? 'true' : 'false') ?>">History</button>
                </li>

            </ul>

            <!-- Tab panes -->
            <div class="tab-content">

                <?php
                    require(dirname(__FILE__) . '/tab_status.php'); 
                    require(dirname(__FILE__) . '/tab_files.php');
                    require(dirname(__FILE__) . '/tab_filaments.php');
                    require(dirname(__FILE__) . '/tab_prints.php');
                    require(dirname(__FILE__) . '/tab_notes.php');
                    require(dirname(__FILE__) . '/tab_history.php');
                ?>

            </div>

        </div>
    </div>
</div>

