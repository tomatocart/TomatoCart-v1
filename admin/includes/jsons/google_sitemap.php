<?php
/*
  $Id: google_sitemap.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/google_sitemap.php');

  class toC_Json_Google_Sitemap {

    function createGoogleSitemap() {
    	global $toC_Json, $osC_Language;
    	
    	//check whether the root directory is writable
    	if ($_POST['languages_code'] != 'en_US' && !is_writable(DIR_FS_CATALOG)) {
    	  $response = array('success' => false, 'feedback' => $osC_Language->get('error_directory_not_writable'));
    	}else {
    	  $google_sitemap = new toC_Google_Sitemap($_POST['languages_code'],
                                        	       $_POST['products_frequency'],
                                        	       $_POST['products_priority'],
                                        	       $_POST['categories_frequency'],
                                        	       $_POST['categories_priority'],
                                        	       $_POST['articles_frequency'],
                                        	       $_POST['articles_priority']);
    	   
    	  if ($google_sitemap->generateSitemap()) {
    	    $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
    	  } else {
    	    $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
    	  }
    	}
     
      echo $toC_Json->encode($response);
    }
    
  }
?>
