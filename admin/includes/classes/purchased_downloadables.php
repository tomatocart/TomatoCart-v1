<?php
/*
  $Id: purchased_downloadables.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_PurchasedDownloadables_Admin {

  function getData($id) {
    global $osC_Database, $osC_Language;

    $Qdownloadable = $osC_Database->query('select o.customers_name, o.customers_email_address, op.products_name from :table_orders_products_download opd, :table_orders_products op, :table_orders o where op.orders_products_id = opd.orders_products_id and op.orders_id = opd.orders_id and op.orders_id = o.orders_id and opd.orders_products_download_id = :orders_products_download_id');
    $Qdownloadable->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
    $Qdownloadable->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
    $Qdownloadable->bindTable(':table_orders', TABLE_ORDERS);
    $Qdownloadable->bindInt(':orders_products_download_id', $id);
    $Qdownloadable->execute();

    $data = $Qdownloadable->toArray();

    $Qdownloadable->freeResult();

    return $data;
  }
  
  function setStatus($id, $flag) {
    global $osC_Database;
    
    $Qstatus = $osC_Database->query('update :table_orders_products_download set status = :status where orders_products_download_id = :orders_products_download_id');
    $Qstatus->bindTable(':table_orders_products_download', TABLE_ORDERS_PRODUCTS_DOWNLOAD);
    $Qstatus->bindInt(':status', $flag);
    $Qstatus->bindInt(':orders_products_download_id', $id);
    $Qstatus->setLogging($_SESSION['module'], $id);
    $Qstatus->execute();

    if ( !$osC_Database->isError() ) {
      if ($flag == '1') {
        require_once('../includes/classes/email_template.php');
        $email = toC_Email_Template::getEmailTemplate('active_downloadable_product');
        
        $data = self::getData($id);
        
        $email->setData($data['customers_name'], $data['customers_email_address'], $data['products_name']);
        $email->buildMessage();
        $email->sendEmail();
      }

      return true;
    }
                
    return false;
  }
}
?>