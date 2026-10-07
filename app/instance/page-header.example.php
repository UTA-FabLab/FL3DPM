<?php
/**
 * app/includes/header.php
 *
 * Instance specific header for application pages
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

$userDetails = \App\Request::$user->userDetails();

?>

<?php if (! defined('WITHOUT_HEADER')) { ?>

  <header id="header" class="header" role="banner">
    <nav class="navbar navbar-expand-lg">
      <div class="container-fluid">
        <img alt="Logo" class="navbar-brand" src="<?= \Framework\siteURL('assets/img/site-logo.png') ?>" />
        <h1 class="site-title"><?= APP_FULL_NAME ?><?= (\MiddleWare\isProduction() ? '' : ' (dev)') ?></h1>
        <div class="navbar-nav ms-auto">
            <div class="text-end">
                <?= \App\Render::homeButton() ?>
                <?= \App\Render::logoutButton() ?>
            </div>
        </div>
      </div>
    </nav>


    <nav id="header-navbar" class="navbar navbar-expand-lg">
        <div id="header-navbar-div" class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link" href="<?= \Framework\siteURL('prints/list') ?>">Prints</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= \Framework\siteURL('projects/list') ?>">Projects</a>
                    </li>


                    <?php if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS, APP_ACL_MANAGE_ACL))) { ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="manageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Manage</a>
                            <ul class="dropdown-menu" aria-labelledby="manageDropdown">
                                <?php if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS))) { ?>
                                    <li><a class="dropdown-item" href="<?= \Framework\siteURL('manage/colleges/list') ?>" alt="Manage Colleges">Colleges</a></li>
                                    <li><a class="dropdown-item" href="<?= \Framework\siteURL('manage/filaments/list') ?>" alt="Manage Filaments">Filaments</a></li>
                                    <li><a class="dropdown-item" href="<?= \Framework\siteURL('manage/printers/list') ?>" alt="Manage Printers">Printers</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php } ?>

                                <?php if (\App\Request::$user->hasACL(array(APP_ACL_MANAGE_ACL))) { ?>
                                    <li><a class="dropdown-item" href="<?= \Framework\siteURL('manage/acl/list') ?>" alt="Manage Access">Access</a></li>
                                <?php } ?>

                                <?php if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS))) { ?>
                                    <li><a class="dropdown-item" href="<?= \Framework\siteURL('manage/settings/list') ?>" alt="Manage Settings">Settings</a></li>
                                <?php } ?>
                                <?php if (\App\Request::$user->hasACL(array(APP_ACL_MANAGE_ACL))) { ?>
                                    <li><a class="dropdown-item" href="<?= \Framework\siteURL('manage/accounts/list') ?>" alt="Manage User Accounts">User Accounts</a></li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">User</a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?= \Framework\siteURL('user/password') ?>" alt="Password">Password</a></li>
                            <li><a class="dropdown-item" href="<?= \Framework\siteURL('user/preferences') ?>" alt="Preferences">Preferences</a></li>
                        </ul>
                    </li>

                </ul>
            </div>
            <div><?= $userDetails['first_name'] . ' ' . $userDetails['last_name'] ?></div>
        </div>
    </nav>

  </header>

<?php } ?>

