<?php
/**
 * app/includes/login-header.php
 *
 * Instance specific header for login application pages
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
<?php if (! defined('WITHOUT_HEADER')) { ?>
  <header id="header" class="header" role="banner">
    <nav class="navbar navbar-expand-lg">
      <div class="container-fluid">
        <img alt="Logo" class="navbar-brand" src="<?= \Framework\siteURL('assets/img/site-logo.png') ?>" />
        <h1 class="site-title"><?= APP_FULL_NAME ?><?= (\MiddleWare\isProduction() ? '' : ' (dev)') ?></h1>
        <div class="navbar-nav ms-auto">
            <div class="text-end">
                &nbsp;
            </div>
        </div>
      </div>
    </nav>
  </header>

<?php } ?>

