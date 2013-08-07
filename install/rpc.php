<?php
/*
  $Id: rpc.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

  require('includes/application.php');

  $dir_fs_www_root = dirname(__FILE__);

  if (isset($_GET['action']) && !empty($_GET['action'])) {
    switch ($_GET['action']) {
      case 'dbCheck':
        $db = array('DB_SERVER' => trim(urldecode($_GET['server'])),
                    'DB_SERVER_USERNAME' => trim(urldecode($_GET['username'])),
                    'DB_SERVER_PASSWORD' => trim(urldecode($_GET['password'])),
                    'DB_DATABASE' => trim(urldecode($_GET['name'])),
                    'DB_DATABASE_CLASS' => trim(urldecode($_GET['class']))
                   );

        $osC_Database = osC_Database::connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD'], $db['DB_DATABASE_CLASS']);

        if ($osC_Database->isError() === false) {
          $osC_Database->selectDatabase($db['DB_DATABASE']);
        }

        if ($osC_Database->isError()) {
          echo '[[0|' . $osC_Database->getError() . ']]';
        } else {
          echo '[[1]]';
        }

        exit;
        break;

      case 'dbImport':
        $db = array('DB_SERVER' => trim(urldecode($_GET['server'])),
                    'DB_SERVER_USERNAME' => trim(urldecode($_GET['username'])),
                    'DB_SERVER_PASSWORD' => trim(urldecode($_GET['password'])),
                    'DB_DATABASE' => trim(urldecode($_GET['name'])),
                    'DB_DATABASE_CLASS' => trim(urldecode($_GET['class'])),
                    'DB_INSERT_SAMPLE_DATA' => ((trim(urldecode($_GET['import'])) == '1') ? 'true' : 'false'),
                    'DB_TABLE_PREFIX' => trim(urldecode($_GET['prefix']))
                   );

        $osC_Database = osC_Database::connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD'], $db['DB_DATABASE_CLASS']);

        if ($osC_Database->isError() === false) {
          $osC_Database->selectDatabase($db['DB_DATABASE']);
        }

        if ($osC_Database->isError() === false) {
          $sql_file = $dir_fs_www_root . '/tomatocart.sql';

          $osC_Database->importSQL($sql_file, $db['DB_DATABASE'], $db['DB_TABLE_PREFIX']);
        }

        if ( ($osC_Database->isError() === false) && ($db['DB_INSERT_SAMPLE_DATA'] == 'true') ) {
          $sql_file = $dir_fs_www_root . '/tomatocart_sample_data.sql';

          $osC_Database->importSQL($sql_file, $db['DB_DATABASE'], $db['DB_TABLE_PREFIX']);
        }

        if ($osC_Database->isError() === false) {
          //english
          foreach ($osC_Language->extractDefinitions('en_US.xml') as $def) {
            $Qdef = $osC_Database->query('insert into :table_languages_definitions (languages_id, content_group, definition_key, definition_value) values (:languages_id, :content_group, :definition_key, :definition_value)');
            $Qdef->bindTable(':table_languages_definitions', $db['DB_TABLE_PREFIX'] . 'languages_definitions');
            $Qdef->bindInt(':languages_id', 1);
            $Qdef->bindValue(':content_group', $def['group']);
            $Qdef->bindValue(':definition_key', $def['key']);
            $Qdef->bindValue(':definition_value', $def['value']);
            $Qdef->execute();
          }

          $osC_DirectoryListing = new osC_DirectoryListing('../includes/languages/en_US');
          $osC_DirectoryListing->setRecursive(true);
          $osC_DirectoryListing->setIncludeDirectories(false);
          $osC_DirectoryListing->setAddDirectoryToFilename(true);
          $osC_DirectoryListing->setCheckExtension('xml');

          foreach ($osC_DirectoryListing->getFiles() as $files) {
            foreach ($osC_Language->extractDefinitions('en_US/' . $files['name']) as $def) {
              $Qdef = $osC_Database->query('insert into :table_languages_definitions (languages_id, content_group, definition_key, definition_value) values (:languages_id, :content_group, :definition_key, :definition_value)');
              $Qdef->bindTable(':table_languages_definitions', $db['DB_TABLE_PREFIX'] . 'languages_definitions');
              $Qdef->bindInt(':languages_id', 1);
              $Qdef->bindValue(':content_group', $def['group']);
              $Qdef->bindValue(':definition_key', $def['key']);
              $Qdef->bindValue(':definition_value', $def['value']);
              $Qdef->execute();
            }
          }
        
          //chinese
          foreach ($osC_Language->extractDefinitions('zh_CN.xml') as $def) {
            $Qdef = $osC_Database->query('insert into :table_languages_definitions (languages_id, content_group, definition_key, definition_value) values (:languages_id, :content_group, :definition_key, :definition_value)');
            $Qdef->bindTable(':table_languages_definitions', $db['DB_TABLE_PREFIX'] . 'languages_definitions');
            $Qdef->bindInt(':languages_id', 2);
            $Qdef->bindValue(':content_group', $def['group']);
            $Qdef->bindValue(':definition_key', $def['key']);
            $Qdef->bindValue(':definition_value', $def['value']);
            $Qdef->execute();
          }
  
          $osC_DirectoryListing = new osC_DirectoryListing('../includes/languages/zh_CN');
          $osC_DirectoryListing->setRecursive(true);
          $osC_DirectoryListing->setIncludeDirectories(false);
          $osC_DirectoryListing->setAddDirectoryToFilename(true);
          $osC_DirectoryListing->setCheckExtension('xml');
  
          foreach ($osC_DirectoryListing->getFiles() as $files) {
            foreach ($osC_Language->extractDefinitions('zh_CN/' . $files['name']) as $def) {
              $Qdef = $osC_Database->query('insert into :table_languages_definitions (languages_id, content_group, definition_key, definition_value) values (:languages_id, :content_group, :definition_key, :definition_value)');
              $Qdef->bindTable(':table_languages_definitions', $db['DB_TABLE_PREFIX'] . 'languages_definitions');
              $Qdef->bindInt(':languages_id', 2);
              $Qdef->bindValue(':content_group', $def['group']);
              $Qdef->bindValue(':definition_key', $def['key']);
              $Qdef->bindValue(':definition_value', $def['value']);
              $Qdef->execute();
            }
          }
        }
        
        if ($osC_Database->isError() === false) {
          define('DB_TABLE_PREFIX', $db['DB_TABLE_PREFIX']);
          include('../includes/database_tables.php');

/* HPDL
          $services = array('banner',
                            'breadcrumb',
                            'category_path',
                            'core',
                            'currencies',
                            'debug',
                            'language',
                            'output_compression',
                            'recently_visited',
                            'reviews',
                            'session',
                            'simple_counter',
                            'specials',
                            'whos_online');
*/
          $services = array('output_compression',
                            'session',
                            'language',
//                            'debug',
                            'currencies',
                            'core',
                            'simple_counter',
                            'category_path',
                            'breadcrumb',
                            'whos_online',
//                            'banner',
                            'specials',
                            'reviews',
                            'recently_visited');

          $installed = array();

          foreach ($services as $service) {
            include('../admin/includes/modules/services/' . $service . '.php');
            $class = 'osC_Services_' . $service . '_Admin';
            $module = new $class();
            $module->install();

            if (isset($module->depends)) {
              if (is_string($module->depends) && (($key = array_search($module->depends, $installed)) !== false)) {
                if (isset($installed[$key+1])) {
                  array_splice($installed, $key+1, 0, $service);
                } else {
                  $installed[] = $service;
                }
              } elseif (is_array($module->depends)) {
                foreach ($module->depends as $depends_module) {
                  if (($key = array_search($depends_module, $installed)) !== false) {
                    if (!isset($array_position) || ($key > $array_position)) {
                      $array_position = $key;
                    }
                  }
                }

                if (isset($array_position)) {
                  array_splice($installed, $array_position+1, 0, $service);
                } else {
                  $installed[] = $service;
                }
              }
            } elseif (isset($module->precedes)) {
              if (is_string($module->precedes)) {
                if ((($key = array_search($module->precedes, $installed)) !== false)) {
                  array_splice($installed, $key, 0, $service);
                } else {
                  $installed[] = $service;
                }
              } elseif (is_array($module->precedes)) {
                foreach ($module->precedes as $precedes_module) {
                  if (($key = array_search($precedes_module, $installed)) !== false) {
                    if (!isset($array_position) || ($key < $array_position)) {
                      $array_position = $key;
                    }
                  }
                }

                if (isset($array_position)) {
                  array_splice($installed, $array_position, 0, $service);
                } else {
                  $installed[] = $service;
                }
              }
            } else {
              $installed[] = $service;
            }

            unset($array_position);
          }

          $Qs = $osC_Database->query('insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ("Service Modules", "MODULE_SERVICES_INSTALLED",  :configuration_value, "Installed services modules", "6", "0", now())');
          $Qs->bindTable(':table_configuration', TABLE_CONFIGURATION);
          $Qs->bindValue(':configuration_value', implode(';', $installed));
          $Qs->execute();

          include('includes/classes/payment.php');
          include('includes/classes/shipping.php');
          include('includes/classes/order_total.php');

          include('../admin/includes/modules/payment/cod.php');
          $module = new osC_Payment_cod();
          $module->install();

          $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = 1 where configuration_key = :configuration_key');
          $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
          $Qupdate->bindValue(':configuration_key', 'MODULE_PAYMENT_COD_STATUS');
          $Qupdate->execute();

          include('../admin/includes/modules/shipping/flat.php');
          $module = new osC_Shipping_flat();
          $module->install();

          include('../admin/includes/modules/order_total/sub_total.php');
          $module = new osC_OrderTotal_sub_total();
          $module->install();

          include('../admin/includes/modules/order_total/shipping.php');
          $module = new osC_OrderTotal_shipping();
          $module->install();

          include('../admin/includes/modules/order_total/tax.php');
          $module = new osC_OrderTotal_tax();
          $module->install();

          include('../admin/includes/modules/order_total/total.php');
          $module = new osC_OrderTotal_total();
          $module->install();
          
          include('../admin/includes/modules/order_total/coupon.php');
          $module = new osC_OrderTotal_coupon();
          $module->install();
          
          include('../admin/includes/modules/order_total/gift_certificate.php');
          $module = new osC_OrderTotal_gift_certificate();
          $module->install();
          
          include('../admin/includes/modules/order_total/store_credit.php');
          $module = new osC_OrderTotal_store_credit();
          $module->install();
          
          include('../admin/includes/modules/order_total/gift_wrapping.php');
          $module = new osC_OrderTotal_gift_wrapping();
          $module->install();
        }

        if ( ($osC_Database->isError() === false) && ($db['DB_DATABASE_CLASS'] == 'mysql_innodb') ) {
          $Qinno = $osC_Database->query('show variables like "have_innodb"');
          if (($Qinno->numberOfRows() === 1) && (strtolower($Qinno->value('Value')) == 'yes')) {
            $database_tables = array('address_book', 'categories', 'categories_description', 'customers', 'customers_basket', 'manufacturers', 'manufacturers_info', 'orders', 'orders_products', 'orders_status', 'orders_status_history', 'orders_products_attributes', 'orders_products_download', 'orders_total', 'products', 'products_attributes', 'products_attributes_download', 'products_description', 'products_options', 'products_options_values', 'products_options_values_to_products_options', 'products_to_categories', 'reviews', 'weight_classes', 'weight_classes_rules');

            foreach ($database_tables as $table) {
              $osC_Database->simpleQuery('alter table ' . $db['DB_TABLE_PREFIX'] . $table . ' ENGINE = innodb');
            }
          }
        }

        if ($osC_Database->isError()) {
          echo '[[0|' . $osC_Database->getError() . ']]';
        } else {
          echo '[[1]]';
        }

        exit;
        break;

      case 'dbImportSample':
        $db = array('DB_SERVER' => trim(urldecode($_GET['server'])),
                    'DB_SERVER_USERNAME' => trim(urldecode($_GET['username'])),
                    'DB_SERVER_PASSWORD' => trim(urldecode($_GET['password'])),
                    'DB_DATABASE' => trim(urldecode($_GET['name'])),
                    'DB_DATABASE_CLASS' => trim(urldecode($_GET['class'])),
                    'DB_TABLE_PREFIX' => trim(urldecode($_GET['prefix']))
                   );

        $osC_Database = osC_Database::connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD'], $db['DB_DATABASE_CLASS']);

        if ($osC_Database->isError() === false) {
          $osC_Database->selectDatabase($db['DB_DATABASE']);
        }

        if ($osC_Database->isError() === false) {
          @chmod('../images', 0777);
          @chmod('../images/categories', 0777);
          @chmod('../images/products', 0777);
          @chmod('../images/products/large', 0777);
          @chmod('../images/products/mini', 0777);
          @chmod('../images/products/originals', 0777);
          @chmod('../images/products/product_info', 0777);
          @chmod('../images/products/thumbnails', 0777);

          toc_copy('samples/images', '../images');

          $sql_file = $dir_fs_www_root . '/tomatocart_sample_data.sql';

          $osC_Database->importSQL($sql_file, $db['DB_DATABASE'], $db['DB_TABLE_PREFIX']);
        }

        if ($osC_Database->isError()) {
          echo '[[0|' . $osC_Database->getError() . ']]';
        } else {
          echo '[[1]]';
        }

        exit;
        break;

      case 'checkWorkDir':
        $directory = trim(urldecode($_GET['dir']));

        if (file_exists($directory)) {
          if (is_writeable($directory)) {
            if (file_exists($directory . '/.htaccess') === false) {
              if ($fp = @fopen($directory . '/.htaccess', 'w')) {
                flock($fp, 2); // LOCK_EX
                fputs($fp, "<Files *>\nOrder Deny,Allow\nDeny from all\n</Files>");
                flock($fp, 3); // LOCK_UN
                fclose($fp);
              }
            }

            echo '[[1]]';
          } else {
            echo '[[0|' . $directory . ']]';
          }
        } else {
          echo '[[-1|' . $directory . ']]';
        }

        exit;
        break;

      case 'getDirectoryPath':
        $directory = trim(urldecode($_GET['dir']));

        if (!is_dir($directory) || (false === $fh = @opendir($directory))) {
          $query = basename($directory);
          $directory = dirname($directory);

          if ($fh = @opendir($directory)) {
            $dirs = array();
            while (false !== ($dir = readdir($fh))) {
              if ( ($dir != '.') && ($dir != '..') && (substr($dir, 0, 1) != '.') && is_dir($directory . '/' . $dir)) {
                if (strlen($query) > 1) {
                  if (substr($dir, 0, strlen($query)) == $query) {
                    $dirs[] = $directory . '/' . $dir;
                  }
                } else {
                  $dirs[] = $directory . '/' . $dir;
                }
              }
            }
            closedir($fh);

            if (sizeof($dirs) > 0) {
              sort($dirs);

              echo '[[0|' . implode(';', $dirs) . ']]';
            } else {
              echo '[[-1|invalidPath]]';
            }
          } else {
            echo '[[-1|invalidPath]]';
          }
        } else {
          echo '[[1|' . $directory . ']]';
        }

        exit;
        break;
    }
  }

  echo '[[-100|noActionError]]';
?>