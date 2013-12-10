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
    
    $products = array();
    if (isset($_POST['keywords']) && !empty($_POST['keywords'])) {
      $Qproducts = $osC_Database->query("select distinct p.products_id as products_id, products_name, image from :table_products_description pd, :table_products p left join :table_products_images pi on (p.products_id = pi.products_id and pi.default_flag = 1) where pd.products_id = p.products_id and p.products_status = :products_status and products_name like :keywords and language_id =" . $osC_Language->getID() . ' limit :max_results');
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qproducts->bindInt(':products_status', 1);
      $Qproducts->bindInt(':max_results', MAX_DISPLAY_AUTO_COMPLETER_RESULTS);
      $Qproducts->bindValue(':keywords', '%' . $_POST['keywords'] . '%');
      $Qproducts->execute();
      
      while($Qproducts->next()) {
        $products[] = $osC_Image->show($Qproducts->value('image'), null, null, 'mini') . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->valueInt('products_id')), $Qproducts->value('products_name'));        
      }
    }
    
    echo $toC_Json->encode($products);
  }
}
?>