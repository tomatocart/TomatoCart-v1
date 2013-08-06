<?php
/*
  $Id: step_6.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  define('DB_TABLE_PREFIX', $_POST['DB_TABLE_PREFIX']);
  include('../includes/database_tables.php');

  $osC_Database = osC_Database::connect($_POST['DB_SERVER'], $_POST['DB_SERVER_USERNAME'], $_POST['DB_SERVER_PASSWORD'], $_POST['DB_DATABASE_CLASS']);
  $osC_Database->selectDatabase($_POST['DB_DATABASE']);

  //configuration
  $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
  $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
  $Qupdate->bindValue(':configuration_value', $_POST['CFG_STORE_NAME']);
  $Qupdate->bindValue(':configuration_key', 'STORE_NAME');
  $Qupdate->execute();

  $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
  $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
  $Qupdate->bindValue(':configuration_value', $_POST['CFG_STORE_OWNER_NAME']);
  $Qupdate->bindValue(':configuration_key', 'STORE_OWNER');
  $Qupdate->execute();

  $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
  $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
  $Qupdate->bindValue(':configuration_value', $_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']);
  $Qupdate->bindValue(':configuration_key', 'STORE_OWNER_EMAIL_ADDRESS');
  $Qupdate->execute();

  if (!empty($_POST['CFG_STORE_OWNER_NAME']) && !empty($_POST['CFG_STORE_OWNER_EMAIL_ADDRESS'])) {
    $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
    $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
    $Qupdate->bindValue(':configuration_value', '"' . $_POST['CFG_STORE_OWNER_NAME'] . '" <' . $_POST['CFG_STORE_OWNER_EMAIL_ADDRESS'] . '>');
    $Qupdate->bindValue(':configuration_key', 'EMAIL_FROM');
    $Qupdate->execute();
  }

  //administrators
  $Qcheck = $osC_Database->query('select user_name from :table_administrators where user_name = :user_name');
  $Qcheck->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
  $Qcheck->bindValue(':user_name', $_POST['CFG_ADMINISTRATOR_USERNAME']);
  $Qcheck->execute();

  if ($Qcheck->numberOfRows()) {
    $Qadmin = $osC_Database->query('update :table_administrators set user_password = :user_password and email_address = :email_address where user_name = :user_name');
  } else {
    $Qadmin = $osC_Database->query('insert into :table_administrators (user_name, user_password, email_address) values (:user_name, :user_password, :email_address)');
  }
  $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
  $Qadmin->bindValue(':user_password', osc_encrypt_string(trim($_POST['CFG_ADMINISTRATOR_PASSWORD'])));
  $Qadmin->bindValue(':user_name', $_POST['CFG_ADMINISTRATOR_USERNAME']);
  $Qadmin->bindValue(':email_address', $_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']);
  $Qadmin->execute();

  //administrators access
  $Qadmin = $osC_Database->query('select id from :table_administrators where user_name = :user_name');
  $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
  $Qadmin->bindValue(':user_name', $_POST['CFG_ADMINISTRATOR_USERNAME']);
  $Qadmin->execute();

  $Qcheck = $osC_Database->query('select module from :table_administrators_access where administrators_id = :administrators_id limit 1');
  $Qcheck->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
  $Qcheck->bindInt(':administrators_id', $Qadmin->valueInt('id'));
  $Qcheck->execute();

  if ($Qcheck->numberOfRows()) {
    $Qdel = $osC_Database->query('delete from :table_administrators_access where administrators_id = :administrators_id');
    $Qdel->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
    $Qdel->bindInt(':administrators_id', $Qadmin->valueInt('id'));
    $Qdel->execute();
  }

  $Qaccess = $osC_Database->query('insert into :table_administrators_access (administrators_id, module) values (:administrators_id, :module)');
  $Qaccess->bindTable(':table_administrators_access', TABLE_ADMINISTRATORS_ACCESS);
  $Qaccess->bindInt(':administrators_id', $Qadmin->valueInt('id'));
  $Qaccess->bindValue(':module', '*');
  $Qaccess->execute();
?>

<div class="contentBlock">
  <h1><?php echo $osC_Language->get('page_title_finished'); ?></h1>
      
  <p><?php echo $osC_Language->get('text_finished'); ?></p>
  
  <div class="contentPane">
<?php
  $dir_fs_document_root = $_POST['DIR_FS_DOCUMENT_ROOT'];
  if ((substr($dir_fs_document_root, -1) != '\\') && (substr($dir_fs_document_root, -1) != '/')) {
    if (strrpos($dir_fs_document_root, '\\') !== false) {
      $dir_fs_document_root .= '\\';
    } else {
      $dir_fs_document_root .= '/';
    }
  }

  $http_url = parse_url($_POST['HTTP_WWW_ADDRESS']);
  $http_server = $http_url['scheme'] . '://' . $http_url['host'];
  $http_catalog = $http_url['path'];
  if (isset($http_url['port']) && !empty($http_url['port'])) {
    $http_server .= ':' . $http_url['port'];
  }

  if (substr($http_catalog, -1) != '/') {
    $http_catalog .= '/';
  }

  $http_work_directory = $_POST['HTTP_WORK_DIRECTORY'];
  if (substr($http_work_directory, -1) != '/') {
    $http_work_directory .= '/';
  }

  $osC_DirectoryListing = new osC_DirectoryListing($http_work_directory);
  $osC_DirectoryListing->setIncludeDirectories(false);
  $osC_DirectoryListing->setCheckExtension('cache');

  foreach ($osC_DirectoryListing->getFiles() as $files) {
    @unlink($osC_DirectoryListing->getDirectory() . '/' . $files['name']);
  }

  $file_contents = '<?php' . "\n" .
                   '  define(\'HTTP_SERVER\', \'' . $http_server . '\');' . "\n" .
                   '  define(\'HTTPS_SERVER\', \'' . $http_server . '\');' . "\n" .
                   '  define(\'ENABLE_SSL\', false);' . "\n" .
                   '  define(\'HTTP_COOKIE_DOMAIN\', \'' . $http_url['host'] . '\');' . "\n" .
                   '  define(\'HTTPS_COOKIE_DOMAIN\', \'' . $http_url['host'] . '\');' . "\n" .
                   '  define(\'HTTP_COOKIE_PATH\', \'' . $http_catalog . '\');' . "\n" .
                   '  define(\'HTTPS_COOKIE_PATH\', \'' . $http_catalog . '\');' . "\n" .
                   '  define(\'DIR_WS_HTTP_CATALOG\', \'' . $http_catalog . '\');' . "\n" .
                   '  define(\'DIR_WS_HTTPS_CATALOG\', \'' . $http_catalog . '\');' . "\n" .
                   '  define(\'DIR_WS_IMAGES\', \'images/\');' . "\n\n" .
                   '  define(\'DIR_WS_DOWNLOAD_PUBLIC\', \'pub/\');' . "\n" .
                   '  define(\'DIR_FS_CATALOG\', \'' . $dir_fs_document_root . '\');' . "\n" .
                   '  define(\'DIR_FS_ADMIN\', \'admin/\');' . "\n" .
                   '  define(\'DIR_FS_WORK\', \'' . $http_work_directory . '\');' . "\n" .
                   '  define(\'DIR_FS_DOWNLOAD\', DIR_FS_CATALOG . \'download/\');' . "\n" .
                   '  define(\'DIR_FS_DOWNLOAD_PUBLIC\', DIR_FS_CATALOG . \'pub/\');' . "\n" .
                   '  define(\'DIR_FS_BACKUP\', \'' . $dir_fs_document_root . '\' . DIR_FS_ADMIN . \'backups/\');' . "\n" .
                   '  define(\'DIR_FS_CACHE\', DIR_FS_CATALOG . \'cache/\');' . "\n" .
                   '  define(\'DIR_FS_CACHE_ADMIN\', DIR_FS_CACHE . DIR_FS_ADMIN);' . "\n\n" .
                   '  define(\'DB_SERVER\', \'' . $_POST['DB_SERVER'] . '\');' . "\n" .
                   '  define(\'DB_SERVER_USERNAME\', \'' . $_POST['DB_SERVER_USERNAME'] . '\');' . "\n" .
                   '  define(\'DB_SERVER_PASSWORD\', \'' . $_POST['DB_SERVER_PASSWORD'] . '\');' . "\n" .
                   '  define(\'DB_DATABASE\', \'' . $_POST['DB_DATABASE'] . '\');' . "\n" .
                   '  define(\'DB_DATABASE_CLASS\', \'' . $_POST['DB_DATABASE_CLASS'] . '\');' . "\n" .
                   '  define(\'DB_TABLE_PREFIX\', \'' . $_POST['DB_TABLE_PREFIX'] . '\');' . "\n" .
                   '  define(\'USE_PCONNECT\', \'false\');' . "\n" .
                   '  define(\'STORE_SESSIONS\', \'mysql\');' . "\n" .
                   '?>';

  if (file_exists($dir_fs_document_root . 'includes/configure.php') && !is_writeable($dir_fs_document_root . 'includes/configure.php')) {
    @chmod($dir_fs_document_root . 'includes/configure.php', 0777);
  }


  if (file_exists($dir_fs_document_root . 'includes/configure.php') && is_writeable($dir_fs_document_root . 'includes/configure.php')) {
    $fp = fopen($dir_fs_document_root . 'includes/configure.php', 'w');
    fputs($fp, $file_contents);
    fclose($fp);
  } else {
?>

    <form name="install" action="index.php?step=6" method="post">

    <div class="noticeBox">
      <?php echo sprintf($osC_Language->get('error_configuration_file_not_writeable'), $dir_fs_document_root . 'includes/configure.php'); ?>

      <p align="right"><?php echo '<input type="image" src="templates/' . $template . '/languages/' . $osC_Language->getCode() . '/images/buttons/retry.gif" border="0" alt="' . $osC_Language->get('image_button_retry') . '" />'; ?></p>

      <?php echo $osC_Language->get('error_configuration_file_alternate_method'); ?>

      <?php echo osc_draw_textarea_field('contents', $file_contents, 60, 5, 'readonly="readonly" style="width: 100%; height: 120px;"', false); ?>
    </div>

<?php
    foreach ($_POST as $key => $value) {
      if ($key != 'x' && $key != 'y') {
        if (is_array($value)) {
          for ($i=0, $n=sizeof($value); $i<$n; $i++) {
            echo osc_draw_hidden_field($key . '[]', $value[$i]);
          }
        } else {
          echo osc_draw_hidden_field($key, $value);
        }
      }
    }
?>

    </form>

    <p><?php echo $osC_Language->get('text_go_to_shop_after_cfg_file_is_saved'); ?></p>

<?php
  }
?>

    <p align="center">
      <a href="<?php echo $http_server . $http_catalog . 'index.php'; ?>" target="_blank"><img src="images/button_catalog.gif" border="0" alt="Catalog" /></a>
      &nbsp;&nbsp;
      <a href="<?php echo $http_server . $http_catalog . 'admin/index.php'; ?>" target="_blank"><img src="images/button_administration_tool.gif" border="0" alt="Administration Tool" /></a>
    </p>
    
    </div>
  </div>