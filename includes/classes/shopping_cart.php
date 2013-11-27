<?php
/*
  $Id: shopping_cart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_ShoppingCart {
    var $_contents = array(),
        $_sub_total = 0,
        $_total = 0,
        $_weight = 0,
        $_tax = 0,
        $_tax_groups = array(),
        $_is_gift_wrapping = false,
        $_gift_wrapping_message = '',
        $_coupon_code = null,
        $_coupon_amount = 0,
        $_gift_certificate_codes = array(),
        $_gift_certificate_redeem_amount = array(),
        $_content_type,
        $_customer_credit = 0,
        $_use_customer_credit = false,
        $_products_in_stock = true;

    function osC_ShoppingCart() {
      if (!isset($_SESSION['osC_ShoppingCart_data'])) {
        $_SESSION['osC_ShoppingCart_data'] = array('contents' => array(),
                                                   'sub_total_cost' => 0,
                                                   'total_cost' => 0,
                                                   'total_weight' => 0,
                                                   'tax' => 0,
                                                   'is_gift_wrapping' => false,
                                                   'tax_groups' => array(),
                                                   'coupon_code' => null,
                                                   'coupon_amount' => 0,
                                                   'gift_certificate_codes' => array(),
                                                   'gift_certificate_redeem_amount' => array(),
                                                   'customer_credit' => 0,
                                                   'use_customer_credit' => false,
                                                   'shipping_boxes_weight' => 0,
                                                   'shipping_boxes' => 1,
                                                   'shipping_address' => array('zone_id' => STORE_ZONE, 'country_id' => STORE_COUNTRY),
                                                   'shipping_method' => array(),
                                                   'billing_address' => array('zone_id' => STORE_ZONE, 'country_id' => STORE_COUNTRY),
                                                   'billing_method' => array(),
                                                   'shipping_quotes' => array(),
                                                   'order_totals' => array());

        $this->resetShippingAddress();
        $this->resetBillingAddress();
      }

      $this->_contents =& $_SESSION['osC_ShoppingCart_data']['contents'];
      $this->_sub_total =& $_SESSION['osC_ShoppingCart_data']['sub_total_cost'];
      $this->_total =& $_SESSION['osC_ShoppingCart_data']['total_cost'];
      $this->_weight =& $_SESSION['osC_ShoppingCart_data']['total_weight'];
      $this->_tax =& $_SESSION['osC_ShoppingCart_data']['tax'];
      $this->_is_gift_wrapping =& $_SESSION['osC_ShoppingCart_data']['is_gift_wrapping'];
      $this->_tax_groups =& $_SESSION['osC_ShoppingCart_data']['tax_groups'];
      $this->_coupon_code =& $_SESSION['osC_ShoppingCart_data']['coupon_code'];
      $this->_coupon_amount =& $_SESSION['osC_ShoppingCart_data']['coupon_amount'];
      $this->_gift_certificate_codes =& $_SESSION['osC_ShoppingCart_data']['gift_certificate_codes'];
      $this->_gift_certificate_redeem_amount =& $_SESSION['osC_ShoppingCart_data']['gift_certificate_redeem_amount'];
      $this->_customer_credit =& $_SESSION['osC_ShoppingCart_data']['customer_credit'];
      $this->_use_customer_credit =& $_SESSION['osC_ShoppingCart_data']['use_customer_credit'];
      $this->_shipping_boxes_weight =& $_SESSION['osC_ShoppingCart_data']['shipping_boxes_weight'];
      $this->_shipping_boxes =& $_SESSION['osC_ShoppingCart_data']['shipping_boxes'];
      $this->_shipping_address =& $_SESSION['osC_ShoppingCart_data']['shipping_address'];
      $this->_shipping_method =& $_SESSION['osC_ShoppingCart_data']['shipping_method'];
      $this->_billing_address =& $_SESSION['osC_ShoppingCart_data']['billing_address'];
      $this->_billing_method =& $_SESSION['osC_ShoppingCart_data']['billing_method'];
      $this->_shipping_quotes =& $_SESSION['osC_ShoppingCart_data']['shipping_quotes'];
      $this->_order_totals =& $_SESSION['osC_ShoppingCart_data']['order_totals'];
    }

    function update() {
      if ( !isset($_SESSION['cartID']) ) {
        $this->_calculate();
      }
    }

    function hasContents() {
      return !empty($this->_contents);
    }
    
    function synchronizeWithDatabase() {
      global $osC_Database, $osC_Services, $osC_Language, $osC_Customer, $osC_Image;

      if (!$osC_Customer->isLoggedOn()) {
        return false;
      }

// insert current cart contents in database
      if ($this->hasContents()) {
        foreach ($this->_contents as $products_id_string => $data) {
          $Qproduct = $osC_Database->query('select products_id, customers_basket_quantity from :table_customers_basket where customers_id = :customers_id and products_id = :products_id');
          $Qproduct->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
          $Qproduct->bindInt(':customers_id', $osC_Customer->getID());
          $Qproduct->bindValue(':products_id', $products_id_string);
          $Qproduct->execute();

          if ($Qproduct->numberOfRows() > 0) {
            $Qupdate = $osC_Database->query('update :table_customers_basket set customers_basket_quantity = :customers_basket_quantity, gift_certificates_data = :gift_certificates_data, customizations = :customizations where customers_id = :customers_id and products_id = :products_id');
            $Qupdate->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
            $Qupdate->bindInt(':customers_basket_quantity', $data['quantity'] + $Qproduct->valueInt('customers_basket_quantity'));
            
            if ($data['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
              $Qupdate->bindValue(':gift_certificates_data', serialize($data['gc_data']));
            } else {
              $Qupdate->bindRaw(':gift_certificates_data', 'null');
            }
          
            if (isset($data['customizations']) && !empty($data['customizations'])) {
              $Qupdate->bindValue(':customizations', serialize($data['customizations']));
            } else {
              $Qupdate->bindRaw(':customizations', 'null');
            }
            
            $Qupdate->bindInt(':customers_id', $osC_Customer->getID());
            $Qupdate->bindValue(':products_id', $products_id_string);
            $Qupdate->execute();
          } else {
            $Qnew = $osC_Database->query('insert into :table_customers_basket (customers_id, products_id, customers_basket_quantity, gift_certificates_data, customizations, customers_basket_date_added) values (:customers_id, :products_id, :customers_basket_quantity, :gift_certificates_data, :customizations, now())');
            $Qnew->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
            $Qnew->bindInt(':customers_id', $osC_Customer->getID());
            $Qnew->bindValue(':products_id', $products_id_string);
            $Qnew->bindInt(':customers_basket_quantity', $data['quantity']);
            
            if ($data['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
              $Qnew->bindValue(':gift_certificates_data', serialize($data['gc_data']));
            } else {
              $Qnew->bindRaw(':gift_certificates_data', 'null');
            }

            if (isset($data['customizations']) && !empty($data['customizations'])) {
              $Qnew->bindValue(':customizations', serialize($data['customizations']));
            } else {
              $Qnew->bindRaw(':customizations', 'null');
            }
            
            $Qnew->execute();
          }
        }
      }

// reset per-session cart contents, but not the database contents
      $this->reset();

      $Qproducts = $osC_Database->query('select cb.products_id, cb.customers_basket_quantity, cb.customers_basket_date_added, cb.gift_certificates_data, cb.customizations, p.products_price, p.products_tax_class_id, p.products_weight, p.products_weight_class, pd.products_name, pd.products_keyword, i.image from :table_customers_basket cb, :table_products p left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag), :table_products_description pd where cb.customers_id = :customers_id and cb.products_id = p.products_id and p.products_id = pd.products_id and pd.language_id = :language_id order by cb.customers_basket_date_added desc');
      $Qproducts->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':default_flag', 1);
      $Qproducts->bindInt(':customers_id', $osC_Customer->getID());
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->execute();

      while ($Qproducts->next()) {
        $osC_Product = new osC_Product(osc_get_product_id($Qproducts->value('products_id')));
        
        $product = explode('#', $Qproducts->value('products_id'), 2);
        $variants_array = array();

        if (!$osC_Product->isGiftCertificate()) {
          if (isset($product[1])) {
            $variants = explode(';', $product[1]);
  
            foreach ($variants as $set) {
              $variant = explode(':', $set);
  
              if (!is_numeric($variant[0]) || !is_numeric($variant[1])) {
                continue 2; // skip product
              }
  
              $variants_array[$variant[0]] = $variant[1];
            }
          }
        }
        
        $price = $osC_Product->getPrice($variants_array, $Qproducts->value('customers_basket_quantity'));

        if ($osC_Services->isStarted('specials')) {
          global $osC_Specials;

          //support variants specials
          if ($new_price = $osC_Product->getSpecialPrice($variants_array)) {
            $price = $new_price;
          }
        }

        $gc_data = null;
        if ($osC_Product->isGiftCertificate()) {
          $gc_data = unserialize($Qproducts->value('gift_certificates_data'));
          
          if($osC_Product->isOpenAmountGiftCertificate()) {
            $price = $gc_data['price'];
          }
        }
        
        if ( defined('CHECK_STOCKS_SYNCHRONIZE_CART_WITH_DATABASE') && (CHECK_STOCKS_SYNCHRONIZE_CART_WITH_DATABASE == '1') ) {
          $quatities_remained = $osC_Product->getQuantity($Qproducts->value('products_id'));
          
          if ($quatities_remained < 1 || $quatities_remained < $Qproducts->valueInt('customers_basket_quantity')) {
            continue;
          }
        }
        
        $this->_contents[$Qproducts->value('products_id')] = array('id' => $Qproducts->value('products_id'),
                                                                 'name' => $osC_Product->getTitle(),
                                                                 'type' => $osC_Product->getProductType(),
                                                                 'keyword' => $osC_Product->getKeyword(),
                                                                 'sku' => $osC_Product->getSKU($variants_array),
                                                                 'image' => $osC_Product->getImage(),
                                                                 'price' => $price,
                                                                 'final_price' => $price,
                                                                 'quantity' => $Qproducts->valueInt('customers_basket_quantity'),
                                                                 'weight' => $osC_Product->getWeight($variants_array),
                                                                 'tax_class_id' => $osC_Product->getTaxClassID(),
                                                                 'date_added' => osC_DateTime::getShort($Qproducts->value('customers_basket_date_added')),
                                                                 'weight_class_id' => $osC_Product->getWeightClass(),
                                                                 'gc_data' => $gc_data);
        
        $customizations = $Qproducts->value('customizations');
        if (!empty($customizations)) {
          $this->_contents[$Qproducts->value('products_id')]['customizations'] = unserialize($customizations);
        }
        
        
        if (!empty($variants_array)) {
          foreach ($variants_array as $group_id => $value_id) {
            $Qvariants = $osC_Database->query('select pvg.products_variants_groups_name, pvv.products_variants_values_name from :table_products_variants pv, :table_products_variants_entries pve, :table_products_variants_groups pvg, :table_products_variants_values pvv where pv.products_id = :products_id and pv.products_variants_id = pve.products_variants_id and pve.products_variants_groups_id = :groups_id and pve.products_variants_values_id = :variants_values_id and pve.products_variants_groups_id = pvg.products_variants_groups_id and pve.products_variants_values_id = pvv.products_variants_values_id and pvg.language_id = :language_id and pvv.language_id = :language_id');
            $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
            $Qvariants->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
            $Qvariants->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
            $Qvariants->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
            $Qvariants->bindInt(':products_id', osc_get_product_id($Qproducts->value('products_id')));
            $Qvariants->bindInt(':groups_id', $group_id);
            $Qvariants->bindInt(':variants_values_id', $value_id);
            $Qvariants->bindInt(':language_id', $osC_Language->getID());
            $Qvariants->bindInt(':language_id', $osC_Language->getID());
            $Qvariants->execute();

            if ($Qvariants->numberOfRows() > 0) {
              $this->_contents[$Qproducts->value('products_id')]['variants'][$group_id] = array('groups_id' => $group_id,
                                                                                                'variants_values_id' => $value_id,
                                                                                                'groups_name' => $Qvariants->value('products_variants_groups_name'),
                                                                                                'values_name' => $Qvariants->value('products_variants_values_name'));
            } else {
              unset($this->_contents[$Qproducts->value('products_id')]);
              continue 2; // skip product
            }
          }
        }
      }

      $this->_cleanUp();
      $this->_calculate();
    }

    function reset($reset_database = false) {
      global $osC_Database, $osC_Customer;

      if (($reset_database === true) && $osC_Customer->isLoggedOn()) {
        //delete customization files
        $Qcheck = $osC_Database->query('select customizations from :table_customers_basket where customers_id = :customers_id');
        $Qcheck->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
        $Qcheck->bindInt(':customers_id', $osC_Customer->getID());
        $Qcheck->execute();
        
        while($Qcheck->next()) {
          $customizations = $Qcheck->value('customizations');
          if (!empty($customizations)) {
            $customizations = unserialize($customizations);

            foreach ($customizations as $customization) {
              foreach ($customization['fields'] as $field) {
                if ($field['customization_type'] == CUSTOMIZATION_FIELD_TYPE_INPUT_FILE) {
                  if ( file_exists(DIR_FS_CACHE . '/products_customizations/' . $field['cache_filename']) ) {
                    @unlink(DIR_FS_CACHE . '/products_customizations/' . $field['cache_filename']);
                  }
                }
              }
            }
          }
        }
          
        $Qdelete = $osC_Database->query('delete from :table_customers_basket where customers_id = :customers_id');
        $Qdelete->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
        $Qdelete->bindInt(':customers_id', $osC_Customer->getID());
        $Qdelete->execute();
      }

      $this->_contents = array();
      $this->_sub_total = 0;
      $this->_total = 0;
      $this->_weight = 0;
      $this->_tax = 0;
      $this->_tax_groups = array();
      $this->_coupon_code = null;
      $this->_coupon_amount = 0;
      $this->_gift_certificate_codes = array();
      $this->_gift_certificate_redeem_amount = array();
      $this->_customer_credit = 0;
      $this->_use_customer_credit = false;
      $this->_content_type = null;

      $this->resetShippingAddress();
      $this->resetShippingMethod();
      $this->resetBillingAddress();
      $this->resetBillingMethod();

      if ( isset($_SESSION['cartID']) ) {
        unset($_SESSION['cartID']);
      }
    }
    
    function add($products_id_string, $variants = null, $quantity = null, $gift_certificates_data = null, $customization_qty = null, $action = 'add') {
      global $osC_Database, $osC_Services, $osC_Language, $osC_Customer, $osC_Image, $toC_Wishlist, $toC_Customization_Fields;
      
      $products_id = osc_get_product_id($products_id_string);
      $osC_Product = new osC_Product($products_id);

      if ($osC_Product->isGiftCertificate()) {
        if (($variants == null) || empty($variants)) {
          $products_id_string =  $products_id . '#' . time();
        } else {
          $products_id_string =  $products_id . '#' . $variants;
          
          //set variants to null 
          $variants = null;
        }
      } else {
        $products_id_string = osc_get_product_id_string($products_id_string, $variants);
      }
      
      if ($osC_Product->getID() > 0) {
        if ( $toC_Wishlist->hasProduct($products_id) ) {
          $toC_Wishlist->deleteProduct($products_id);
        }
        
        if ($this->exists($products_id_string)) {
          $old_quantity = $this->getQuantity($products_id_string);
          
          if (!is_numeric($quantity)) {
            $quantity = $this->getQuantity($products_id_string) + 1;
          } else if (is_numeric($quantity) && ($quantity == 0)) {
            $this->remove($products_id_string);
            
            return;
          } else {
            if ($action == 'add') {
              $quantity = $this->getQuantity($products_id_string) + $quantity;
            } else if ($action == 'update') {
              $quantity = $quantity;
              
              if ( isset($customization_qty) && !empty($customization_qty) ) {
                foreach($customization_qty as $key => $value) {
                  $this->_contents[$products_id_string]['customizations'][$key]['qty'] = $value;
                }
              }
            }
          }

          if ($osC_Product->isGiftCertificate()) {
            if ($quantity > 1) {
              $quantity = 1;
              
              $error = $osC_Language->get('error_gift_certificate_quantity_must_be_one');
            }
          }
          
          //check minimum order quantity
          $products_moq = $osC_Product->getMOQ();
          if ($quantity < $products_moq) {
            $quantity = $products_moq;
            $error = sprintf($osC_Language->get('error_minimum_order_quantity'), $osC_Product->getTitle(), $products_moq);
          }
          
          //check maximum order quantity
          $products_max_order_quantity = $osC_Product->getMaxOrderQuantity();      
          if ( $products_max_order_quantity > 0 ) {
            if ( $quantity > $products_max_order_quantity ) {
	          $quantity = $products_max_order_quantity;
	          $error = sprintf($osC_Language->get('error_maximum_order_quantity'), $osC_Product->getTitle(), $products_max_order_quantity);
            }
          }
          
          //check order increment
          $increment = $osC_Product->getOrderIncrement();
          if ((($quantity - $products_moq) % $increment) != 0) {
            $quantity = $products_moq + (floor(($quantity - $products_moq) / $increment) + 1) * $increment;
            $error = sprintf($osC_Language->get('error_order_increment'), $osC_Product->getTitle(), $increment);
          }

          //set error to session
          if (isset($error) && !empty($error)) {
            $this->_contents[$products_id_string]['error'] = $error;
          }
          
          if (($osC_Product->isGiftCertificate()) && ($osC_Product->isOpenAmountGiftCertificate())) {
            $price = $this->_contents[$products_id_string]['price']; 
          } else {
            $price = $osC_Product->getPrice($variants, $quantity);
            
            if ($osC_Services->isStarted('specials')) {
              global $osC_Specials;
              
              if ($new_price = $osC_Product->getSpecialPrice($variants)) {
                $price = $new_price;
              }
            }       
          }
            
          $this->_contents[$products_id_string]['quantity'] = $quantity;
          $this->_contents[$products_id_string]['price'] = $price;
          $this->_contents[$products_id_string]['final_price'] = $price;

          if ( $toC_Customization_Fields->exists($products_id) ) {
            $fields = $toC_Customization_Fields->get($products_id);
            $this->_contents[$products_id_string]['customizations'][time()] = array('qty' => ($quantity - $old_quantity), 'fields' => array_values($fields) );
            
            $toC_Customization_Fields->remove($products_id);
          }
          
          // update database
          if ($osC_Customer->isLoggedOn()) {
            $Qupdate = $osC_Database->query('update :table_customers_basket set customers_basket_quantity = :customers_basket_quantity, gift_certificates_data = :gift_certificates_data, customizations = :customizations where customers_id = :customers_id and products_id = :products_id');
            $Qupdate->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
            $Qupdate->bindInt(':customers_basket_quantity', $quantity);
            
            if ($osC_Product->getProductType() == PRODUCT_TYPE_GIFT_CERTIFICATE) {
              $Qupdate->bindValue(':gift_certificates_data', serialize($gift_certificates_data));
            } else {
              $Qupdate->bindRaw(':gift_certificates_data', 'null');
            }
            
            if (isset($this->_contents[$products_id_string]['customizations']) && !empty($this->_contents[$products_id_string]['customizations'])) {
              $Qupdate->bindValue(':customizations', serialize($this->_contents[$products_id_string]['customizations']));
            } else {
              $Qupdate->bindRaw(':customizations', 'null');
            }
            
            $Qupdate->bindInt(':customers_id', $osC_Customer->getID());
            $Qupdate->bindValue(':products_id', $products_id_string);
            $Qupdate->execute();
          }
        } else {
          if (!is_numeric($quantity)) {
            $quantity = 1;
          }

          if ($osC_Product->isGiftCertificate()) {
            if ($quantity > 1) {
              $quantity = 1;
              
              $error = $osC_Language->get('error_gift_certificate_quantity_must_be_one');
            }
          }
          
          //check minimum order quantity
          $products_moq = $osC_Product->getMOQ();
          if ($quantity < $products_moq) {
            $quantity = $products_moq;
            $error = sprintf($osC_Language->get('error_minimum_order_quantity'), $osC_Product->getTitle(), $products_moq);
          }
          
          //check order increment
          $increment = $osC_Product->getOrderIncrement();
          if ((($quantity - $products_moq) % $increment) != 0) {
            $quantity = $products_moq + (floor(($quantity - $products_moq) / $increment) + 1) * $increment;
            $error = sprintf($osC_Language->get('error_order_increment'), $osC_Product->getTitle(), $increment);
          }
          
          //if product has variants and variants is not given
          if ($osC_Product->hasVariants() && ($variants == null)) {
            $variant = $osC_Product->getDefaultVariant();
            $variants = osc_parse_variants_from_id_string($variant['product_id_string']);
          }

          if (($osC_Product->isGiftCertificate()) && ($osC_Product->isOpenAmountGiftCertificate())) {
            $price = $gift_certificates_data['price']; 
          } else {
            $price = $osC_Product->getPrice($variants, $quantity);
            
            if ($osC_Services->isStarted('specials')) {
              global $osC_Specials;
  
              if ($new_price = $osC_Product->getSpecialPrice($variants)) {
                $price = $new_price;
              }
            }          
          }
          
          $this->_contents[$products_id_string] = array('id' => $products_id_string,
                                                        'name' => $osC_Product->getTitle(),
                                                        'type' => $osC_Product->getProductType(),
                                                        'keyword' => $osC_Product->getKeyword(),
                                                        'sku' => $osC_Product->getSKU($variants),
                                                        'image' => $osC_Product->getImage($variants),
                                                        'price' => $price,
                                                        'final_price' => $price,
                                                        'quantity' => $quantity,
                                                        'weight' => $osC_Product->getWeight($variants),
                                                        'tax_class_id' => $osC_Product->getTaxClassID(),
                                                        'date_added' => osC_DateTime::getShort(osC_DateTime::getNow()),
                                                        'weight_class_id' => $osC_Product->getWeightClass(),
                                                        'gc_data' => $gift_certificates_data);
        
          if ( $toC_Customization_Fields->exists($products_id) ) {
            $fields = $toC_Customization_Fields->get($products_id);
            
            $time = time();
            $this->_contents[$products_id_string]['customizations'][$time] = array('qty' => $quantity, 'fields' => array_values($fields));
            
            $toC_Customization_Fields->remove($products_id);
          }
          
          //set error to session
          if (isset($error) && !empty($error)) {
            $this->_contents[$products_id_string]['error'] = $error;
          }

// insert into database
          if ($osC_Customer->isLoggedOn()) {
            $Qnew = $osC_Database->query('insert into :table_customers_basket (customers_id, products_id, customers_basket_quantity, gift_certificates_data, customizations, customers_basket_date_added) values (:customers_id, :products_id, :customers_basket_quantity, :gift_certificates_data, :customizations, now())');
            $Qnew->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
            $Qnew->bindInt(':customers_id', $osC_Customer->getID());
            $Qnew->bindValue(':products_id', $products_id_string);
            $Qnew->bindInt(':customers_basket_quantity', $quantity);
            
            if ($osC_Product->getProductType() == PRODUCT_TYPE_GIFT_CERTIFICATE) {
              $Qnew->bindValue(':gift_certificates_data', serialize($gift_certificates_data));
            } else {
              $Qnew->bindRaw(':gift_certificates_data', 'null');
            }
            
            if (isset($this->_contents[$products_id_string]['customizations']) && !empty($this->_contents[$products_id_string]['customizations'])) {
              $Qnew->bindValue(':customizations', serialize($this->_contents[$products_id_string]['customizations']));
            } else {
              $Qnew->bindRaw(':customizations', 'null');
            }
            
            $Qnew->execute();
          }

          if (is_array($variants) && !empty($variants)) {
            $variants_array = $osC_Product->getVariants();
            $products_variants_id_string = osc_get_product_id_string($products_id_string, $variants);
            $products_variants_id = $variants_array[$products_variants_id_string]['variants_id'];

            $this->_contents[$products_id_string]['products_variants_id'] = $products_variants_id;
            if (isset($variants_array[$products_variants_id_string]['filename']) && !empty($variants_array[$products_variants_id_string]['filename'])) {
              $this->_contents[$products_id_string]['variant_filename'] = $variants_array[$products_variants_id_string]['filename'];
              $this->_contents[$products_id_string]['variant_cache_filename'] = $variants_array[$products_variants_id_string]['cache_filename'];            
            }

            foreach ($variants as $group_id => $value_id) {
              $Qvariants = $osC_Database->query('select pvg.products_variants_groups_name, pvv.products_variants_values_name from :table_products_variants pv, :table_products_variants_entries pve, :table_products_variants_groups pvg, :table_products_variants_values pvv where pv.products_id = :products_id and pv.products_variants_id = pve.products_variants_id and pve.products_variants_groups_id = :groups_id and pve.products_variants_values_id = :variants_values_id and pve.products_variants_groups_id = pvg.products_variants_groups_id and pve.products_variants_values_id = pvv.products_variants_values_id and pvg.language_id = :language_id and pvv.language_id = :language_id');
              $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
              $Qvariants->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
              $Qvariants->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
              $Qvariants->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
              $Qvariants->bindInt(':products_id', $osC_Product->getID());
              $Qvariants->bindInt(':groups_id', $group_id);
              $Qvariants->bindInt(':variants_values_id', $value_id);
              $Qvariants->bindInt(':language_id', $osC_Language->getID());
              $Qvariants->bindInt(':language_id', $osC_Language->getID());
              $Qvariants->execute();

              $this->_contents[$products_id_string]['variants'][$group_id] = array('groups_id' => $group_id,
                                                                                   'variants_values_id' => $value_id,
                                                                                   'groups_name' => $Qvariants->value('products_variants_groups_name'),
                                                                                   'values_name' => $Qvariants->value('products_variants_values_name'));
            }
          }
        }

        $this->_cleanUp();
        $this->_calculate();
      }
    }
      
    function clearError($products_id_string) {
      if (isset($this->_contents[$products_id_string]['error'])) {
        unset($this->_contents[$products_id_string]['error']);
      }
    }
    
    function numberOfPhysicalItems() {
      $total = 0;

      if ($this->hasContents()) {
        foreach ($this->_contents as $product) {
          if(($product['type'] == PRODUCT_TYPE_SIMPLE) || ( ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) && ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_PHYSICAL) )) {
            $total += $product['quantity'];
          }
        }
      }

      return $total;
    }
    
    function numberOfItems() {
      $total = 0;

      if ($this->hasContents()) {
        foreach (array_keys($this->_contents) as $products_id) {
          $total += $this->getQuantity($products_id);
        }
      }

      return $total;
    }

    function getQuantity($products_id) {
      if (isset($this->_contents[$products_id])) {
        return $this->_contents[$products_id]['quantity'];
      }

      return false;
    }

    function exists($products_id) {
      return isset($this->_contents[$products_id]);
    }

    function remove($products_id) {
      global $osC_Database, $osC_Customer, $toC_Customization_Fields;

      if ( isset($this->_contents[$products_id]['customizations']) && !empty($this->_contents[$products_id]['customizations']) ) {
        foreach ($this->_contents[$products_id]['customizations'] as $customization) {
          foreach ($customization['fields'] as $field) {
            if ($field['customization_type'] == CUSTOMIZATION_FIELD_TYPE_INPUT_FILE) {
              if ( file_exists(DIR_FS_CACHE . '/products_customizations/' . $field['cache_filename']) ) {
                @unlink(DIR_FS_CACHE . '/products_customizations/' . $field['cache_filename']);
              }
            }
          }
        }
      }
      
      unset($this->_contents[$products_id]);

// remove from database
      if ($osC_Customer->isLoggedOn()) {
        $Qdelete = $osC_Database->query('delete from :table_customers_basket where customers_id = :customers_id and products_id = :products_id');
        $Qdelete->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
        $Qdelete->bindInt(':customers_id', $osC_Customer->getID());
        $Qdelete->bindValue(':products_id', $products_id);
        $Qdelete->execute();
      }

      $this->_calculate();
    }

    function getProducts() {
      static $_is_sorted = false;

      if ($_is_sorted === false) {
        $_is_sorted = true;

        uasort($this->_contents, array('osC_ShoppingCart', '_uasortProductsByDateAdded'));
      }

      return $this->_contents;
    }

    function getSubTotal() {
      return $this->_sub_total;
    }

    function getTotal() {
      return $this->_total;
    }

    function isTotalZero() {
      return ($this->_total == 0);
    }    
    
    function getWeight() {
      return $this->_weight;
    }

    function generateCartID($length = 5) {
      return osc_create_random_string($length, 'digits');
    }

    function getCartID() {
      return $_SESSION['cartID'];
    }

    function getContentType() {
      global $osC_Database;

      if ( $this->hasContents() ) {
        $products = array_values($this->_contents);
        
        foreach ($products as $product) {
          if (($product['type'] == PRODUCT_TYPE_SIMPLE) || ( ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) && ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_PHYSICAL) )) {
            switch ($this->_content_type) {
              case 'virtual':
                $this->_content_type = 'mixed';
          
                return $this->_content_type;
                break;
              default:
                $this->_content_type = 'physical';
                break;
            }
          } else {
            switch ($this->_content_type) {
              case 'physical':
                $this->_content_type = 'mixed';
          
                return $this->_content_type;
                break;
              default:
                $this->_content_type = 'virtual';
                break;
            }
          }        
        }
      }

      return $this->_content_type;
    }
    
    function isVirtualCart() {
      return ($this->getContentType() == 'virtual');
    }

    function hasVariants($products_id) {
      return isset($this->_contents[$products_id]['variants']) && !empty($this->_contents[$products_id]['variants']);
    }

    function getVariants($products_id) {
      if (isset($this->_contents[$products_id]['variants']) && !empty($this->_contents[$products_id]['variants'])) {
        return $this->_contents[$products_id]['variants'];
      }
    }

    function isInStock($products_id) {
      global $osC_Database;

      $osC_Product = new osC_Product(osc_get_product_id($products_id));
      if (($osC_Product->getQuantity($products_id) - $this->_contents[$products_id]['quantity']) >= 0) {
        return true;
      } elseif ($this->_products_in_stock === true) {
        $this->_products_in_stock = false;
      }

      return false;
    }

    function hasStock() {
      return $this->_products_in_stock;
    }

    function hasShippingAddress() {
      return isset($this->_shipping_address) && isset($this->_shipping_address['id']);
    }

    function setShippingAddress($address_id) {
      global $osC_Database, $osC_Customer;

      $previous_address = false;

      if (isset($this->_shipping_address['id'])) {
        $previous_address = $this->getShippingAddress();
      }
                
      $Qaddress = $osC_Database->query('select ab.entry_gender, ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, ab.entry_telephone, z.zone_code, z.zone_name, ab.entry_country_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format, ab.entry_state, ab.entry_fax from :table_address_book ab left join :table_zones z on (ab.entry_zone_id = z.zone_id) left join :table_countries c on (ab.entry_country_id = c.countries_id) where ab.customers_id = :customers_id and ab.address_book_id = :address_book_id');
      $Qaddress->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
      $Qaddress->bindTable(':table_zones', TABLE_ZONES);
      $Qaddress->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qaddress->bindInt(':customers_id', $osC_Customer->getID());
      $Qaddress->bindInt(':address_book_id', $address_id);
      $Qaddress->execute();

      $this->_shipping_address = array('id' => $address_id,
                                       'gender' => $Qaddress->valueProtected('entry_gender'),
                                       'firstname' => $Qaddress->valueProtected('entry_firstname'),
                                       'lastname' => $Qaddress->valueProtected('entry_lastname'),
                                       'company' => $Qaddress->valueProtected('entry_company'),
                                       'street_address' => $Qaddress->valueProtected('entry_street_address'),
                                       'suburb' => $Qaddress->valueProtected('entry_suburb'),
                                       'city' => $Qaddress->valueProtected('entry_city'),
                                       'postcode' => $Qaddress->valueProtected('entry_postcode'),
                                       'state' => (!osc_empty($Qaddress->valueProtected('entry_state'))) ? $Qaddress->valueProtected('entry_state') : $Qaddress->valueProtected('zone_name'),
                                       'zone_id' => $Qaddress->valueInt('entry_zone_id'),
                                       'zone_code' => $Qaddress->value('zone_code'),
                                       'country_id' => $Qaddress->valueInt('entry_country_id'),
                                       'country_title' => $Qaddress->value('countries_name'),
                                       'country_iso_code_2' => $Qaddress->value('countries_iso_code_2'),
                                       'country_iso_code_3' => $Qaddress->value('countries_iso_code_3'),
                                       'format' => $Qaddress->value('address_format'),
                                       'telephone_number' => $Qaddress->value('entry_telephone'),
                                       'fax' => $Qaddress->value('entry_fax'));

      if ( is_array($previous_address) && ( ($previous_address['id'] != $this->_shipping_address['id']) || ($previous_address['country_id'] != $this->_shipping_address['country_id']) || ($previous_address['zone_id'] != $this->_shipping_address['zone_id']) || ($previous_address['state'] != $this->_shipping_address['state']) || ($previous_address['postcode'] != $this->_shipping_address['postcode']) ) ) {
        $this->_calculate();
      }
    }
    
    function setRawShippingAddress($data) {
      global $osC_Database;

              
      $Qcountries = $osC_Database->query('select countries_name, countries_iso_code_2, countries_iso_code_3, address_format from :table_countries where countries_id = :countries_id');
      $Qcountries->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcountries->bindInt(':countries_id', $data['country_id']);
      $Qcountries->execute();                  

      $state = isset($data['state']) ? $data['state'] : '';
      $zone_code = '';
      $zone_id = isset($data['zone_id']) ? $data['zone_id'] : 0;
      if (!empty($zone_id)) {
        $Qzones = $osC_Database->query('select zone_code, zone_name from :table_zones where zone_id = :zone_id');
        $Qzones->bindTable(':table_zones', TABLE_ZONES);
        $Qzones->bindInt(':zone_id', $zone_id);
        $Qzones->execute();
        
        $state = (!osc_empty($state)) ? $state : $Qzones->valueProtected('zone_name');
        $zone_code = $Qzones->value('zone_code');
        $zone_name = $Qzones->value('zone_name');
      }

      $this->_shipping_address = array('id' => -1,
                                      'gender' => $data['gender'],
                                      'firstname' => $data['firstname'],
                                      'lastname' => $data['lastname'],
                                      'company' => $data['company'],
                                      'street_address' => $data['street_address'],
                                      'suburb' => $data['suburb'],
                                      'city' => $data['city'],
                                      'postcode' => $data['postcode'],
                                      'state' => $state,
                                      'zone_id' => $zone_id,
                                      'zone_code' => $zone_code,
                                      'country_id' => $data['country_id'],
                                      'country_title' => $Qcountries->value('countries_name'),
                                      'country_iso_code_2' => $Qcountries->value('countries_iso_code_2'),
                                      'country_iso_code_3' => $Qcountries->value('countries_iso_code_3'),
                                      'format' => $Qcountries->value('address_format'),
                                      'telephone_number' => $data['telephone'],
                                      'fax' => $data['fax'],
                                      'create_shipping_address' => $data['create_shipping_address']);      
      
      $this->_calculate();
    }
    
    function getShippingAddress($key = '') {
      if (empty($key)) {
        return $this->_shipping_address;
      }

      return $this->_shipping_address[$key];
    }

    function resetShippingAddress() {
      global $osC_Customer;

      $this->_shipping_address = array('zone_id' => STORE_ZONE, 'country_id' => STORE_COUNTRY);

      if ($osC_Customer->isLoggedOn() && $osC_Customer->hasDefaultAddress()) {
        $this->setShippingAddress($osC_Customer->getDefaultAddressID());
      }
    }

    function setShippingMethod($shipping_array, $calculate_total = true) {
      $this->_shipping_method = $shipping_array;

      if ($calculate_total === true) {
        $this->_calculate(false);
      }
    }

    function getShippingMethod($key = '') {
      if (empty($key)) {
        return $this->_shipping_method;
      }

      return $this->_shipping_method[$key];
    }

    function resetShippingMethod() {
      $this->_shipping_method = array();

      $this->_calculate();
    }

    function hasShippingMethod() {
      return !empty($this->_shipping_method);
    }

    function hasBillingAddress() {
      return isset($this->_billing_address) && isset($this->_billing_address['id']);
    }

    function setBillingAddress($address_id) {
      global $osC_Database, $osC_Customer;

      $previous_address = false;

      if (isset($this->_billing_address['id'])) {
        $previous_address = $this->getBillingAddress();
      }
      
      $Qaddress = $osC_Database->query('select ab.entry_gender, ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, ab.entry_telephone, z.zone_code, z.zone_name, ab.entry_country_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format, ab.entry_state, ab.entry_fax from :table_address_book ab left join :table_zones z on (ab.entry_zone_id = z.zone_id) left join :table_countries c on (ab.entry_country_id = c.countries_id) where ab.customers_id = :customers_id and ab.address_book_id = :address_book_id');
      $Qaddress->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
      $Qaddress->bindTable(':table_zones', TABLE_ZONES);
      $Qaddress->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qaddress->bindInt(':customers_id', $osC_Customer->getID());
      $Qaddress->bindInt(':address_book_id', $address_id);
      $Qaddress->execute();

      $this->_billing_address = array('id' => $address_id,
                                      'gender' => $Qaddress->valueProtected('entry_gender'),
                                      'firstname' => $Qaddress->valueProtected('entry_firstname'),
                                      'lastname' => $Qaddress->valueProtected('entry_lastname'),
                                      'company' => $Qaddress->valueProtected('entry_company'),
                                      'street_address' => $Qaddress->valueProtected('entry_street_address'),
                                      'suburb' => $Qaddress->valueProtected('entry_suburb'),
                                      'city' => $Qaddress->valueProtected('entry_city'),
                                      'postcode' => $Qaddress->valueProtected('entry_postcode'),
                                      'state' => (!osc_empty($Qaddress->valueProtected('entry_state'))) ? $Qaddress->valueProtected('entry_state') : $Qaddress->valueProtected('zone_name'),
                                      'zone_id' => $Qaddress->valueInt('entry_zone_id'),
                                      'zone_code' => $Qaddress->value('zone_code'),
                                      'country_id' => $Qaddress->valueInt('entry_country_id'),
                                      'country_title' => $Qaddress->value('countries_name'),
                                      'country_iso_code_2' => $Qaddress->value('countries_iso_code_2'),
                                      'country_iso_code_3' => $Qaddress->value('countries_iso_code_3'),
                                      'format' => $Qaddress->value('address_format'),
                                      'telephone_number' => $Qaddress->value('entry_telephone'),
                                      'fax' => $Qaddress->value('entry_fax'));

	  $address_changed = false;
      if ( is_array($previous_address) && ( ($previous_address['id'] != $this->_billing_address['id']) || ($previous_address['country_id'] != $this->_billing_address['country_id']) || ($previous_address['zone_id'] != $this->_billing_address['zone_id']) || ($previous_address['state'] != $this->_billing_address['state']) || ($previous_address['postcode'] != $this->_billing_address['postcode']) ) ) {
        $address_changed = true;
      }
      
      if ( ( $this->isVirtualCart() && ($previous_address == false) ) || $address_changed) {
        $this->_calculate();
      }
    }
    
    function setRawBillingAddress($data) {
      global $osC_Database;
              
      $Qcountries = $osC_Database->query('select countries_name, countries_iso_code_2, countries_iso_code_3, address_format from :table_countries where countries_id = :countries_id');
      $Qcountries->bindTable(':table_countries', TABLE_COUNTRIES);
      $Qcountries->bindInt(':countries_id', $data['country_id']);
      $Qcountries->execute();                  

      $state = isset($data['state']) ? $data['state'] : '';
      $zone_code = '';
      $zone_id = isset($data['zone_id']) ? $data['zone_id'] : 0;
      if (!empty($zone_id)) {
        $Qzones = $osC_Database->query('select zone_code, zone_name from :table_zones where zone_id = :zone_id');
        $Qzones->bindTable(':table_zones', TABLE_ZONES);
        $Qzones->bindInt(':zone_id', $zone_id);
        $Qzones->execute();
        
        $state = (!osc_empty($state)) ? $state : $Qzones->valueProtected('zone_name');
        $zone_code = $Qzones->value('zone_code');
        $zone_name = $Qzones->value('zone_name');
      }

      $this->_billing_address = array('id' => -1,
                                      'email_address' => $data['email_address'],
                                      'password' => $data['password'],
                                      'gender' => $data['gender'],
                                      'firstname' => $data['firstname'],
                                      'lastname' => $data['lastname'],
                                      'company' => $data['company'],
                                      'street_address' => $data['street_address'],
                                      'suburb' => $data['suburb'],
                                      'city' => $data['city'],
                                      'postcode' => $data['postcode'],
                                      'state' => $state,
                                      'zone_id' => $zone_id,
                                      'zone_code' => $zone_code,
                                      'country_id' => $data['country_id'],
                                      'country_title' => $Qcountries->value('countries_name'),
                                      'country_iso_code_2' => $Qcountries->value('countries_iso_code_2'),
                                      'country_iso_code_3' => $Qcountries->value('countries_iso_code_3'),
                                      'format' => $Qcountries->value('address_format'),
                                      'telephone_number' => $data['telephone'],
                                      'fax' => $data['fax'],
                                      'ship_to_this_address' => $data['ship_to_this_address'],
                                      'create_billing_address' => $data['create_billing_address']);
      
      $this->_calculate();
    }


    function getBillingAddress($key = '') {
      if (empty($key)) {
        return $this->_billing_address;
      }

      return $this->_billing_address[$key];
    }

    function resetBillingAddress() {
      global $osC_Customer;

      $this->_billing_address = array('zone_id' => STORE_ZONE, 'country_id' => STORE_COUNTRY);

      if ($osC_Customer->isLoggedOn() && $osC_Customer->hasDefaultAddress()) {
        $this->setBillingAddress($osC_Customer->getDefaultAddressID());
      }
    }
    
    function setBillingMethod($billing_array) {
      $this->_billing_method = $billing_array;

      $this->_calculate();
    }

    function getBillingMethod($key = '') {
      if (empty($key)) {
        return $this->_billing_method;
      }

      return $this->_billing_method[$key];
    }

    function resetBillingMethod($calculate = true) {
      $this->_billing_method = array();

      if ($calculate == true) {
        $this->_calculate();
      }
    }

    function hasBillingMethod() {
      return !empty($this->_billing_method);
    }
    
    function getCartBillingMethods() {
      global $osC_Language;
      
      $payment_methods = array();
      
      if ($this->isUseStoreCredit()) {
        $payment_methods[] = $osC_Language->get('store_credit_title');
      }
      
      if ($this->hasBillingMethod()) {
        $payment_methods[] = $this->getBillingMethod('title');
      }  
      
      return $payment_methods;
    }

    function getCartBillingModules() {
      $payment_modules = array();
      
      if ($this->isUseStoreCredit()) {
        $payment_modules[] = 'store_credit';
      }
      
      if ($this->hasBillingMethod()) {
        $payment_modules[] = $GLOBALS['osC_Payment_' . $this->getBillingMethod('id')]->getCode();
      }
      
      return $payment_modules;
    }
    
    function getTaxingAddress($id = '') {
      if ($this->getContentType() == 'virtual') {
        return $this->getBillingAddress($id);
      }

      return $this->getShippingAddress($id);
    }

    function addTaxAmount($amount) {
      $this->_tax += $amount;
    }

    function getTax() {
      return $this->_tax;
    }
    
    function numberOfTaxGroups() {
      return sizeof($this->_tax_groups);
    }

    function addTaxGroup($group, $amount) {
      if (isset($this->_tax_groups[$group])) {
        $this->_tax_groups[$group] += $amount;
      } else {
        $this->_tax_groups[$group] = $amount;
      }
    }
    
    function resetGiftCertificates () {
      $this->_gift_certificate_codes = array();
      $this->_gift_certificate_redeem_amount = array();
    }

    function addGiftCertificateCode ($gift_certificate_code) {
      $this->_gift_certificate_codes[] = $gift_certificate_code;
      
      $this->_calculate();
    }
    
    function deleteGiftCertificate ($gift_certificate_code, $caculate = true) {
      foreach ($this->_gift_certificate_codes as $i => $code) {
        if($code == $gift_certificate_code) {
          unset($this->_gift_certificate_codes[$i]);
        }
      }
      
      if ($caculate == true) {
        $this->_calculate();
      }
    }
    
    function getGiftCertificateCodes() {
      return $this->_gift_certificate_codes;
    }
    
    function hasGiftCertificate() {
      return !empty($this->_gift_certificate_codes);
    }
    
    function containsGiftCertifcate($gift_certificate_code) {
      return in_array($gift_certificate_code, $this->_gift_certificate_codes);
    }
    
    function setGiftCertificateRedeemAmount($gift_certificate_code, $amount) {
      $this->_gift_certificate_redeem_amount[$gift_certificate_code] = $amount;
    }
    
    function getGiftCertificateRedeemAmount($gift_certificate_code = '') {
      if ( !empty($gift_certificate_code) ) {
        return $this->_gift_certificate_redeem_amount[$gift_certificate_code];
      } 
      
      return $this->_gift_certificate_redeem_amount;
    }
    
    function setCouponCode($coupon_code, $calculate = true) {
      $this->_coupon_code = $coupon_code;
      
      if ($calculate)
      	$this->_calculate();
    }
      
    function getCouponCode() {
      return $this->_coupon_code;
    }
    
    function hasCoupon() {
      return !empty($this->_coupon_code);
    }
    
    function deleteCoupon() {
      $this->_coupon_code = null;
      
      $this->_calculate();
    }

    function setCouponAmount($amount) {
      $this->_coupon_amount = $amount;
    }

    function getCouponAmount() {
      return $this->_coupon_amount;
    }
    
    function isUseStoreCredit() {
      return $this->_use_customer_credit;
    }
    
    function setUseStoreCredit($use_store_credit) {
      $this->_use_customer_credit = $use_store_credit;
      
      $this->_calculate();
    }
      
    function setStoreCredit($store_credit) {
      $this->_customer_credit = $store_credit;
    }

    function getStoreCredit() {
      return $this->_customer_credit;
    }
    
    function addToTotal($amount) {
      $this->_total += $amount;
    }
    
    function getOrderTotals() {
      return $this->_order_totals;
    }

    function setGiftWrapping($gift_wrapping) {
      $this->_is_gift_wrapping = $gift_wrapping;
    }
    
    function isGiftWrapping() {
      return $this->_is_gift_wrapping;
    }
    
    function getShippingBoxesWeight() {
      return $this->_shipping_boxes_weight;
    }

    function numberOfShippingBoxes() {
      return $this->_shipping_boxes;
    }

    function _cleanUp() {
      global $osC_Database, $osC_Customer;

      foreach ($this->_contents as $product_id_string => $data) {
        if ($data['quantity'] < 1) {
          //delete customization files
          if ( isset($this->_contents[$products_id]['customizations']) && !empty($this->_contents[$products_id]['customizations']) ) {
            foreach ($this->_contents[$products_id]['customizations'] as $customization) {
              foreach ($customization['fields'] as $field) {
                if ($field['customization_type'] == CUSTOMIZATION_FIELD_TYPE_INPUT_FILE) {
                  if ( file_exists(DIR_FS_CACHE . '/products_customizations/' . $field['cache_filename']) ) {
                    @unlink(DIR_FS_CACHE . '/products_customizations/' . $field['cache_filename']);
                  }
                }
              }
            }
          }
        
          unset($this->_contents[$product_id_string]);

// remove from database
          if ($osC_Customer->isLoggedOn()) {
            $Qdelete = $osC_Database->query('delete from :table_customers_basket where customers_id = :customers_id and products_id = :products_id');
            $Qdelete->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
            $Qdelete->bindInt(':customers_id', $osC_Customer->getID());
            $Qdelete->bindValue(':products_id', $product_id_string);
            $Qdelete->execute();
          }
        }
      }
    }

    function _calculate($set_shipping = true) {
      global $osC_Currencies, $osC_Tax, $osC_Weight, $osC_Shipping, $osC_OrderTotal;

      $this->_sub_total = 0;
      $this->_total = 0;
      $this->_weight = 0;
      $this->_tax = 0;
      $this->_tax_groups = array();
      $this->_shipping_boxes_weight = 0;
      $this->_shipping_boxes = 0;
      $this->_shipping_quotes = array();
      $this->_order_totals = array();

      $_SESSION['cartID'] = $this->generateCartID();

      if ($this->hasContents()) {
        foreach ($this->_contents as $data) {
          if(($data['type'] == PRODUCT_TYPE_SIMPLE) || ( ($data['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) && ($data['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_PHYSICAL) )) {
            $products_weight = $osC_Weight->convert($data['weight'], $data['weight_class_id'], SHIPPING_WEIGHT_UNIT);
            $this->_weight += $products_weight * $data['quantity'];
          }

          $tax = $osC_Tax->getTaxRate($data['tax_class_id'], $this->getTaxingAddress('country_id'), $this->getTaxingAddress('zone_id'));
          $tax_description = $osC_Tax->getTaxRateDescription($data['tax_class_id'], $this->getTaxingAddress('country_id'), $this->getTaxingAddress('zone_id'));

          $shown_price = $osC_Currencies->addTaxRateToPrice($data['final_price'], $tax, $data['quantity']);

          $this->_sub_total += $shown_price;
          $this->_total += $shown_price;

          if (DISPLAY_PRICE_WITH_TAX == '1') {
            $tax_amount = $shown_price - ($shown_price / (($tax < 10) ? '1.0' . str_replace('.', '', $tax) : '1.' . str_replace('.', '', $tax)));
          } else {
            $tax_amount = ($tax / 100) * $shown_price;

            //oscommerce 3 bug, no matter the tax is displayed or not, tax should not be add to total
            $this->_total += $tax_amount;
          }

          $this->_tax += $tax_amount;

          if (isset($this->_tax_groups[$tax_description])) {
            $this->_tax_groups[$tax_description] += $tax_amount;
          } else {
            $this->_tax_groups[$tax_description] = $tax_amount;
          }
        }

        $this->_shipping_boxes_weight = $this->_weight;
        $this->_shipping_boxes = 1;

        if (SHIPPING_BOX_WEIGHT >= ($this->_shipping_boxes_weight * SHIPPING_BOX_PADDING/100)) {
          $this->_shipping_boxes_weight = $this->_shipping_boxes_weight + SHIPPING_BOX_WEIGHT;
        } else {
          $this->_shipping_boxes_weight = $this->_shipping_boxes_weight + ($this->_shipping_boxes_weight * SHIPPING_BOX_PADDING/100);
        }

        if ($this->_shipping_boxes_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
          $this->_shipping_boxes = ceil($this->_shipping_boxes_weight / SHIPPING_MAX_WEIGHT);
          $this->_shipping_boxes_weight = $this->_shipping_boxes_weight / $this->_shipping_boxes;
        }

        if ($set_shipping === true) {
          if (!class_exists('osC_Shipping')) {
            include('includes/classes/shipping.php');
          }

          if (!$this->isVirtualCart()) {
            $osC_Shipping = new osC_Shipping($this->getShippingMethod('id'));
            $this->setShippingMethod($osC_Shipping->getQuote(), false);
          } else {
            //reset shipping address and shipping method
            $this->_shipping_address = array();
            $this->_shipping_method = array();
          }
        }
        
        if (!class_exists('osC_OrderTotal')) {
          include('includes/classes/order_total.php');
        }
        
        $osC_OrderTotal = new osC_OrderTotal();
        $this->_order_totals = $osC_OrderTotal->getResult();
      }
    }

    function _uasortProductsByDateAdded($a, $b) {
      if ($a['date_added'] == $b['date_added']) {
        return strnatcasecmp($a['name'], $b['name']);
      }

      return ($a['date_added'] > $b['date_added']) ? -1 : 1;
    }
  }
?>