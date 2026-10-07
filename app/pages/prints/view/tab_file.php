<?php
/**
 * app/pages/projects/view/tab_file.php
 *
 * Displays project file tab content
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

<div id="fileInfo" 
    role="tabpanel" 
    aria-labelledby="file-tab"
    class="tab-pane app-view-tab <?= ($this->currentTab == 'file' ? 'active' : '') ?>">

    <div class="app-div-table app-view-tab">

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end align-items-center">
                File Name:
            </div>
            <div class="col-md-11 align-items-center">
                <?= $this->projectPrintFile->file_name ?>
                &nbsp; &nbsp;
                <?php 
                    \App\Render::downloadButton(
                        \Framework\siteURL('projects/file/download?id=' . $this->projectPrintFile->id),
                        'File Download ' . $this->projectPrintFile->id,
                        'fileDownload' . $this->projectPrintFile->id);
                ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                File Type:
            </div>
            <div class="col-md-11">
                <?= $this->projectPrintFile->normalize('file_type') ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                File Size:
            </div>
            <div class="col-md-11">
                <?= $this->projectPrintFile->normalize('file_size') ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                Uploaded:
            </div>
            <div class="col-md-11">
                <?= $this->projectPrintFile->normalize('uploaded') ?>
            </div>
        </div>

    </div>

</div> <!-- end file tag -->

