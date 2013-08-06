<?php
/*
  $Id: feature_products_manager.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Feature_Products_Manager_Admin {
  
    function delete($id) {
      global $osC_Database;

      $Qstatus = $osC_Database->query('delete from  :table_products_frontpage where products_id = :products_id');
      $Qstatus->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
      $Qstatus->bindInt(':products_id', $id);
      $Qstatus->setLogging($_SESSION['module'], $id);
      $Qstatus->execute();
      
      if(!$osC_Database->isError()) {
        osC_Cache::clear('feature-products');
        
        return true;
      }
      
      return false;
    }
    
    function save($id, $value) {
      global $osC_Database;

      $Qstatus = $osC_Database->query('update :table_products_frontpage set sort_order = :sort_order where products_id = :products_id');
      $Qstatus->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
      $Qstatus->bindInt(':products_id', $id);
      $Qstatus->bindInt(':sort_order', $value);
      $Qstatus->setLogging($_SESSION['module'], $id);
      $Qstatus->execute();

      if ( !$osC_Database->isError() ) {
        osC_Cache::clear('feature-products');
        
        return true;
      }

      return false;
    }
  }
?>