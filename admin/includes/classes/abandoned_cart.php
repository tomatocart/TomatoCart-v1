<?php
/*
  $Id: abandoned_cart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once('includes/classes/customers.php');

  class toC_Abandoned_Cart_Admin {

    function getData($customers_id){
      $data = osC_Customers_Admin::getData($customers_id);
      $data['contents'] = self::getCartContents($customers_id);

      return $data;
    }

    function getCartContents($customers_id) {
      global $osC_Database, $osC_Language;

      $Qproducts = $osC_Database->query('select * from :table_customers_basket c left join :table_products p on c.products_id = p.products_id left join :table_products_description d on c.products_id=d.products_id  where c.customers_id  = :customers_id and d.language_id = :language_id');
      $Qproducts->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':customers_id', $customers_id);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->execute();

      $contents = array();
      while ($Qproducts->next()) {
        $contents[] = array('id' => $Qproducts->valueInt('products_id'),
                            'sku' => $Qproducts->value('products_sku'),
                            'qty' => $Qproducts->value('customers_basket_quantity'),
                            'price' => $Qproducts->value('products_price'),
                            'name' => $Qproducts->value('products_name'));
      }
      $Qproducts->freeResult();

      return $contents;
    }


    function sendEmail($customers_id, $addtional_message) {
      global $osC_Database, $osC_Language;

      $contents = self::getCartContents($customers_id);

      $Qcustomer = $osC_Database->query('select * from :table_customers where customers_id = :customers_id');
      $Qcustomer->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomer->bindInt(':customers_id', $customers_id);
      $Qcustomer->execute();

      include_once('../includes/classes/email_template.php');
      $email = toC_Email_Template::getEmailTemplate('abandoned_cart_inquiry');
      $email->setData($Qcustomer->value('customers_gender'), $Qcustomer->value('customers_firstname'), $Qcustomer->value('customers_lastname'), $contents, $addtional_message, $Qcustomer->value('customers_email_address'));
      $email->buildMessage();
      $email->sendEmail();

      $Qcustomer = $osC_Database->query('update :table_customers set abandoned_cart_last_contact_date = :abandoned_cart_last_contact_date where customers_id = :customers_id');
      $Qcustomer->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomer->bindRaw(':abandoned_cart_last_contact_date', 'now()');
      $Qcustomer->bindInt(':customers_id', $customers_id);
      $Qcustomer->setLogging($_SESSION['module'], $customers_id);
      $Qcustomer->execute();

      if ( $osC_Database->isError() ) {
        return false;
      }

      return true;
    }

    function delete($id = null) {
      global $osC_Database;

      $Qdelete = $osC_Database->query('delete from :table_customers_basket where customers_id = :customers_id');
      $Qdelete->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
      $Qdelete->bindInt(':customers_id', $id);
      $Qdelete->setLogging($_SESSION['module'], $id);
      $Qdelete->execute();

      if ( $osC_Database->isError() ) {
        return false;
      }

      return true;
    }
  }
?>
