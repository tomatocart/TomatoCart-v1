<?php
/*
  $Id: wishlist.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

	class toC_Wishlist {
	  var $_contents = array(),
	      $_wishlists_id = null,
	      $_token = null;
	      
	  function toC_Wishlist() {
	    if (!isset($_SESSION['toC_Wishlist_data'])) {
	      $_SESSION['toC_Wishlist_data'] = array('contents' => array(), 
	                                             'wishlists_id' => null, 
	                                             'token' => null);
	    }
	    
	    $this->_contents =& $_SESSION['toC_Wishlist_data']['contents'];
	    $this->_wishlists_id =& $_SESSION['toC_Wishlist_data']['wishlists_id'];
	    $this->_token =& $_SESSION['toC_Wishlist_data']['token'];
	  }
	  
	  function exists($products_id) {
	    return isset($this->_contents[$products_id]);	  
	  }
	  
	  function hasContents() {
	    return !empty($this->_contents);
	  }

	  function hasWishlistID() {
      return !empty($this->_wishlists_id);
    }
    
    function getToken() {
      return $this->_token;
    }
    	  
	  function reset() {
      $this->_wishlists_id = null;
      $this->_token = null;
	    $this->_contents = array();
    }
    
    function generateToken() {
      global $osC_Customer, $osC_Session;
      
      $token = md5($osC_Customer->getID() . time());
      
      return $token;
    }
    
	  function synchronizeWithDatabase() {
      global $osC_Database, $osC_Services, $osC_Language, $osC_Customer, $osC_Image;

      if (!$osC_Customer->isLoggedOn()) {
        return false;
      }

      $Qcheck = $osC_Database->query('select wishlists_id, wishlists_token from :table_wishlists where customers_id = :customers_id');
      $Qcheck->bindTable(':table_wishlists', TABLE_WISHLISTS);
      $Qcheck->bindInt(':customers_id', $osC_Customer->getID());
      $Qcheck->execute();
      
	    if ($Qcheck->numberOfRows() > 0) {
        $this->_wishlists_id = $Qcheck->valueInt('wishlists_id');
        $this->_token = $Qcheck->value('wishlists_token');
        
  	    // reset per-session cart contents, but not the database contents
        $this->_contents = array();
  
        $Qproducts = $osC_Database->query('select wishlists_products_id, products_id, date_added, comments from :table_wishlist_products where wishlists_id = :wishlists_id');
        $Qproducts->bindTable(':table_wishlist_products', TABLE_WISHLISTS_PRODUCTS);
        $Qproducts->bindInt(':wishlists_id', $this->_wishlists_id);
        $Qproducts->execute();      
        
        while ($Qproducts->next()) {
          $osC_Product = new osC_Product($Qproducts->value('products_id'));
          
          $product_price = $osC_Product->getPrice();
          $product_name = $osC_Product->getTitle();
          $product_image = $osC_Product->getImage();
  
          if ($osC_Services->isStarted('specials')) {
            global $osC_Specials;
  
            if ($new_price = $osC_Specials->getPrice(osc_get_product_id($Qproducts->value('products_id')))) {
              $price = $new_price;
            }
          }
          
          //the product has variants
          $variants = array();
          if ($osC_Product->hasVariants()) {
            $Qvariants = $osC_Database->query('select products_variants_groups_id, products_variants_groups, products_variants_values_id, products_variants_values from :table_wishlists_products_variants where whishlists_id = :whishlists_id, whishlists_products_id = :whishlists_products_id');
            $Qvariants->bindTable(':table_wishlists_products_variants', TABLE_WISHLISTS_PRODUCTS_VARIANTS);
            $Qvariants->bindInt(':whishlists_id', $this->_wishlists_id);
            $Qvariants->bindInt(':whishlists_products_id', $Qproducts->valueInt('wishlists_products_id'));
            $Qvariants->execute();
            
            $products_variants = $osC_Product->getVariants();
            $row_variants = array();
            if ($Qvariants->numberOfRows() > 0) {
              while($Qvariants->next()) {
                $row_variants[] = array('groups_id' => $Qvariants->valueInt('products_variants_groups_id'), 
                                        'values_id' => $Qvariants->valueInt('products_variants_values_id'), 
                                        'groups_name' => $Qvariants->value('products_variants_groups'), 
                                        'values_name' => $Qvariants->value('products_variants_values'));
              }
              
              $Qvariants->freeResult();
            }
            
            if (!osc_empty($row_variants)) {
              $product_name .= '<br />';
              
              foreach($row_variants as $variant) {
                $variants[$variant['groups_id']] = $variant['values_id'];
                $product_name .= '<em>' . $variant['groups_name'] . ': ' . $variant['values_name'] . '</em>' . '<br />';
              }
              
              if (is_array($variants) && !osc_empty($variants)) {
                $product_id_string = osc_get_product_id_string($products_id, $variants);
                
                $products_variant = $products_variants[$product_id_string];
              }else {
                $products_variant = $osC_Product->getDefaultVariant();
              }
              
              $product_price = $products_variant['price'];
              $product_image = $products_variant['image'];
            }
          }
  
          $this->_contents[$Qproducts->value('products_id')] = array('products_id' => $Qproducts->value('products_id'),
                                                                     'name' => $product_name,
                                                                     'image' => $product_image,
                                                                     'price' => $product_price,
                                                                     'variants' => $variants,
                                                                     'date_added' => osC_DateTime::getShort($Qproducts->value('date_added')),
                                                                     'comments' => $Qproducts->value('comments'));
        }
        
      } else {
        $token = $this->generateToken();
        
        $Qupdate = $osC_Database->query('update :table_wishlists set customers_id = :customers_id, wishlists_token = :wishlists_token where wishlists_id = :wishlists_id');
        $Qupdate->bindTable(':table_wishlists', TABLE_WISHLISTS);
        $Qupdate->bindInt(':customers_id', $osC_Customer->getID());
        $Qupdate->bindValue(':wishlists_token', $token);
        $Qupdate->bindInt(':wishlists_id', $this->_wishlists_id);
        $Qupdate->execute();
        
        $this->_token = $token;
      }
    }
    
    function add($products_id, $variants = array()) {
      global $osC_Database, $osC_Services, $osC_Customer, $osC_Product;
      
      //if wishlist empty, create a new wishlist
      if (!$this->hasWishlistID()) {
        $token = $this->generateToken();
        
        $Qnew = $osC_Database->query('insert into :table_wishlists (customers_id, wishlists_token) values (:customers_id, :wishlists_token)');
        $Qnew->bindTable(':table_wishlists', TABLE_WISHLISTS);
        
        $Qnew->bindInt(':customers_id', $osC_Customer->getID());
        
        $Qnew->bindValue(':wishlists_token', $token);
        $Qnew->execute();
        
        $this->_wishlists_id = $osC_Database->nextID();
        $this->_token = $token;
        
        $Qnew->freeResult();
      }
      
      if (!isset($osC_Product)) {
        $osC_Product = new osC_Product($products_id);
      }

      if ($osC_Product->getID() > 0) {
        if (!$this->exists($products_id)) {
          $product_price = $osC_Product->getPrice();
          $product_name = $osC_Product->getTitle();
          $product_image = $osC_Product->getImage();
          
          if ($osC_Services->isStarted('specials')) {
            global $osC_Specials;

            if ($new_price = $osC_Specials->getPrice($products_id)) {
              $price = $new_price;
            }
          }
          
          //if the product has variants, set the image, price etc according to the variants
          if ($osC_Product->hasVariants()) {
            $products_variants = $osC_Product->getVariants();
            
            if (is_array($variants) && !osc_empty($variants)) {
              $product_id_string = osc_get_product_id_string($products_id, $variants);
              
              $products_variant = $products_variants[$product_id_string];
            }else {
              $products_variant = $osC_Product->getDefaultVariant();
            }
            
            $variants_groups_id = $products_variant['groups_id'];
            $variants_groups_name = $products_variant['groups_name'];
            
            if (!osc_empty($variants_groups_name)) {
              $product_name .= '<br />';
              foreach ($variants_groups_name as $group_name => $value_name) {
                $product_name .= '<em>' . $group_name . ': ' . $value_name . '</em>' . '<br />';
              }
            }
            
            $product_price = $products_variant['price'];
            
            $product_image = $products_variant['image'];
          }

          $this->_contents[$products_id]= array('products_id' => $products_id,
                                                'name' => $product_name,
                                                'image' => $product_image,
                                                'price' => $product_price, 
                                                'date_added' => osC_DateTime::getShort(osC_DateTime::getNow()),
                                                'variants' => $variants,
                                                'comments' => '');

          //insert into wishlist products
          $Qnew = $osC_Database->query('insert into :table_wishlist_products (wishlists_id, products_id, date_added, comments) values (:wishlists_id, :products_id, now(), :comments)');
          $Qnew->bindTable(':table_wishlist_products', TABLE_WISHLISTS_PRODUCTS);
          $Qnew->bindInt(':wishlists_id', $this->_wishlists_id);
          $Qnew->bindInt(':products_id', $products_id);
          $Qnew->bindValue(':comments', '');
          $Qnew->execute();
          
          $wishlists_products_id = $osC_Database->nextID();
          
          $Qnew->freeResult();
          
          //if the wishlists products has variants
          $products_variants_groups_id = array();
          $products_variants_groups_name = array();
          if (isset($variants_groups_id) && isset($variants_groups_name)) {
            foreach($variants_groups_id as $groups_id => $values_id) {
              $products_variants_groups_id[] = array('groups_id' => $groups_id, 'values_id' => $values_id);
            }
             
            foreach($variants_groups_name as $groups_name => $values_name) {
              $products_variants_groups_name[] = array('groups_name' => $groups_name, 'values_name' => $values_name);
            }
          }
          
          if (!osc_empty($products_variants_groups_id)) {
            foreach($products_variants_groups_id as $key => $groups_id) {
              $Qinsert = $osC_Database->query('insert into :table_wishlists_products_variants (wishlists_id, wishlists_products_id, products_variants_groups_id, products_variants_groups, products_variants_values_id, products_variants_values) values (:wishlists_id, :wishlists_products_id, :products_variants_groups_id, :products_variants_groups, :products_variants_values_id, :products_variants_values)');
              $Qinsert->bindTable(':table_wishlists_products_variants', TABLE_WISHLISTS_PRODUCTS_VARIANTS);
              $Qinsert->bindInt(':wishlists_id', $this->_wishlists_id);
              $Qinsert->bindInt(':wishlists_products_id', $wishlists_products_id);
              $Qinsert->bindInt(':products_variants_groups_id', $groups_id['groups_id']);
              $Qinsert->bindInt(':products_variants_values_id', $groups_id['values_id']);
              $Qinsert->bindValue(':products_variants_groups', $products_variants_groups_name[$key]['groups_name']);
              $Qinsert->bindValue(':products_variants_values', $products_variants_groups_name[$key]['values_name']);
              $Qinsert->execute();
            }
          }
        }
      }
    }
    
    function getProducts() {
      global $osC_Customer;
      
      $products = array();
      
      if ($this->hasContents()) {
        foreach ($this->_contents as $products_id => $data) {
          $products[] = $data;
        }

        return $products;        
      }
           
      return false;      
    }    
	  
    function updateWishlist($comments) {
      global $osC_Database, $osC_Customer;
      
      $error = false;
      
      foreach($comments as $products_id => $comment) {
        $this->_contents[$products_id]['comments'] = $comment;
        
        $Qupdate = $osC_Database->query('update :table_wishlist_products set comments = :comments where wishlists_id = :wishlists_id and products_id = :products_id');
        $Qupdate->bindTable(':table_wishlist_products', TABLE_WISHLISTS_PRODUCTS);
        $Qupdate->bindValue(':comments', $comment);
        $Qupdate->bindInt(':wishlists_id', $this->_wishlists_id);
        $Qupdate->bindInt(':products_id', $products_id);
        $Qupdate->execute();
        
        if ($osC_Database->isError()) {       
          $error = true;
          break;      
        }
      }
      
      if ($error === false) {
        return true;
      }
      
      return false;
    }
    
    function hasProduct($products_id) {
      if (isset($this->_contents[$products_id])) {
        return true;
      }
      
      return false;
    }
    
    function deleteProduct($products_id) {
      global $osC_Customer, $osC_Database;
      
      $Qcheck = $osC_Database->query('select wishlists_products_id from :table_wishlist_products where products_id = :products_id and wishlists_id = :wishlists_id');
      $Qcheck->bindTable(':table_wishlist_products', TABLE_WISHLISTS_PRODUCTS);
      $Qcheck->bindInt(':products_id', $products_id);
      $Qcheck->bindInt(':wishlists_id', $this->_wishlists_id);
      $Qcheck->execute();
      
      if ($Qcheck->numberOfRows() > 0) {
        $row = $Qcheck->toArray();
        
        $wishlists_products_id = $row['wishlists_products_id'];
      }
      $Qcheck->freeResult();
      
      //check if the product has variants in the table, then delete the variants
      if (isset($wishlists_products_id)) {
        $QcheckVariants = $osC_Database->query('select wishlists_products_variants_id from :table_wishlists_products_variants where wishlists_id = :wishlists_id and wishlists_products_id = :wishlists_products_id');
        $QcheckVariants->bindTable(':table_wishlists_products_variants', TABLE_WISHLISTS_PRODUCTS_VARIANTS);
        $QcheckVariants->bindInt(':wishlists_id', $this->_wishlists_id);
        $QcheckVariants->bindInt(':wishlists_products_id', $wishlists_products_id);
        $QcheckVariants->execute();
        
        if ($QcheckVariants->numberOfRows() > 0) {
          $QdeleteVariants = $osC_Database->query('delete from :table_wishlists_products_variants where wishlists_id = :wishlists_id and wishlists_products_id = :wishlists_products_id');
          $QdeleteVariants->bindTable(':table_wishlists_products_variants', TABLE_WISHLISTS_PRODUCTS_VARIANTS);
          $QdeleteVariants->bindInt(':wishlists_id', $this->_wishlists_id);
          $QdeleteVariants->bindInt(':wishlists_products_id', $wishlists_products_id);
          $QdeleteVariants->execute();
          
          $QdeleteVariants->freeResult();
        }
        
        $QcheckVariants->freeResult();
      }
      
      //delete the products
      $Qdelete = $osC_Database->query('delete from :table_wishlist_products where products_id = :products_id and wishlists_id = :wishlists_id');
      $Qdelete->bindTable(':table_wishlist_products', TABLE_WISHLISTS_PRODUCTS);
      $Qdelete->bindInt(':products_id', $products_id);
      $Qdelete->bindInt(':wishlists_id', $this->_wishlists_id);
      $Qdelete->execute();
        
      if (!$osC_Database->isError()) {
        if (isset($this->_contents[$products_id])) {
          unset($this->_contents[$products_id]);
        }
        
        //when the products is empty, delete wishlist
        if ((!$this->hasContents())) {
          $Qdelete = $osC_Database->query('delete from :table_wishlist where wishlists_id = :wishlists_id');
          $Qdelete->bindTable(':table_wishlist', TABLE_WISHLISTS);
          $Qdelete->bindInt(':wishlists_id', $this->_wishlists_id);
          $Qdelete->execute();
          
          if ($osC_Database->isError()) {
            return false;
          }
          
          $this->_wishlists_id = null;
          $this->_token = null;
        }
        
        return true;
      }
      
      return false;
    }
  }
?>