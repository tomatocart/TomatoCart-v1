<?php
/*
  $Id: cart_add.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Actions_cart_add {
    function execute() {
      global $osC_Session, $osC_ShoppingCart, $osC_Language, $messageStack, $toC_Customization_Fields;
      
      //get the product id or product id string including the variants
			$id = false;
			
			//used to fix bug [#209 - Compare / wishlist variant problem]
			if (isset($_GET['pid']) && (preg_match ( '/^[0-9]+(_?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$/', $_GET['pid'] ) || preg_match ( '/^[a-zA-Z0-9 -_]*$/', $_GET['pid']))) {
			  $id = $_GET['pid'];
			}else {
				foreach ( $_GET as $key => $value ) {
					if ((preg_match ( '/^[0-9]+(_?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$/', $key ) || preg_match ( '/^[a-zA-Z0-9 -_]*$/', $key )) && ($key != $osC_Session->getName ())) {
						$id = $key;
					}
				
					break;
				}
			}
			
			//processing variants products
			$variants = null;
			if (strpos ( $id, '_' ) !== false) {
				$id = str_replace ( '_', '#', $id );
				$variants = osc_parse_variants_from_id_string ( $id );
			}
			
			if (($id !== false) && osC_Product::checkEntry ( $id )) {
				$osC_Product = new osC_Product ( $id );
			}

      if (isset($osC_Product)) {
        //customization fields check
        if ($osC_Product->hasRequiredCustomizationFields()) {
          if ( !$toC_Customization_Fields->exists($osC_Product->getID()) ) {
            $osC_Language->load('products');
            
            $messageStack->add_session('products', $osC_Language->get('error_customization_fields_missing'), 'error');
            
            osc_redirect(osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID()));  
          }
        }
        
        $gift_certificate_data = null;
        if($osC_Product->isGiftCertificate() && isset($_POST['senders_name']) && isset($_POST['recipients_name']) && isset($_POST['message'])) {
          if ($osC_Product->isEmailGiftCertificate()) {
            $gift_certificate_data = array('senders_name' => $_POST['senders_name'],
                                           'senders_email' => $_POST['senders_email'],
                                           'recipients_name' => $_POST['recipients_name'],
                                           'recipients_email' => $_POST['recipients_email'],
                                           'message' => $_POST['message']);
          } else {
            $gift_certificate_data = array('senders_name' => $_POST['senders_name'],
                                           'recipients_name' => $_POST['recipients_name'],
                                           'message' => $_POST['message']);
          }
          
          if ($osC_Product->isOpenAmountGiftCertificate()) {
            $gift_certificate_data['price'] = $_POST['gift_certificate_amount']; 
          }
          
          $gift_certificate_data['type'] = $osC_Product->getGiftCertificateType();
        }

        $quantity = null;
        if (isset($_POST['quantity']) && is_numeric($_POST['quantity'])) {
          $quantity = $_POST['quantity'];
        }
        
        if ( $osC_Product->isGiftCertificate() && ($gift_certificate_data == null) ) {
          osc_redirect(osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID()));
          
          return false;
        } else {
          $osC_ShoppingCart->add($osC_Product->getID(), $variants, $quantity, $gift_certificate_data);
        }
      }

      osc_redirect(osc_href_link(FILENAME_CHECKOUT));
    }
  }
?>