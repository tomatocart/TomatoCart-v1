<?php
/*
  $Id: coupons.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  include('includes/classes/category_tree.php');
  require('includes/classes/currencies.php');
  require('includes/classes/coupons.php');
  require('includes/classes/categories.php');

  class toC_Json_Coupons {
        
    function listCoupons() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];       
      
      $Qcoupons = $osC_Database->query('select * from :table_coupons c left join :table_coupons_description cd on c.coupons_id=cd.coupons_id and cd.language_id=:language_id  order by c.coupons_id');
      $Qcoupons->bindTable(':table_coupons', TABLE_COUPONS);
      $Qcoupons->bindTable(':table_coupons_description', TABLE_COUPONS_DESCRIPTION);
      $Qcoupons->bindInt(':language_id', $osC_Language->getID());
      $Qcoupons->setExtBatchLimit($start, $limit);
      $Qcoupons->execute();
        
      $records = array();     
      while ( $Qcoupons->next() ) {        
        $action = array();        
        $action[] = array('class' => 'icon-edit-record', 'qtip' => $osC_Language->get('icon_edit'));
        $action[] = array('class' => 'icon-send-email-record', 'qtip' => $osC_Language->get('icon_email_send'));
        $action[] = array('class' => 'icon-view-record', 'qtip' => $osC_Language->get('icon_view'));
        $action[] = array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash'));
        
        $records[] = array(
          'coupons_id' => $Qcoupons->valueInt('coupons_id'),
          'coupons_name' =>  $Qcoupons->value('coupons_name'),
          'coupons_code' => $Qcoupons->value('coupons_code'),
          'start_date' => osC_DateTime::getShort($Qcoupons->value('start_date')),
          'expires_date' => osC_DateTime::getShort($Qcoupons->value('expires_date')),
          'coupons_status' => $Qcoupons->value('coupons_status'),
          'action' => $action
        );           
      }
      $Qcoupons->freeResult();         
       
      $response = array(EXT_JSON_READER_TOTAL => $Qcoupons->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }          
    
    function saveCoupons() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $feedback = array();

      if ( empty($_REQUEST['coupons_code']) ) {
        $_REQUEST['coupons_code'] = toC_Coupons_Admin::createCouponCode();
      }

      $data = array('coupons_status'           => $_REQUEST['coupons_status'],
                    'coupons_name'             => $_REQUEST['coupons_name'],
                    'coupons_description'      => $_REQUEST['coupons_description'],
                    'coupons_code'             => $_REQUEST['coupons_code'],
                    'coupons_amount'           => $_REQUEST['coupons_amount'],
                    'coupons_include_tax'      => $_REQUEST['coupons_include_tax'],
                    'coupons_include_shipping' => $_REQUEST['coupons_include_shipping'],
                    'coupons_minimum_order'    => $_REQUEST['coupons_minimum_order'],
                    'uses_per_coupon'          => $_REQUEST['uses_per_coupon'],
                    'uses_per_customer'        => $_REQUEST['uses_per_customer'],
                    'start_date'               => $_REQUEST['start_date'],
                    'coupons_type'             => $_REQUEST['coupons_type'],
                    'expires_date'             => $_REQUEST['expires_date'],
                    'coupons_restrictions'     => $_REQUEST['coupons_restrictions']);

      if ($_REQUEST['coupons_restrictions'] == COUPONS_RESTRICTION_CATEGOREIS) {
        $data['categories_id_array'] = array();
        
        if ($_REQUEST['categoriesIds']) {
          $data['categories_id_array'] = explode(',', $_REQUEST['categoriesIds']);
        }
      }

      if ($_POST['coupons_restrictions'] == COUPONS_RESTRICTION_PRODUCTS) {        
        $data['products_id_array'] = array();
        
        if ($_REQUEST['productsIds']) {
          $data['products_id_array'] = explode(',', $_REQUEST['productsIds']);
        }
      }

      if ($_REQUEST['coupons_type'] == 0) {
        if ($_REQUEST['coupons_amount'] < 0 || $_REQUEST['coupons_amount'] == '') {
          $feedback[] = $osC_Language->get('ms_error_coupons_amount');
          $error = true;
        }
      }

      if ($_REQUEST['coupons_type'] == 1) {
        if ($_REQUEST['coupons_amount'] > 100 || $_REQUEST['coupons_amount'] <= 0) {
          $feedback[] = $osC_Language->get('ms_error_coupons_amount_percentage');
          $error = true;
        }
      }

      foreach ($osC_Language->getAll() as $l) {
        if ( empty($_REQUEST['coupons_name'][$l['id']]) ) {
          $feedback[] = sprintf($osC_Language->get('ms_error_coupons_name_empty'), $l['name']);
          $error = true;
        }
      }

      if (empty($_REQUEST['start_date'])) {
        $feedback[] = $osC_Language->get('ms_error_start_date_empty');
        $error = true;
      }

      if (empty($_REQUEST['expires_date'])) {
        $feedback[] = $osC_Language->get('ms_error_expires_date_empty');
        $error = true;
      }
      
      $start_date = strtotime($_REQUEST['start_date']);
      $expires_date = strtotime($_REQUEST['expires_date']);
      if ($start_date > $expires_date) {
        $feedback[] = $osC_Language->get('ms_error_expires_date_smaller_than_start_date');
        $error = true;
      }

      if ($_REQUEST['coupons_restrictions'] == 1) {
        if (empty($data['categories_id_array'])) {
          $feedback[] = $osC_Language->get('ms_error_categories_empty');
          $error = true;
        }
      }

      if ($_REQUEST['coupons_restrictions'] == 2) {
        if (empty($data['products_id_array'])) {
          $feedback[] = $osC_Language->get('ms_error_products_empty');
          $error = true;
        }
      }

      if ($error === false) {
        if ( toC_Coupons_Admin::save((isset($_REQUEST['coupons_id']) && is_numeric($_REQUEST['coupons_id']) ? $_REQUEST['coupons_id'] : null), $data) ) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }      
      }else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));     
      }
      
      echo $toC_Json->encode($response);
    }
    
    function loadCoupons() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $coupons_id = ( isset($_REQUEST['coupons_id']) && is_numeric($_REQUEST['coupons_id']) ) ? $_REQUEST['coupons_id'] : null;
      
      if ($coupons_id > 0) {
        $data = toC_Coupons_Admin::getData($coupons_id);            
        $data['start_date'] = osC_DateTime::getDate($data['start_date']);
        $data['expires_date'] = osC_DateTime::getDate($data['expires_date']);

        $Qcoupons = $osC_Database->query('select * from :table_coupons_description  where coupons_id = :coupons_id ');
        $Qcoupons->bindTable(':table_coupons_description', TABLE_COUPONS_DESCRIPTION);
        $Qcoupons->bindInt(':coupons_id', $coupons_id);
        $Qcoupons->execute();
        
        while ($Qcoupons->next()) {
          $data['coupons_name['.$Qcoupons->value('language_id').']'] = $Qcoupons->value('coupons_name');
          $data['coupons_description['.$Qcoupons->value('language_id').']'] = $Qcoupons->value('coupons_description');  
        }
        $Qcoupons->freeResult();
        
        $data['coupons_restrictions'] = COUPONS_RESTRICTION_NONE;
        
        $Qctoc = $osC_Database->query('select ctoc.*,cd.categories_name from :table_coupons_to_categories ctoc left join :table_categories_description cd on ctoc.categories_id=cd.categories_id  where ctoc.coupons_id = :coupons_id and cd.language_id = :language_id ');
        $Qctoc->bindTable(':table_coupons_to_categories', TABLE_COUPONS_TO_CATEGORIES);
        $Qctoc->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qctoc->bindInt(':language_id', $osC_Language->getID());
        $Qctoc->bindInt(':coupons_id', $coupons_id);
        $Qctoc->execute();
        
        if ($Qctoc->numberOfRows() > 0) {
          $data['coupons_restrictions'] = COUPONS_RESTRICTION_CATEGOREIS;
          
          while ($Qctoc->next()) {
            $data['categories'][] = array('id' => $Qctoc->value('categories_id'), 'name' => $Qctoc->value('categories_name'));
          }
        }
        
        $Qctop = $osC_Database->query('select ctop.*,pd.products_name from :table_coupons_to_products ctop left join :table_products_description pd on ctop.products_id=pd.products_id  where ctop.coupons_id = :coupons_id and pd.language_id = :language_id ');
        $Qctop->bindTable(':table_coupons_to_products', TABLE_COUPONS_TO_PRODUCTS);
        $Qctop->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
        $Qctop->bindInt(':language_id', $osC_Language->getID());
        $Qctop->bindInt(':coupons_id', $coupons_id);
        $Qctop->execute();
        
        if ($Qctop->numberOfRows() > 0) {
          $data['coupons_restrictions'] = COUPONS_RESTRICTION_PRODUCTS;
          
          while ($Qctop->next()) {
            $data['products'][] = array('id' => $Qctop->value('products_id') , 'name' => $Qctop->value('products_name'));    
          }
        }

        $response = array('success' => true, 'data' => $data);
      } else {
        $response = array('success' => false);
      }
     
      echo $toC_Json->encode($response);   
    }
    
    function setStatus() {
      global $toC_Json, $osC_Language;
        
      if ( isset($_REQUEST['cID']) && is_numeric($_REQUEST['cID']) ) {
        if ( toC_Coupons_Admin::setStatus($_REQUEST['cID'], ( isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null) ) ) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed') );
        }
        else
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
    
      echo $toC_Json->encode($response);
    }
    
    function deleteCoupon() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      $feedback = array();
      
      $Qcoupons = $osC_Database->query("select * from :table_coupons_redeem_history where coupons_id = :coupons_id");
      $Qcoupons->bindTable(':table_coupons_redeem_history', TABLE_COUPONS_REDEEM_HISTORY);
      $Qcoupons->bindInt(':coupons_id', $_REQUEST['coupons_id']);
      $Qcoupons->execute();
      
      if ($Qcoupons->numberOfRows() > 0) {
        $error = true;
        $feedback[] = $osC_Language->get('delete_warning_coupon_in_use');
      }
      
      if ($error === false) {
        if ( toC_Coupons_Admin::delete($_REQUEST['coupons_id']) ) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));               
        }else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));       
        }     
      }else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));      
      }
      
      echo $toC_Json->encode($response);  
    }
    
    function deleteCoupons() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $error = false;
      $feedback = array();
      $batch = explode(',', $_REQUEST['batch']);
      
      $Qcoupons = $osC_Database->query('select c.coupons_id, cd.coupons_name from :table_coupons c left join :table_coupons_description cd on c.coupons_id=cd.coupons_id  where c.coupons_id in (":id") and cd.language_id=:language_id order by cd.coupons_name');
      $Qcoupons->bindTable(':table_coupons', TABLE_COUPONS);
      $Qcoupons->bindTable(':table_coupons_description', TABLE_COUPONS_DESCRIPTION);
      $Qcoupons->bindInt(':language_id', $osC_Language->getID());
      $Qcoupons->bindRaw(':id', implode('", "', array_unique(array_filter(array_slice($batch, 0, MAX_DISPLAY_SEARCH_RESULTS), 'is_numeric'))));
      $Qcoupons->execute();
      
      $redeemed_coupons = array();
      while ($Qcoupons->next()) {
        $Qredeem = $osC_Database->query("select * from :table_coupons_redeem_history where coupons_id = :coupons_id");
        $Qredeem->bindTable(':table_coupons_redeem_history', TABLE_COUPONS_REDEEM_HISTORY);
        $Qredeem->bindInt(':coupons_id', $Qcoupons->valueInt('coupons_id'));
        $Qredeem->execute();
        
        if ($Qredeem->numberOfRows() > 0) {
          $error = true;
          $redeemed_coupons[] = '<b>' . $Qcoupons->valueProtected('coupons_name') . '</b>';        
        }
      }
      
      if ($error === false) {
        foreach ($batch as $id) {
          if (!toC_Coupons_Admin::delete($id)) {
            $error = true;
            break;        
          }   
        }
      
      if ($error === false) {
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
          $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('batch_delete_warning_coupons_in_use') . '<br /><br />' . implode(', ', $redeemed_coupons));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function getCustomers() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qcustomers = $osC_Database->query('select customers_email_address, customers_id, customers_firstname, customers_lastname from :table_customers order by customers_firstname');
      $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomers->execute();
      
      $records = array(array('id' => '0', 'text' => $osC_Language->get('none')));
      while ($Qcustomers->next()) {
        $records[] = array('id'=> $Qcustomers->valueInt('customers_id'),
                           'text' => $Qcustomers->value('customers_firstname') . ' ' . $Qcustomers->value('customers_lastname') . '(' . $Qcustomers->value('customers_email_address') . ')');
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
                              
      echo $toC_Json->encode($response);
    }
        
    function sendEmail() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $coupons_id = ( isset($_REQUEST['coupons_id']) && is_numeric($_REQUEST['coupons_id']) ) ? $_REQUEST['coupons_id'] : null;
      $customers_id = $_REQUEST['customers_id'];
      $message = $_REQUEST['message'];
      
      $Qcustomer = $osC_Database->query('select customers_email_address, customers_id, customers_firstname, customers_lastname, customers_gender from :table_customers where customers_id = :customers_id');
      $Qcustomer->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomer->bindInt(':customers_id', $customers_id);
      $Qcustomer->execute();
      
      $Qcoupons = $osC_Database->query('select * from :table_coupons c, :table_coupons_description cd where c.coupons_id=cd.coupons_id and cd.language_id=:language_id and c.coupons_id = :coupons_id');
      $Qcoupons->bindTable(':table_coupons', TABLE_COUPONS);
      $Qcoupons->bindTable(':table_coupons_description', TABLE_COUPONS_DESCRIPTION);
      $Qcoupons->bindInt(':language_id', $osC_Language->getID());
      $Qcoupons->bindInt(':coupons_id', $coupons_id);
      $Qcoupons->execute();
      
      if (!$osC_Database->isError) {
        $email = $Qcustomer->value('customers_email_address');
        $customers_firstname = $Qcustomer->value('customers_firstname');
        $customers_lastname = $Qcustomer->value('customers_lastname');

        include('../includes/classes/email_template.php');
        $email_template = toC_Email_Template::getEmailTemplate('send_coupon');
        $email_template->setData($Qcustomer->value('customers_gender'), $customers_firstname, $customers_lastname, $Qcoupons->value('coupons_code'), $message, $email);
        $email_template->buildMessage();
        $email_template->sendEmail();
                 
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      }else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);    
    }
    
    function getRedeemHistory() {
      global $toC_Json, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $coupons_id = ( isset($_REQUEST['coupons_id']) && is_numeric($_REQUEST['coupons_id']) ) ? $_REQUEST['coupons_id'] : null;
      
      $osC_Currencies = new osC_Currencies_Admin();
      
      $Qcoupons = $osC_Database->query("select crh.*, ot.value, c.customers_id, c.customers_firstname, c.customers_lastname from :table_coupons_redeem_history crh, :table_orders_total ot, :table_customers c where crh.coupons_id = :coupons_id and crh.orders_id = ot.orders_id and crh.customers_id = c.customers_id and ot.class = 'coupon' order by crh.orders_id");
      $Qcoupons->bindTable(':table_coupons_redeem_history', TABLE_COUPONS_REDEEM_HISTORY);
      $Qcoupons->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
      $Qcoupons->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcoupons->bindInt(':coupons_id', $coupons_id);
      $Qcoupons->setExtBatchLimit($start, $limit);
      $Qcoupons->execute();
      
      $records = array();
      while ($Qcoupons->next()) {
        $records[] = array('customers_id' =>  $Qcoupons->valueInt('customers_id'),
                           'customers_name' => $Qcoupons->value('customers_firstname') . '&nbsp;' . $Qcoupons->value('customers_lastname'),
                           'orders_id' => $Qcoupons->valueInt('orders_id'),
                           'redeem_amount' => $osC_Currencies->format($Qcoupons->value('redeem_amount')),
                           'redeem_date' => osC_DateTime::getShort($Qcoupons->value('redeem_date')), 
                           'redeem_id_address' => $Qcoupons->value('redeem_ip_address'));
      }
      
      $Qcoupons->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qcoupons->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);

    }
    
    function getCategories() {
      global $toC_Json, $osC_Language;
      
      $osC_CategoryTree = new osC_CategoryTree_Admin();
      
      $categories_array = array();
      if (isset($_REQUEST['top']) && ($_REQUEST['top'] == '1')) {
        $categories_array = array(array('id' => '', 'text' => $osC_Language->get('top_category')));
      }
      
      foreach ($osC_CategoryTree->getTree() as $value) {
        $categories_array[] = array('id' => $value['id'],
                                    'text' => $value['title']);
      }

      $response = array(EXT_JSON_READER_ROOT => $categories_array);    
                          
      echo $toC_Json->encode($response);
    }
  
    function listCategories() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qcategories = $osC_Database->query('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and cd.language_id = :language_id');
      $Qcategories->appendQuery('and c.parent_id = :parent_id');
      
      if ( isset($_REQUEST['categories_id']) && !empty($_REQUEST['categories_id']) ) {
        $Qcategories->bindInt(':parent_id', $_REQUEST['categories_id']);  
      } else {
        $Qcategories->bindInt(':parent_id', 0);
      }      
      
      if ( isset($_REQUEST['search']) && !empty($_REQUEST['search']) ) {
        $Qcategories->appendQuery('and cd.categories_name like :categories_name');
        $Qcategories->bindValue(':categories_name', '%' . $_REQUEST['search'] . '%');
      } 
    
      $Qcategories->appendQuery('order by c.sort_order, cd.categories_name');
      $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
      $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
      $Qcategories->bindInt(':language_id', $osC_Language->getID());
      $Qcategories->setExtBatchLimit($start, $limit);
      $Qcategories->execute();
      
      $records = array();
      $osC_CategoryTree = new osC_CategoryTree();
      while ($Qcategories->next()) {
        $records[] = array('categories_id' => $Qcategories->value('categories_id'),
                           'categories_name' => $Qcategories->value('categories_name'),
                           'path' => $osC_CategoryTree->buildBreadcrumb($Qcategories->valueInt('categories_id')),); 
      }
        
      $response = array(EXT_JSON_READER_TOTAL => $Qcategories->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records); 
                        
      echo $toC_Json->encode($response);
    }
    
    function listProducts() {
      global $toC_Json, $osC_Database, $osC_Language, $osC_Currencies;
      
      require_once('../includes/classes/currencies.php');
      $osC_Currencies = new osC_Currencies();
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $current_category_id = empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id']; 
      
      if ( $current_category_id > 0 ) {
        $osC_CategoryTree = new osC_CategoryTree_Admin();
        $osC_CategoryTree->setBreadcrumbUsage(false);
    
        $in_categories = array($current_category_id);
    
        foreach($osC_CategoryTree->getTree($current_category_id) as $category) {
          $in_categories[] = $category['id'];
        }
    
        $Qproducts = $osC_Database->query('select distinct p.products_id, p.products_type, pd.products_name, p.products_quantity, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status from :table_products p, :table_products_description pd, :table_products_to_categories p2c where p.products_id = pd.products_id and pd.language_id = :language_id and p.products_id = p2c.products_id and p2c.categories_id in (:categories_id)');
        $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qproducts->bindRaw(':categories_id', implode(',', $in_categories));
      } else {
        $Qproducts = $osC_Database->query('select p.products_id, p.products_type, pd.products_name, p.products_quantity, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status from :table_products p, :table_products_description pd where p.products_id = pd.products_id and pd.language_id = :language_id');
      }
    
      if ( !empty($_REQUEST['search']) ) {
        $Qproducts->appendQuery('and pd.products_name like :products_name');
        $Qproducts->bindValue(':products_name', '%' . $_REQUEST['search'] . '%');
      }
    
      $Qproducts->appendQuery(' order by pd.products_name');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->setExtBatchLimit($start, $limit);
      $Qproducts->execute();
      
      $records = array();
      while ($Qproducts->next()) {
        $products_price = $osC_Currencies->format($Qproducts->value('products_price'));
        
        if ($Qproducts->valueInt('products_type') == PRODUCT_TYPE_GIFT_CERTIFICATE) {
          $Qcertificate = $osC_Database->query('select open_amount_min_value, open_amount_max_value from :table_products_gift_certificates where gift_certificates_amount_type = :gift_certificates_amount_type and products_id = :products_id');
          $Qcertificate->bindTable(':table_products_gift_certificates', TABLE_PRODUCTS_GIFT_CERTIFICATES);
          $Qcertificate->bindInt(':gift_certificates_amount_type', GIFT_CERTIFICATE_TYPE_OPEN_AMOUNT);
          $Qcertificate->bindInt(':products_id', $Qproducts->value('products_id'));
          $Qcertificate->execute();
          
          if ($Qcertificate->numberOfRows() > 0) {
            $products_price = $osC_Currencies->format($Qcertificate->value('open_amount_min_value')) . ' ~ ' . $osC_Currencies->format($Qcertificate->value('open_amount_max_value'));
          }
        }
        
        $records[] = array(
          'products_id'=>$Qproducts->value('products_id'),
          'products_name'=>$Qproducts->value('products_name'),
          'products_price'=> $products_price,
          'products_quantity'=>$Qproducts->value('products_quantity')
        );
      }
  
      $response = array(EXT_JSON_READER_TOTAL => $Qproducts->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);    
    }
  }
?>
