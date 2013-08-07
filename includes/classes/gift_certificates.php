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

  class toC_Gift_Certificates {
  
    function isGiftCertificateValid($gift_certificate_code) {
      global $osC_Database;
      
      $Qcertificate = $osC_Database->query('select amount, status from :table_gift_certificates where gift_certificates_code = :gift_certificates_code');
      $Qcertificate->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
      $Qcertificate->bindValue(':gift_certificates_code', $gift_certificate_code);
      $Qcertificate->execute();
      
      if ($Qcertificate->numberOfRows() > 0) {
        //check status
        if ($Qcertificate->valueInt('status') == 1) {
          $Qhistory = $osC_Database->query('select sum(redeem_amount) as redeem_total from :table_gift_certificates c, :table_gift_certificates_redeem_history crh where c.gift_certificates_id = crh.gift_certificates_id and c.gift_certificates_code = :gift_certificates_code');
          $Qhistory->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
          $Qhistory->bindTable(':table_gift_certificates_redeem_history', TABLE_GIFT_CERTIFICATES_REDEEM_HISTORY);
          $Qhistory->bindValue(':gift_certificates_code', $gift_certificate_code);
          $Qhistory->execute();
            
          $certificate_amount = $Qcertificate->value('amount');
          $redeem_total = (int) $Qhistory->value('redeem_total');
            
          if($certificate_amount > $redeem_total) {
            return true;
          }
        } 
      }
       
      return false;
    }
    
    function getGiftCertificateAmount($gift_certificate_code) {
      global $osC_Database, $osC_ShoppingCart;
      
      $Qcertificate = $osC_Database->query('select gift_certificates_id, amount from :table_gift_certificates where gift_certificates_code = :gift_certificates_code');
      $Qcertificate->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
      $Qcertificate->bindValue(':gift_certificates_code', $gift_certificate_code);
      $Qcertificate->execute();
      
      $amount = 0;
      if ($Qcertificate->numberOfRows() == 1) {
        $amount = $Qcertificate->value('amount');
      }
      
      $Qhistory = $osC_Database->query('select * from :table_gift_certificates_redeem_history where gift_certificates_id = :gift_certificates_id');
      $Qhistory->bindTable(':table_gift_certificates_redeem_history', TABLE_GIFT_CERTIFICATES_REDEEM_HISTORY);
      $Qhistory->bindValue(':gift_certificates_id', $Qcertificate->valueInt('gift_certificates_id'));
      
      if (is_object($osC_ShoppingCart) && method_exists($osC_ShoppingCart, 'getOrderID')) {
        $Qhistory->appendQuery('and orders_id <> :orders_id');
        $Qhistory->bindInt(':orders_id', $osC_ShoppingCart->getOrderID());
      }

      $Qhistory->execute();
      
      $redeem_amount = 0;
      while($Qhistory->next()) {
        $redeem_amount += $Qhistory->value('redeem_amount');
      }
      
      
      return ($amount - $redeem_amount);
    }
    
    function createGiftCertificateCode($length = 12) {
      global $osC_Database;

      srand((double) microtime() * 1000000);
      $rand_str = md5(uniqid(rand(), true)) . md5(uniqid(rand(), true)) . md5(uniqid(rand(), true)) . md5(uniqid(rand(), true));

      $gift_certificates_code = '';
      $length = $length - 1;
      $found = true;
      while ($found == true) {
        $random_start = rand(0, (128 - $length));
        $gift_certificates_code = strtoupper(substr($rand_str, $random_start, $length));

        $Qcertificate = $osC_Database->query('select * from :table_gift_certificates where gift_certificates_code = :gift_certificates_code');
        $Qcertificate->bindTable(':table_gift_certificates', TABLE_GIFT_CERTIFICATES);
        $Qcertificate->bindValue(':gift_certificates_code', $gift_certificates_code);
        $Qcertificate->execute();

        if ($Qcertificate->numberOfRows() == 0) {
          $found = false;
        }
      }

      return 'G' . $gift_certificates_code;
    }
  }
?>
