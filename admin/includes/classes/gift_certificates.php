<?php
/*
  $Id: gift_certificates.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
  class toC_GiftCertificates_Admin {
  
    function getData($id) {
      global $osC_Database, $osC_Language;
  
      $Qcertificates = $osC_Database->query('select * from :table_gift_certificates where gift_certificates_id = :gift_certificates_id');
      $Qcertificates->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
      $Qcertificates->bindInt(':gift_certificates_id', $id);
      $Qcertificates->execute();
  
      $data = $Qcertificates->toArray();
  
      $Qcertificates->freeResult();
  
      return $data;
    }
    
    function setStatus($id, $flag){
      global $osC_Database;
      
      $Qstatus = $osC_Database->query('update :table_gift_certificates set status = :gift_certificates_status where gift_certificates_id = :gift_certificates_id');
      $Qstatus->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
      $Qstatus->bindInt(':gift_certificates_status', $flag);
      $Qstatus->bindInt(':gift_certificates_id', $id);
      $Qstatus->setLogging($_SESSION['module'], $id);
      $Qstatus->execute();
  
      if ( !$osC_Database->isError() ) {
        $data = self::getData($id);
        
        if ( ($flag == '1') && ($data['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) ) {
          require_once('includes/classes/currencies.php');  
          $osC_Currencies = new osC_Currencies_Admin();
          
          require_once('../includes/classes/email_template.php');
          $email = toC_Email_Template::getEmailTemplate('active_gift_certificate');
          
          $email->setData($data['senders_name'], $data['senders_email'], $data['recipients_name'], $data['recipients_email'], $osC_Currencies->format($data['amount']), $data['gift_certificates_code'], $data['messages']);
          $email->buildMessage();
          $email->sendEmail();
        }
  
        return true;
      }
                  
      return false;
    }
  }
?>