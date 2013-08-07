<?php
/*
  $Id: rss.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

	$_SERVER['SCRIPT_FILENAME'] = __FILE__;
	
	include('includes/application_top.php');
	include('includes/classes/rss.php');
	 
	if ( isset($_GET['categories_id']) ) {
	  $categories_id = is_numeric($_GET['categories_id']) ? $_GET['categories_id'] : 0;
	  $rss = toC_RSS::buildCategoriesRSS($categories_id);
	}else if ( isset($_GET['group']) ) {
	  $rss = toC_RSS::buildProductsRss($_GET['group']);
	} 
 
  $xml = new osC_XML($rss, 'UTF-8');

// Now send the file with header() magic
  header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Pragma: no-cache");
  header("Content-Type: text/xml");

  echo $xml->toXML();
?>