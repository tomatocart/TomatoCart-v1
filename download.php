<?php
/*
  $Id: download.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $_SERVER['SCRIPT_FILENAME'] = __FILE__;

  include('includes/application_top.php');

  $file_name = null;
  $cache_file = null;
  
  //attachments file
  if (isset($_GET['type']) && ($_GET['type'] == 'attachment') && isset($_GET['aid']) && !empty($_GET['aid'])) {
    $Qattachments = $osC_Database->query('select filename, cache_filename from :table_products_attachments where attachments_id = :attachments_id');
    $Qattachments->bindTable(':table_products_attachments', TABLE_PRODUCTS_ATTACHMENTS);
    $Qattachments->bindInt(":attachments_id", $_GET['aid']);
    $Qattachments->execute();
    
    $attachments = $Qattachments->toArray();
    
    if (!empty($attachments)) {
    	$file_name = $attachments['filename'];
    	$cache_file = $attachments['cache_filename'];
    }
  }else {
	  //sample file
	  if (isset($_GET['type']) && ($_GET['type'] == 'sample')) {
	    $Qdownload = $osC_Database->query('select sample_filename, cache_sample_filename from :table_products_downloadables where products_id = :products_id');
	    $Qdownload->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
	    $Qdownload->bindInt(':products_id', $_GET['id']);
	    $Qdownload->execute();
	    
	    if ($Qdownload->numberOfRows() > 0) {
	      $file_name = $Qdownload->value('sample_filename');
	      $cache_file = $Qdownload->value('cache_sample_filename');
	    } else {
	      die;
	    }
	  } 
	  //admin view file
	  else if ( isset($_GET['id']) && ( isset($_GET['cache_filename']) || isset($_GET['cache_sample_filename']) ) ) {
	    $Qdownload = $osC_Database->query('select filename, cache_filename, sample_filename, cache_sample_filename from :table_products_downloadables where products_id = :products_id');
	    $Qdownload->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
	    $Qdownload->bindInt(':products_id', $_GET['id']);
	    $Qdownload->execute();
	
	    if ($Qdownload->numberOfRows() > 0) {
	      if (isset($_GET['cache_filename']) && ($_GET['cache_filename'] == $Qdownload->value('cache_filename')) ) {
	        $file_name = $Qdownload->value('filename');
	        $cache_file = $Qdownload->value('cache_filename');
	      } else if (isset($_GET['cache_sample_filename']) && ($_GET['cache_sample_filename'] == $Qdownload->value('cache_sample_filename')) ) {
	        $file_name = $Qdownload->value('sample_filename');
	        $cache_file = $Qdownload->value('cache_sample_filename');
	      }
	    } else {
	      die;
	    }
	  } else {
	    if ($osC_Customer->isLoggedOn() == false) die;
	  
	    // Check download.php was called with proper GET parameters
	    if ((isset($_GET['order']) && !is_numeric($_GET['order'])) || (isset($_GET['id']) && !is_numeric($_GET['id'])) ) {
	      die;
	    }
	  
	    // Check that order_id, customer id and filename match
	    $Qdownload = $osC_Database->query('select date_format(o.date_purchased, "%Y-%m-%d") as date_purchased_day, opd.download_maxdays, opd.download_count, opd.download_maxdays, opd.orders_products_filename, opd.orders_products_cache_filename from :table_orders o, :table_orders_products op, :table_orders_products_download opd where o.customers_id = :customers_id and o.orders_id = :orders_id and o.orders_id = op.orders_id and op.orders_products_id = opd.orders_products_id and opd.orders_products_download_id = :orders_products_download_id and opd.orders_products_filename != ""');
	    $Qdownload->bindTable(':table_orders', TABLE_ORDERS);
	    $Qdownload->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
	    $Qdownload->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
	    $Qdownload->bindInt(':customers_id', $osC_Customer->getID());
	    $Qdownload->bindInt(':orders_id', $_GET['order']);
	    $Qdownload->bindInt(':orders_products_download_id', $_GET['id']);
	    $Qdownload->execute();
	    
	  
	    if ($Qdownload->numberOfRows() < 1) {
	      die();
	    }
	    
	    $file_name = $Qdownload->value('orders_products_filename');
	    $cache_file = $Qdownload->value('orders_products_cache_filename');
	    
	    // MySQL 3.22 does not have INTERVAL
	    list($dt_year, $dt_month, $dt_day) = explode('-', $Qdownload->value('date_purchased_day'));
	    $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $Qdownload->value('download_maxdays'), $dt_year);
	  
	    // Die if time expired (maxdays = 0 means no time limit)
	    if (($Qdownload->value('download_maxdays') != 0) && ($download_timestamp <= time())) die($osC_Language->get('error_download_max_num_of_days'));
	    // Die if remaining count is <=0
	    if ($Qdownload->value('download_count') <= 0) die ($osC_Language->get('error_download_max_num_of_times'));
	    // Die if file is not there
	    if (!file_exists(DIR_FS_DOWNLOAD . $Qdownload->value('orders_products_cache_filename'))) die ($osC_Language->get('error_download_file_not_exist'));
	    
	    // Now decrement counter
	    $Qupdate = $osC_Database->query('update :table_orders_products_download set download_count = download_count-1 where orders_products_download_id = :orders_products_download_id');
	    $Qupdate->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
	    $Qupdate->bindInt(':orders_products_download_id', $_GET['id']);
	    $Qupdate->execute();
	    
	    // Now insert history
	    $Qinsert = $osC_Database->query('insert into :table_products_download_history (orders_products_download_id, download_date, download_ip_address) values (:orders_products_download_id, now(), :download_ip_address)');
	    $Qinsert->bindTable(':table_products_download_history', TABLE_PRODUCTS_DOWNLOAD_HISTORY);
	    $Qinsert->bindInt(':orders_products_download_id', $_GET['id']);
	    $Qinsert->bindValue(':download_ip_address', osc_get_ip_address());
	    $Qinsert->execute();
	  }
  }	  
  

// Returns a random name, 16 to 20 characters long
// There are more than 10^28 combinations
// The directory is "hidden", i.e. starts with '.'
function osc_random_name() {
  $letters = 'abcdefghijklmnopqrstuvwxyz';
  $dirname = '.';
  $length = floor(osc_rand(16,20));

  for ($i = 1; $i <= $length; $i++) {
   $q = floor(osc_rand(1,26));
   $dirname .= $letters[$q];
  }

  return $dirname;
}

// Unlinks all subdirectories and files in $dir
// Works only on one subdir level, will not recurse
function osc_unlink_temp_dir($dir) {
  $h1 = opendir($dir);
  while ($subdir = readdir($h1)) {
// Ignore non directories
    if (!is_dir($dir . $subdir)) continue;
// Ignore . and .. and CVS
    if ($subdir == '.' || $subdir == '..' || $subdir == 'CVS') continue;
// Loop and unlink files in subdirectory
    $h2 = opendir($dir . $subdir);
    while ($file = readdir($h2)) {
      if ($file == '.' || $file == '..') continue;
      @unlink($dir . $subdir . '/' . $file);
    }
    closedir($h2);
    @rmdir($dir . $subdir);
  }
  closedir($h1);
}


// Now send the file with header() magic
  header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Pragma: no-cache");
  header("Content-Type: Application/octet-stream");
  header("Content-disposition: attachment; filename=" . $file_name);
  
  if (isset($_GET['type']) && ($_GET['type'] == 'attachment')) {
  	readfile(DIR_FS_CACHE . 'products_attachments/' . $cache_file);
  	exit;
  }else if (DOWNLOAD_BY_REDIRECT == '1') {
// This will work only on Unix/Linux hosts
    osc_unlink_temp_dir(DIR_FS_DOWNLOAD_PUBLIC);
    $tempdir = osc_random_name();
    umask(0000);
    mkdir(DIR_FS_DOWNLOAD_PUBLIC . $tempdir, 0777);
    symlink(DIR_FS_DOWNLOAD . $cache_file, DIR_FS_DOWNLOAD_PUBLIC . $tempdir . '/' . $file_name);
    osc_redirect(DIR_WS_DOWNLOAD_PUBLIC . $tempdir . '/' . $file_name);
  } else {
// This will work on all systems, but will need considerable resources
// We could also loop with fread($fp, 4096) to save memory
    readfile(DIR_FS_DOWNLOAD . $cache_file);
  }
?>