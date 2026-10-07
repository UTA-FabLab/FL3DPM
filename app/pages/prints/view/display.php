<?php
/**
 * app/pages/prints/view/display.php
 *
 * Displays information about a print
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
    <div class="row" role="region" aria-label="View Project Print">
        <div class="col-12">
            <div class="clearfix app-view-title">
                <div class="float-start">
                    <?php \App\Render::goBackButton(); ?>
                </div>
                <div class="float-start app-view-title-text">
                    <h3>Project Print: <?= $this->project->normalize('id') ?></h3>
                </div>
            </div>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($this->currentTab == 'status' ? 'active' : '') ?>" id="status-tab" data-bs-toggle="tab" data-bs-target="#statusInfo" type="button" role="tab" aria-controls="statusInfo" aria-selected="<?= ($this->currentTab == 'status' ? 'true' : 'false') ?>">Status</button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($this->currentTab == 'contact' ? 'active' : '') ?>" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contactInfo" type="button" role="tab" aria-controls="contactInfo" aria-selected="<?= ($this->currentTab == 'contact' ? 'true' : 'false') ?>">Contact</button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($this->currentTab == 'file' ? 'active' : '') ?>" id="file-tab" data-bs-toggle="tab" data-bs-target="#fileInfo" type="button" role="tab" aria-controls="fileInfo" aria-selected="<?= ($this->currentTab == 'file' ? 'true' : 'false') ?>">File</button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($this->currentTab == 'filaments' ? 'active' : '') ?>" id="filaments-tab" data-bs-toggle="tab" data-bs-target="#filamentsInfo" type="button" role="tab" aria-controls="filamentsInfo" aria-selected="<?= ($this->currentTab == 'filaments' ? 'true' : 'false') ?>">Filaments</button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($this->currentTab == 'notes' ? 'active' : '') ?>" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notesInfo" type="button" role="tab" aria-controls="notesInfo" aria-selected="<?= ($this->currentTab == 'notes' ? 'true' : 'false') ?>">Notes<?= ($this->projectNotes->count() > 0 ? ' <i class="' . ICON_FLAG . '"></i>' : '') ?></button>
                </li>

                <?php if ($this->projectPrintProblems->count() > 0) { ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= ($this->currentTab == 'problems' ? 'active' : '') ?>" id="problems-tab" data-bs-toggle="tab" data-bs-target="#problemsInfo" type="button" role="tab" aria-controls="problemsInfo" aria-selected="<?= ($this->currentTab == 'problems' ? 'true' : 'false') ?>">Problems</button>
                    </li>
                <?php } ?>

            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <?php
                    require(dirname(__FILE__) . '/tab_status.php'); 
                    require(dirname(__FILE__) . '/tab_contact.php'); 
                    require(dirname(__FILE__) . '/tab_file.php'); 
                    require(dirname(__FILE__) . '/tab_filaments.php'); 
                    require(dirname(__FILE__) . '/tab_notes.php'); 
                    if ($this->projectPrintProblems->count() > 0) {
                        require(dirname(__FILE__) . '/tab_problems.php'); 
                    }
                ?>
            </div>

        </div>
    </div>
</div>
