<?php
/*
  $Id: step_2.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  $files = array('/sitemapsIndex.xml', '/sitemapsCategories.xml', '/sitemapsProducts.xml', 
                 '/sitemapsArticles.xml', '/includes/configure.php');
  
  $directories = array('/admin/images', '/admin/backups', '/cache', 
                       '/cache/admin', '/cache/admin/emails', '/cache/admin/emails/attachments',
                       '/cache/orders_customizations', '/cache/products_attachments', '/cache/products_customizations',
                       '/download', '/images', '/images/articles',
                       '/images/articles/large', '/images/articles/mini', '/images/articles/originals',
                       '/images/articles/product_info', '/images/articles/thumbnails', '/images/products',
                       '/images/products/large', '/images/products/mini', '/images/products/originals',
                       '/images/products/product_info', '/images/products/thumbnails', '/images/categories',
                       '/images/manufacturers', '/includes/work', '/includes/logs', '/templates',
                       '/admin/includes/languages', '/includes/languages', '/install/includes/languages', '/install/templates/main_page/languages');
  
  $root_dir = osc_realpath(dirname(__FILE__) . '/../../../');
?>

<div class="contentBlock">
  <h1><?php echo $osC_Language->get('page_title_pre_installation_check'); ?></h1>

  <p><?php echo $osC_Language->get('text_pre_installation_check'); ?></p>
  
  <div class="infoPane">
  
    <div class="infoPaneContents">
      <div style="float: right;">
	      <table border="0" width="300" cellspacing="0" cellpadding="2">
	        <tr>
	          <th><?php echo $osC_Language->get('box_directory_permissions'); ?></th>
	          <th align="right" width="25"></th>
	        </tr>
	      <?php
	        foreach ($directories as $directory) {
	      ?>
	        <tr>
	          <td><?php echo $directory; ?></td>
	          <td align="right"><img src="images/<?php echo (is_writable($root_dir . $directory) ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
	        </tr>
	      <?php
	        }
	      ?>
	      </table>
     </div>
     <div>
	      <table border="0" width="300" cellspacing="0" cellpadding="2">
	        <tr>
	          <th><?php echo $osC_Language->get('box_server_php_settings'); ?></th>
	          <th align="right"></th>
	          <th align="right" width="25"></th>
	        </tr>
          <tr>
            <td><?php echo $osC_Language->get('box_server_safe_mode'); ?></td>
            <td align="right"><?php echo (((int)ini_get('safe_mode') === 0) ? $osC_Language->get('box_server_off') : $osC_Language->get('box_server_on')); ?></td>
            <td align="right"><img src="images/<?php echo (((int)ini_get('safe_mode') === 0) ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
          </tr>
	        <tr>
	          <td><?php echo $osC_Language->get('box_server_register_globals'); ?></td>
	          <td align="right"><?php echo (((int)ini_get('register_globals') === 0) ? $osC_Language->get('box_server_off') : $osC_Language->get('box_server_on')); ?></td>
	          <td align="right"><img src="images/<?php echo (((int)ini_get('register_globals') === 0) ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
	        </tr>
	        <tr>
	          <td><?php echo $osC_Language->get('box_server_magic_quotes'); ?></td>
	          <td align="right"><?php echo (((int)ini_get('magic_quotes') === 0) ? $osC_Language->get('box_server_off') : $osC_Language->get('box_server_on')); ?></td>
	          <td align="right"><img src="images/<?php echo (((int)ini_get('magic_quotes') === 0) ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
	        </tr>
	        <tr>
	          <td><?php echo $osC_Language->get('box_server_file_uploads'); ?></td>
	          <td align="right"><?php echo (((int)ini_get('file_uploads') === 0) ? $osC_Language->get('box_server_off') : $osC_Language->get('box_server_on')); ?></td>
	          <td align="right"><img src="images/<?php echo (((int)ini_get('file_uploads') === 1) ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
	        </tr>
	        <tr>
	          <td><?php echo $osC_Language->get('box_server_session_auto_start'); ?></td>
	          <td align="right"><?php echo (((int)ini_get('session.auto_start') === 0) ? $osC_Language->get('box_server_off') : $osC_Language->get('box_server_on')); ?></td>
	          <td align="right"><img src="images/<?php echo (((int)ini_get('session.auto_start') === 0) ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
	        </tr>
	        <tr>
	          <td><?php echo $osC_Language->get('box_server_session_use_trans_sid'); ?></td>
	          <td align="right"><?php echo (((int)ini_get('session.use_trans_sid') === 0) ? $osC_Language->get('box_server_off') : $osC_Language->get('box_server_on')); ?></td>
	          <td align="right"><img src="images/<?php echo (((int)ini_get('session.use_trans_sid') === 0) ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
	        </tr>
	      </table>
	      
	      <table border="0" width="300" cellspacing="0" cellpadding="2">
	        <tr>
	          <th><?php echo $osC_Language->get('box_server_php_version'); ?></th>
	          <th align="right"><?php echo phpversion(); ?></th>
	          <th align="right" width="25"><img src="images/<?php echo ((phpversion() >= '5.1.6') ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></th>
	        </tr>
	      </table>
	      
	      <table border="0" width="300" cellspacing="0" cellpadding="2">
	        <tr>
	          <th><b><?php echo $osC_Language->get('box_server_php_extensions'); ?></b></th>
	          <th align="right" width="25"></th>
	        </tr>
	        <tr>
	          <td><?php echo $osC_Language->get('box_server_mysql'); ?></td>
	          <td align="right"><img src="images/<?php echo (extension_loaded('mysql') ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
	        </tr>
	        <tr>
	          <td><?php echo $osC_Language->get('box_server_gd'); ?></td>
	          <td align="right"><img src="images/<?php echo (extension_loaded('gd') ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
	        </tr>
	        <tr>
	          <td><?php echo $osC_Language->get('box_server_curl'); ?></td>
	          <td align="right"><img src="images/<?php echo (extension_loaded('curl') ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
	        </tr>
	        <tr>
	          <td><?php echo $osC_Language->get('box_server_openssl'); ?></td>
	          <td align="right"><img src="images/<?php echo (extension_loaded('openssl') ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
	        </tr>
	      </table>
	      
	      <table border="0" width="300" cellspacing="0" cellpadding="2">
	        <tr>
	          <th><?php echo $osC_Language->get('box_file_permissions'); ?></th>
	          <th align="right" width="25"></th>
	        </tr>
	      <?php
	        foreach ($files as $file) {
	      ?>
	        <tr>
	          <td><?php echo $file; ?></td>
	          <td align="right"><img src="images/<?php echo (is_writable($root_dir . $file) ? 'ok.png' : 'error.png'); ?>" border="0" width="16" height="16"></td>
	        </tr>
	      <?php
	        }
	      ?>
	      </table>
      </div>
      <div class="clear"></div>
    </div>
  </div>
</div>

<p align="right">
  <?php echo '<a href="javascript:void(0);" onclick="javascript: window.location.reload();"><img src="templates/' . $template . '/languages/' . $osC_Language->getCode() . '/images/buttons/retry.gif" border="0" alt="' . $osC_Language->get('image_button_retry') . '" /></a>'; ?>
  <?php echo '<a href="javascript:void(0);" onclick="javascript: history.go(-1);"><img src="templates/' . $template . '/languages/' . $osC_Language->getCode() . '/images/buttons/cancel.gif" border="0" alt="' . $osC_Language->get('image_button_install') . '" /></a>'; ?>
  <?php echo '<a href="index.php?step=3"><img src="templates/' . $template . '/languages/' . $osC_Language->getCode() . '/images/buttons/continue.gif" border="0" alt="' . $osC_Language->get('image_button_install') . '" /></a>'; ?>
</p>