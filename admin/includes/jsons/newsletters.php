<?php
/*
  $Id: newsletters.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/newsletters.php');

  class toC_Json_Newsletters {
        
    function listNewsletters() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];       
      
      $Qnewsletters = $osC_Database->query('select newsletters_id, title, length(content) as content_length, module, date_added, date_sent, status, locked from :table_newsletters order by date_added desc');
      $Qnewsletters->bindTable(':table_newsletters', TABLE_NEWSLETTERS);
      $Qnewsletters->setExtBatchLimit($start, $limit);
      $Qnewsletters->execute();
        
      $records = array();     
      while ( $Qnewsletters->next() ) {
        $newsletter_module_class = 'osC_Newsletter_' . $Qnewsletters->value('module');

        if ( !class_exists($newsletter_module_class) ) {
          $osC_Language->loadIniFile('modules/newsletters/' . $Qnewsletters->value('module') . '.php');
          include('includes/modules/newsletters/' . $Qnewsletters->value('module') . '.php');
    
          $$newsletter_module_class = new $newsletter_module_class();
        }
        
        $status = $Qnewsletters->valueInt('status');
        
        $action = array();
        if ($status === 1) {
          $sent = '<img src= "templates/default/images/icons/16x16/checkbox_ticked.gif" />';
          $action[] = array('class' => 'icon-empty-record', 'qtip' => '');
          $action[] = array('class' => 'icon-log-record', 'qtip' => $osC_Language->get('icon_log'));
          $action[] = array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash'));
        }
        else {
          $sent = '<img src= "templates/default/images/icons/16x16/checkbox_crossed.gif" />';
          $action[] = array('class' => 'icon-edit-record', 'qtip' => $osC_Language->get('icon_edit'));
          $action[] = array('class' => 'icon-send-email-record', 'qtip' => $osC_Language->get('icon_email_send'));
          $action[] = array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash'));
        }
        
        $records[] = array(
          'newsletters_id' => $Qnewsletters->valueInt('newsletters_id'),
          'title' =>  $Qnewsletters->value('title'),
          'size' => $Qnewsletters->valueInt('content_length'),
          'module' => $Qnewsletters->value('module'),
          'sent' => $sent,
          'action' => $action
        );           
      }
      $Qnewsletters->freeResult();         
       
      $response = array(EXT_JSON_READER_TOTAL => $Qnewsletters->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }          
    
   function getModules() {
      global $toC_Json, $osC_Language;
      
      $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/newsletters');
      $osC_DirectoryListing->setIncludeDirectories(false);
      
      $records = array();
      foreach ( $osC_DirectoryListing->getFiles() as $file ) {
        $module = substr($file['name'], 0, strrpos($file['name'], '.'));
        
        $osC_Language->loadIniFile('modules/newsletters/' . $file['name']);
        include('includes/modules/newsletters/' . $file['name']);
        
        $newsletter_module_class = 'osC_Newsletter_' . $module;
        $osC_NewsletterModule = new $newsletter_module_class();
        
        $records[] = array('id' => $module,
                           'text' => $osC_NewsletterModule->getTitle());
      }

      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function deleteNewsletter() {
      global $toC_Json, $osC_Language;
      
      if ( !osC_Newsletters_Admin::delete($_REQUEST['newsletters_id']) ) {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed')); 
      }else {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteNewsletters() {
      global $toC_Json, $osC_Language;
     
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if (!osC_Newsletters_Admin::delete($id)) {
          $error = true;
          break;
        }
      }
       
      if ($error === false) {      
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      }else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
       
      echo $toC_Json->encode($response);               
    }       
    
    function loadNewsletter() {
      global $toC_Json;
     
      $data = osC_Newsletters_Admin::getData($_REQUEST['newsletters_id']);
      $data['newsletter_module'] = $data['module'];
      
      $response = array('success' => true, 'data' => $data);
       
      echo $toC_Json->encode($response);    
    }
   
    function saveNewsletter() {
      global $toC_Json, $osC_Language;
      
      $data = array('title' => $_REQUEST['title'],
                    'content' => $_REQUEST['content'],
                    'module' => $_REQUEST['newsletter_module']);
                   
      if ( osC_Newsletters_Admin::save((isset($_REQUEST['newsletters_id']) && is_numeric($_REQUEST['newsletters_id'])) ? $_REQUEST['newsletters_id'] : null, $data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      }else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
     
      echo $toC_Json->encode($response);
    }
        
    function getEmailsAudience() {
      global $toC_Json, $osC_Language, $osC_Database;

      $osC_Language->loadIniFile('modules/newsletters/email.php');
      
      $customers_array = array(array('id' => '***',
                                     'text' => $osC_Language->get('newsletter_email_all_customers')));

      $Qcustomers = $osC_Database->query('select customers_id, customers_firstname, customers_lastname, customers_email_address from :table_customers order by customers_lastname');
      $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomers->execute();

      while ( $Qcustomers->next() ) {
        $customers_array[] = array('id' => $Qcustomers->valueInt('customers_id'),
                                   'text' => $Qcustomers->value('customers_lastname') . ', ' . $Qcustomers->value('customers_firstname') . ' (' . $Qcustomers->value('customers_email_address') . ')');
      }
      $Qcustomers->freeResult();

      $response = array(EXT_JSON_READER_ROOT => $customers_array);
      
      echo $toC_Json->encode($response);
    }

    function getEmailsConfirmation() {
      global $toC_Json, $osC_Language, $osC_Database;

      $osC_Language->loadIniFile('modules/newsletters/email.php');
      
      $confirmation_string = '';
      $audience_size = 0;
            
      if ( isset($_REQUEST['batch']) && !empty($_REQUEST['batch']) ) {
        $Qcustomers = $osC_Database->query('select count(customers_id) as total from :table_customers c left join :table_newsletters_log nl on (c.customers_email_address = nl.email_address and nl.newsletters_id = :newsletters_id) where nl.email_address is null');
        $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomers->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
        $Qcustomers->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);

        $customers = explode(',', $_REQUEST['batch']);
        if( !in_array('***', $customers) ){
          $Qcustomers->appendQuery(' and c.customers_id in (":customers_id") ');
          $Qcustomers->bindRaw(':customers_id', implode('", "', array_unique(array_filter(array_slice($customers, 0, MAX_DISPLAY_SEARCH_RESULTS), 'is_numeric'))));
        }
        $Qcustomers->execute();

        $audience_size = $Qcustomers->valueInt('total');
        $email = osC_Newsletters_Admin::getData($_REQUEST['newsletters_id']);
        
        $confirmation_string = '<p><font color="#ff0000"><b>' . sprintf($osC_Language->get('newsletter_email_total_recipients'), $audience_size) . '</b></font></p>' .
                               '<p><b>' . $email['title'] . '</b></p>' .
                               '<p>' . nl2br($email['content']) . '</p>';
      }
      
      $response = array('success' => true, 'confirmation' => $confirmation_string);
      
      echo $toC_Json->encode($response);
    }    
      
    function sendEmails() {
      global $toC_Json, $osC_Database, $osC_Language;

      $email = osC_Newsletters_Admin::getData($_REQUEST['newsletters_id']);
      
      $audience = array();
      if (!empty($_REQUEST['newsletters_id'])) {
        
        $Qcustomers = $osC_Database->query('select c.customers_id, c.customers_firstname, c.customers_lastname, c.customers_email_address from :table_customers c left join :table_newsletters_log nl on (c.customers_email_address = nl.email_address and nl.newsletters_id = :newsletters_id) where nl.email_address is  null');
        $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomers->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
        $Qcustomers->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);

        $customers = explode(',', $_REQUEST['batch']);
        if( !in_array('***', $customers) ){
          $Qcustomers->appendQuery(' and c.customers_id in (":customers_id") ');
          $Qcustomers->bindRaw(':customers_id', implode('", "', array_unique(array_filter(array_slice($customers, 0, MAX_DISPLAY_SEARCH_RESULTS), 'is_numeric'))));
        }
        $Qcustomers->execute();
        
        while ($Qcustomers->next()) {
          if (!isset($audience[$Qcustomers->valueInt('customers_id')])) {
            $audience[$Qcustomers->valueInt('customers_id')] = array('firstname' => $Qcustomers->value('customers_firstname'),
                                                                     'lastname' => $Qcustomers->value('customers_lastname'),
                                                                     'email_address' => $Qcustomers->value('customers_email_address'));
          }
        }
        $Qcustomers->freeResult();

        if (sizeof($audience) > 0) {
         
          $osC_Mail = new osC_Mail(null, null, null, EMAIL_FROM, $email['title']);
          $osC_Mail->setBodyHTML($email['content']);

          foreach ($audience as $key => $value) {
            $osC_Mail->clearTo();
            $osC_Mail->addTo($value['firstname'] . ' ' . $value['lastname'], $value['email_address']);
            $osC_Mail->send();
            
            $Qlog = $osC_Database->query('insert into :table_newsletters_log (newsletters_id, email_address, date_sent) values (:newsletters_id, :email_address, now())');
            $Qlog->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
            $Qlog->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);
            $Qlog->bindValue(':email_address', $value['email_address']);
            $Qlog->execute();
          }
        }

        $Qupdate = $osC_Database->query('update :table_newsletters set date_sent = now(), status = 1 where newsletters_id = :newsletters_id');
        $Qupdate->bindTable(':table_newsletters', TABLE_NEWSLETTERS);
        $Qupdate->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);
        $Qupdate->execute();
      }
      
      $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      
      echo $toC_Json->encode($response);
    }    
        
    function getProducts() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $Qproducts = $osC_Database->query('select pd.products_id, pd.products_name from :table_products p, :table_products_description pd where pd.language_id = :language_id and pd.products_id = p.products_id and p.products_status = 1 order by pd.products_name');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->execute();

      $products = array();
      while ($Qproducts->next()) {
        $products[] = array('id' => $Qproducts->valueInt('products_id'),
                            'text' => $Qproducts->value('products_name'));
      }
      $Qproducts->freeResult();

      $response = array(EXT_JSON_READER_ROOT => $products); 

      echo $toC_Json->encode($response);
    }
  
    function getProductNotificationsConfirmation() {
      global $toC_Json, $osC_Language, $osC_Database;

      $osC_Language->loadIniFile('modules/newsletters/product_notification.php');
            
      $email = osC_Newsletters_Admin::getData($_REQUEST['newsletters_id']);
      
      $confirmation_string = '';
      $audience_size = 0;
      
      if ( (isset($_REQUEST['batch']) && !empty($_REQUEST['batch'])) || (isset($_REQUEST['global']) && ($_REQUEST['global'] == 'true')) ) {
        $Qcustomers = $osC_Database->query('select count(customers_id) as total from :table_customers where global_product_notifications = 1');
        $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomers->execute();

        $audience_size = $Qcustomers->valueInt('total');

        $Qcustomers = $osC_Database->query('select count(distinct pn.customers_id) as total from :table_products_notifications pn, :table_customers c left join :table_newsletters_log nl on (c.customers_email_address = nl.email_address and nl.newsletters_id = :newsletters_id) where pn.customers_id = c.customers_id and nl.email_address is null');
        $Qcustomers->bindTable(':table_products_notifications', TABLE_PRODUCTS_NOTIFICATIONS);
        $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomers->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
        $Qcustomers->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);

        if ( isset($_REQUEST['batch']) && !empty($_REQUEST['batch']) ) {
          $Qcustomers->appendQuery('and pn.products_id in (:products_id)');
          $Qcustomers->bindRaw(':products_id', $_REQUEST['batch']);
        }

        $Qcustomers->execute();

        $audience_size += $Qcustomers->valueInt('total');
      
        $confirmation_string = '<p><font color="#ff0000"><b>' . sprintf($osC_Language->get('newsletter_product_notifications_total_recipients'), $audience_size) . '</b></font></p>' .
                               '<p><b>' . $email['title'] . '</b></p>' .
                               '<p>' . nl2br($email['content']) . '</p>';          
      }
      
      $response = array('success' => true, 'execute' => ($audience_size > 0 ? true : false), 'confirmation' => $confirmation_string);
      
      echo $toC_Json->encode($response);
    }    

    function sendProductNotifications() {
      global $toC_Json, $osC_Database, $osC_Language;

      $max_execution_time = 0.8 * (int) ini_get('max_execution_time');
      $time_start = explode(' ', PAGE_PARSE_START_TIME);
      
      $email = osC_Newsletters_Admin::getData($_REQUEST['newsletters_id']);
      $error = false;

      $audience = array();
      if ( (isset($_REQUEST['batch']) && !empty($_REQUEST['batch'])) || (isset($_REQUEST['global']) && ($_REQUEST['global'] == 'true')) ) {
        $Qcustomers = $osC_Database->query('select customers_id, customers_firstname, customers_lastname, customers_email_address from :table_customers where global_product_notifications = 1');
        $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomers->execute();

        while ($Qcustomers->next()) {
          if (!isset($audience[$Qcustomers->valueInt('customers_id')])) {
            $audience[$Qcustomers->valueInt('customers_id')] = array('firstname' => $Qcustomers->value('customers_firstname'),
                                                                     'lastname' => $Qcustomers->value('customers_lastname'),
                                                                     'email_address' => $Qcustomers->value('customers_email_address'));
          }
        }        
        
        $Qcustomers = $osC_Database->query('select distinct pn.customers_id, c.customers_firstname, c.customers_lastname, c.customers_email_address from :table_products_notifications pn, :table_customers c left join :table_newsletters_log nl on (c.customers_email_address = nl.email_address and nl.newsletters_id = :newsletters_id) where pn.customers_id = c.customers_id and nl.email_address is null');
        $Qcustomers->bindTable(':table_products_notifications', TABLE_PRODUCTS_NOTIFICATIONS);
        $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomers->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
        $Qcustomers->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);

        if ( isset($_REQUEST['batch']) && !empty($_REQUEST['batch']) ) {
          $Qcustomers->appendQuery('and pn.products_id in (:products_id)');
          $Qcustomers->bindRaw(':products_id', $_REQUEST['batch']);
        }
        $Qcustomers->execute();

        while ($Qcustomers->next()) {
          if (!isset($audience[$Qcustomers->valueInt('customers_id')])) {
            $audience[$Qcustomers->valueInt('customers_id')] = array('firstname' => $Qcustomers->value('customers_firstname'),
                                                                     'lastname' => $Qcustomers->value('customers_lastname'),
                                                                     'email_address' => $Qcustomers->value('customers_email_address'));
          }
        }
        
        if (sizeof($audience) > 0) {
          $osC_Mail = new osC_Mail(null, null, null, EMAIL_FROM, $email['title']);
          $osC_Mail->setBodyHTML($email['content']);
  
          foreach ($audience as $key => $value) {
            $osC_Mail->clearTo();
            $osC_Mail->addTo($value['firstname'] . ' ' . $value['lastname'], $value['email_address']);
            $osC_Mail->send();
  
            $Qlog = $osC_Database->query('insert into :table_newsletters_log (newsletters_id, email_address, date_sent) values (:newsletters_id, :email_address, now())');
            $Qlog->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
            $Qlog->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);
            $Qlog->bindValue(':email_address', $value['email_address']);
            $Qlog->execute();
  
            $time_end = explode(' ', microtime());
            $timer_total = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
  
            if ( $timer_total > $max_execution_time ) {
              $error === true;
              break;
            }
          }
        }        
      }      
      
      if ($error === false) {
        $Qupdate = $osC_Database->query('update :table_newsletters set date_sent = now(), status = 1 where newsletters_id = :newsletters_id');
        $Qupdate->bindTable(':table_newsletters', TABLE_NEWSLETTERS);
        $Qupdate->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);
        $Qupdate->execute();
        
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      }else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function getNewslettersConfirmation() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $osC_Language->loadIniFile('modules/newsletters/newsletter.php');
      
      $email = osC_Newsletters_Admin::getData($_REQUEST['newsletters_id']);
      
      $Qrecipients = $osC_Database->query('select count(*) as total from :table_customers c left join :table_newsletters_log nl on (c.customers_email_address = nl.email_address and nl.newsletters_id = :newsletters_id) where c.customers_newsletter = 1 and nl.email_address is null');
      $Qrecipients->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qrecipients->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
      $Qrecipients->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);
      $Qrecipients->execute();

      $audience_size = $Qrecipients->valueInt('total');
      
      $confirmation_string = '<p><font color="#ff0000"><b>' . sprintf($osC_Language->get('newsletter_newsletter_total_recipients'), $audience_size) . '</b></font></p>' .
                             '<p><b>' . $email['title'] . '</b></p>' .
                             '<p>' . nl2br($email['content']) . '</p>';
      
      $response = array('success' => true, 'execute' => ($audience_size > 0 ? true : false), 'confirmation' => $confirmation_string);
      
      echo $toC_Json->encode($response);
    }
    
    function sendNewsletters() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $time_start = explode(' ', PAGE_PARSE_START_TIME);
      $max_execution_time = 0.8 * (int)ini_get('max_execution_time');
      
      $email = osC_Newsletters_Admin::getData($_REQUEST['newsletters_id']);
      
      $error = false;
     
      $Qrecipients = $osC_Database->query('select c.customers_firstname, c.customers_lastname, c.customers_email_address from :table_customers c left join :table_newsletters_log nl on (c.customers_email_address = nl.email_address and nl.newsletters_id = :newsletters_id) where c.customers_newsletter = 1 and nl.email_address is null');
      $Qrecipients->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qrecipients->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
      $Qrecipients->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);
      $Qrecipients->execute();

      if ( $Qrecipients->numberOfRows() > 0 ) {
        $osC_Mail = new osC_Mail(null, null, null, EMAIL_FROM, $email['title']);
        $osC_Mail->setBodyHTML($email['content']);

        while ( $Qrecipients->next() ) {
          $osC_Mail->clearTo();
          $osC_Mail->addTo($Qrecipients->value('customers_firstname') . ' ' . $Qrecipients->value('customers_lastname'), $Qrecipients->value('customers_email_address'));
          $osC_Mail->send();

          $Qlog = $osC_Database->query('insert into :table_newsletters_log (newsletters_id, email_address, date_sent) values (:newsletters_id, :email_address, now())');
          $Qlog->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
          $Qlog->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);
          $Qlog->bindValue(':email_address', $Qrecipients->value('customers_email_address'));
          $Qlog->execute();

          $time_end = explode(' ', microtime());
          $timer_total = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);

          if ( $timer_total > $max_execution_time ) {
            $error === true;
          }
        }
        $Qrecipients->freeResult();
      }
      
      if ($error === false) {
        $Qupdate = $osC_Database->query('update :table_newsletters set date_sent = now(), status = 1 where newsletters_id = :newsletters_id');
        $Qupdate->bindTable(':table_newsletters', TABLE_NEWSLETTERS);
        $Qupdate->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);
        $Qupdate->execute();
        
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      }else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function listLog() {
      global $toC_Json, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qlog = $osC_Database->query('select email_address, date_sent from :table_newsletters_log where newsletters_id = :newsletters_id order by date_sent desc');
      $Qlog->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
      $Qlog->bindInt(':newsletters_id', $_REQUEST['newsletters_id']);
      $Qlog->setExtBatchLimit($start, $limit);
      $Qlog->execute();
      
      $records = array();     
      while ( $Qlog->next() ) {
        $records[] = array('email_address' => $Qlog->valueProtected('email_address'), 
                           'sent' => osc_icon(!osc_empty($Qlog->value('date_sent')) ? 'checkbox_ticked.gif' : 'checkbox_crossed.gif', null, null), 
                           'date_sent' => osC_DateTime::getShort($Qlog->value('date_sent'), true));
      }
      $Qlog->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => $Qlog->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }
  }
?>
