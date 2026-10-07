<?php
/**
 * Middleware/acl/view/display.php
 *
 * Display user ACL
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

    <div class="clearfix" style="padding-bottom: 20px">
        <div class="float-start">
            <?php
                \App\Render::goBackButton();
            ?>
        </div>
        <div class="float-start" style="padding-left: 20px;"><h3>Access for <?= $this->staffIdentity->last_name . ', ' . $this->staffIdentity->first_name ?></h3></div>
        <div class="float-end">
            <?php
               \App\Render::iconButton(
                    'editButton',
                    'manage/acl/edit?id='. $this->staffIdentity->id,
                    'btn',
                    ICON_EDIT,
                    'Edit Access',
                    'style="font-size: 2em;"');

                \App\Render::iconButton(
                    'trashButton',
                    'manage/acl/remove?id='. $this->staffIdentity->id,
                    'btn',
                    ICON_TRASH,
                    'Remove Access',
                    'style="font-size: 2em;"');
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-10 offset-1">
            <?php $this->viewDisplayACL(APP_ACL_TREE); ?>
        </div>
    </div>

    <BR>

    <div class="row">
        <div class="col-10 offset-1">
            <i class="<?= ICON_HAS_ACL ?>"></i> Explicitly Granted Access
            &nbsp; &nbsp;
            <i class="<?= ICON_SUB_ACL ?>"></i> Implied Access
            &nbsp; &nbsp;
            <i class="<?= ICON_NO_ACL ?>"></i> No Access
        </div>
    </div>

</div>


