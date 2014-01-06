<?php
/*
  $Id: auto_completer.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Json_Auto_Completer {

  function getProducts() {  
    global $osC_Database, $osC_Language, $toC_Json, $osC_Image;
    
    if (defined('IMAGE_GROUP_AUTO_COMPLETER')) {
    	$image_group = IMAGE_GROUP_AUTO_COMPLETER;
    }else {
    	$image_group = 'mini';
    }
    
    if (defined('MAX_CHARACTERS_AUTO_COMPLETER')) {
    	$max_name_len = MAX_CHARACTERS_AUTO_COMPLETER;
    }else {
    	$max_name_len = 40;
    }
    
    $products = array();
    if (isset($_POST['keywords']) && !empty($_POST['keywords'])) {
      $Qproducts = $osC_Database->query("select distinct p.products_id as products_id, pd.products_name from :table_products_description pd, :table_products p where pd.products_id = p.products_id and p.products_status = :products_status and products_name like :keywords and language_id =" . $osC_Language->getID() . ' limit :max_results');
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindInt(':products_status', 1);
      $Qproducts->bindInt(':max_results', MAX_DISPLAY_AUTO_COMPLETER_RESULTS);
      $Qproducts->bindValue(':keywords', '%' . $_POST['keywords'] . '%');
      $Qproducts->execute();
      
      while($Qproducts->next()) {
      	$osC_Product = new osC_Product($Qproducts->valueInt('products_id'));
      	
      	$products_name = $Qproducts->value('products_name');
      	
      	if (strlen($products_name) > $max_name_len) {
      		$products_name = substr($products_name, 0, $max_name_len) . '...';
      	}
      	
        $products[] = '<div class="image">' . $osC_Image->show($osC_Product->getImage(), null, null, $image_group) . '</div><div class="details">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->valueInt('products_id')), $products_name) . '<strong class="price">' . $osC_Product->getPriceFormated(true) . '</strong></div>';        
      }
    }
    
    echo $toC_Json->encode($products);
  }
}
?>