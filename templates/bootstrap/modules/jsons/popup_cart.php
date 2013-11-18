<?php
/*
  $Id: popup_cart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
	ini_set('display_errors', 1);
	include_once(DIR_FS_CATALOG . 'includes/classes/modules.php');

  class toC_Json_Popup_Cart {
  
    function getCartContents() {
      global $osC_Language, $osC_ShoppingCart, $osC_Currencies, $toC_Json, $osC_Image;
      
      //the maximum displayed characters of the product name
      $product_name_length = 16;
      
      $content = '<div class="cartInner">';
      $content .=   '<h6>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $osC_Language->get('box_shopping_cart_heading')) . '</h6>' . 
                    '<div class="content clearfix">';
                      
      
      //products
      if ($osC_ShoppingCart->hasContents()) {
        $content .= 	'<table class="products">';
        
        foreach ($osC_ShoppingCart->getProducts() as $products_id_string => $product) {
        	//product name
        	if (strlen($product['name']) > $product_name_length) {
        		$product_name = substr($product['name'], 0, $product_name_length) . '..';
        	}else {
        		$product_name = $product['name'];
        	}
        	
          $content .= 	'<tr>' .
          							'	<td>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $osC_Image->show($product['image'], $product['name'], '', 'mini')) . '</td>' .
                      	'	<td>' . 
                      		$product['quantity'] . ' x ' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $product_name);
          
          								//gift certificates
									        if ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
									         $content .= '<br />- ' . $osC_Language->get('senders_name') . ': ' . $product['gc_data']['senders_name'];
									          
									          if ($products['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
									            $content .= '<br />- ' . $osC_Language->get('senders_email')  . ': ' . $product['gc_data']['senders_email'];
									          }
									          
									          $content .= '<br />- ' . $osC_Language->get('recipients_name') . ': ' . $product['gc_data']['recipients_name'];
									          
									          if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
									            $content .= '<br />- ' . $osC_Language->get('recipients_email')  . ': ' . $product['gc_data']['recipients_email'];
									          }
									          
									          $content .= '<br />- ' . $osC_Language->get('message')  . ': ' . $product['gc_data']['message'];
									        }
									        
									        //variants products
									        if ($osC_ShoppingCart->hasVariants($product['id'])) {
									        	foreach ($osC_ShoppingCart->getVariants($product['id']) as $variants) {
									        
									        		$content .=  '<br />- ' . $variants['groups_name'] . ': ' . $variants['values_name'];
									        	}
									        }

									        
					$content .=		'	</td>';
					
					//products price
          $content .=		'	<td><strong>' . $osC_Currencies->displayPrice($product['final_price'], $product['tax_class_id'], $product['quantity']) . '</strong></td>';
          
          //only when the ajax shopping cart box is disabled, the delete button will be displayed
          if (isset($_POST['enable_delete']) && $_POST['enable_delete'] == 'yes') {
          	$variants_string = null;
          	if (!is_numeric($products_id_string) && (strpos($products_id_string, '#') != false)) {
          		$tmp = explode('#', $products_id_string);
          		$variants_string = $tmp[1];
          	}
          	
          	$content .=		'	<td>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, osc_get_product_id($product['id']) . (!empty($variants_string) ? '&variants=' . $variants_string : '') . '&action=cart_remove', 'SSL'), osc_draw_image_button('small_delete.gif', $osC_Language->get('button_delete')), 'class="removeBtn" data-pid="' . $product['id'] . '"') . '</td>';
          }
		      
      		$content .=		'</tr>';
        }

        $content .= 	'</table>';
      } else {
        $content .= 	'<div><strong class="cartEmpty">' . $osC_Language->get('box_shopping_cart_empty') . '</strong></div>';
      }
      
      //order totals
      $content .= 		'<table class="orderTotals">';
			foreach ($osC_ShoppingCart->getOrderTotals() as $module) {
				$content .= 		'<tr>' .
													'	<td class="title"><strong>' . $module['title'] . '</strong></td>' .
													'	<td class="text"><strong>' . $module['text'] . '</strong></td>' .
												'</tr>';
			}
        
      $content .=		'</table>';
      
			$content .= 	'</div>';
			
			//bottom buttons
			$content .= '<div class="buttons clearfix">' . 
										osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'), $osC_Language->get('button_checkout'), 'class="btnCheckout btn"') .
										osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'cart', 'SSL'), $osC_Language->get('box_shopping_cart_heading'), 'class="btnCart btn"') . 
									'</div>';
			
      $content .= '</div>';
      
      $response = array('success' => true, 'content' => $content, 'total' => count($osC_ShoppingCart->getProducts()));
      
      echo $toC_Json->encode($response);
    } 
    
    //remove product from popup cart
    function removeProduct() {
    	global $toC_Json, $osC_ShoppingCart;
    	
    	$products_id = isset($_POST['pID']) ? $_POST['pID'] : null;
    	
    	if ( (!empty($products_id)) && osC_Product::checkEntry($products_id) ) {
    		$osC_ShoppingCart->remove($products_id);
    		$osC_ShoppingCart->resetShippingMethod();
    	
    		if (!$osC_ShoppingCart->hasContents()) {
    			$osC_ShoppingCart->reset();
    		}
    	
    		$response = array('success' => true);
    	}else {
    		$response = array('success' => false);
    	}
    	
    	echo $toC_Json->encode($response);
    }
  }
?> 