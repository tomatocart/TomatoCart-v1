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
	include_once(DIR_FS_CATALOG . 'includes/classes/modules.php');

  class toC_Json_Popup_Cart {
  
    function getCartContents() {
      global $osC_Language, $osC_ShoppingCart, $osC_Currencies, $toC_Json, $osC_Image;
      
      //the maximum displayed characters of the product name
      $product_name_length = 18;
      
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
          							'	<td>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $product['id'], 'SSL'), $osC_Image->show($product['image'], $product['name'], '', 'mini')) . '</td>' .
                      	'	<td>' . 
                      		$product['quantity'] . ' x ' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $product['id'], 'SSL'), $product_name);
          
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
          	
          	$content .=		'	<td>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, osc_get_product_id($product['id']) . (!empty($variants_string) ? '&variants=' . $variants_string : '') . '&action=cart_remove', 'SSL'), 'X', 'class="btn btn-mini removeBtn" data-pid="' . $product['id'] . '"') . '</td>';
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
										osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'), $osC_Language->get('button_checkout'), 'class="btn btn-primary btnCheckout"') .
										osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'cart', 'SSL'), $osC_Language->get('button_cart'), 'class="btn btn-primary btnCart"') . 
									'</div>';
			
      $content .= '</div>';
      
      $response = array('success' => true, 'content' => $content, 'total' => $osC_ShoppingCart->numberOfItems());
      
      echo $toC_Json->encode($response);
    }

    function addProduct() {
    	global $osC_ShoppingCart, $toC_Json, $osC_Language, $toC_Customization_Fields, $osC_Image, $osC_Currencies;
    	
    	$osC_Language->load('products');
    
    	if ( is_numeric($_POST['pID']) && osC_Product::checkEntry($_POST['pID']) ) {
    		$osC_ShoppingCart->resetShippingMethod();
    
    		$osC_Product = new osC_Product($_POST['pID']);
    
    		//gift certificate check
    		if ($osC_Product->isGiftCertificate() && !isset($_POST['senders_name'])) {
    			$response = array('success' => false,
    												'feedback' => $osC_Language->get('error_gift_certificate_data_missing'));
    		}
    		//customization fields check
    		else if ( $osC_Product->hasRequiredCustomizationFields() && !$toC_Customization_Fields->exists($osC_Product->getID()) ) {
    			$response = array('success' => false,
    												'feedback' => $osC_Language->get('error_customization_fields_missing'));
    		} else {
    			$variants = null;
    			if (isset($_POST['variants']) && !empty($_POST['variants'])) {
    				$variants = osc_parse_variants_string($_POST['variants']);
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
    
    			$osC_ShoppingCart->add($_POST['pID'], $variants, $_POST['pQty'], $gift_certificate_data);
    
    			$items = 0;
    			foreach($osC_ShoppingCart->getProducts() as $products_id => $data) {
    				$items += $data['quantity'];
    			}
    			
    			//build the content of the confirmation dialog
    			$product_id_string = osc_get_product_id_string($_POST['pID'], $variants);
    			
    			//find the added product
    			$added_product = null;
    			foreach ($osC_ShoppingCart->getProducts() as $id_string => $product) {
    				if ($product_id_string == $id_string) {
    					$added_product = $product;
    					
    					break;
    				}
    			}
    			
    			//build the dialog
    			$confirm_dialog = null;
    			if ($added_product !== null) {
    				$confirm_dialog .= '<div class="dlgConfirm">' .
						    								'<div class="itemImage">' . $osC_Image->show($added_product['image'], $added_product['name'], '', 'thumbnail') . '</div>' .
						    								'<p class="itemDetail">' . sprintf($osC_Language->get('add_to_cart_confirmation'), $added_product['quantity'] . ' x ' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $added_product['name']));
    				 
    				//gift certificates
    				if ($added_product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
    					$confirm_dialog .= '<br />- ' . $osC_Language->get('senders_name') . ': ' . $product['gc_data']['senders_name'];
    						
    					if ($added_product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
    						$confirm_dialog .= '<br />- ' . $osC_Language->get('senders_email')  . ': ' . $product['gc_data']['senders_email'];
    					}
    						
    					$confirm_dialog .= '<br />- ' . $osC_Language->get('recipients_name') . ': ' . $product['gc_data']['recipients_name'];
    						
    					if ($added_product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
    						$confirm_dialog .= '<br />- ' . $osC_Language->get('recipients_email')  . ': ' . $product['gc_data']['recipients_email'];
    					}
    						
    					$confirm_dialog .= '<br />- ' . $osC_Language->get('message')  . ': ' . $product['gc_data']['message'];
    				}
    				
    				$confirm_dialog .= '</p>';
    				
    				//variants products
    				if ($osC_ShoppingCart->hasVariants($product['id'])) {
    					foreach ($osC_ShoppingCart->getVariants($product['id']) as $variants) {
    						$confirm_dialog .= '<div>';
    				
    						$confirm_dialog .=  '<strong>' . $variants['groups_name'] . ' - </strong><strong>' . $variants['values_name'] . '</strong></tr>';
    						
    						$confirm_dialog .= '</div>';
    					}
    				}
    					
    				$confirm_dialog .= '</div>';
    				
    				//cart total
    				$confirm_dialog .= '<p><strong>' . $osC_ShoppingCart->numberOfItems() . ' ' . $osC_Language->get('text_items') . '</strong> - <strong>' . $osC_Currencies->format($osC_ShoppingCart->getTotal()) . '</strong></p>';
    				
    				//bottom buttons
    				$confirm_dialog .=	'<div class="btns">' .
							    								osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'), $osC_Language->get('button_checkout'), 'class="btn btn-primary"') .
							    								osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'cart', 'SSL'), $osC_Language->get('button_cart'), 'class="btn btn-primary"') .
							    								osc_link_object(osc_href_link(FILENAME_DEFAULT), $osC_Language->get('button_continue'), 'id="btnContinue" class="btn btn-primary"') .
					    									'</div>';
    				
    				$confirm_dialog .= '</div>';
    			}
    			
    			$response = array('success' => true, 'items' => $items, 'confirm_dialog' => $confirm_dialog);
    		}
    	} else {
    		$response = array('success' => false);
    	}
    
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
    		
    		//order totals
    		$order_totals =	'';
    		foreach ($osC_ShoppingCart->getOrderTotals() as $module) {
    			$order_totals .=	'<tr>' .
					    							'	<td class="title"><strong>' . $module['title'] . '</strong></td>' .
					    							'	<td class="text"><strong>' . $module['text'] . '</strong></td>' .
					    							'</tr>';
    		}
    	
    		$response = array('success' => true, 'total' => $osC_ShoppingCart->numberOfItems(), 'order_totals' => $order_totals);
    	}else {
    		$response = array('success' => false);
    	}
    	
    	echo $toC_Json->encode($response);
    }
  }
?>  