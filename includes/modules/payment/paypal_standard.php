<?php
/*
 $Id: paypal_standard.php $
TomatoCart Open Source Shopping Cart Solutions
http://www.tomatocart.com

Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License v2 (1991)
as published by the Free Software Foundation.
*/

class osC_Payment_paypal_standard extends osC_Payment {
    var $_title,
    $_code = 'paypal_standard',
    $_status = false,
    $_sort_order,
    $_order_id,
    $_ignore_order_totals = array('sub_total', 'tax', 'total'),
    $_transaction_response;

    // class constructor
    function osC_Payment_paypal_standard() {
        global $osC_Database, $osC_Language, $osC_ShoppingCart;

        $this->_title = $osC_Language->get('payment_paypal_standard_title');
        $this->_method_title = $osC_Language->get('payment_paypal_standard_method_title');
        $this->_sort_order = MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER;
        $this->_status = ((MODULE_PAYMENT_PAYPAL_STANDARD_STATUS == '1') ? true : false);

        if (MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live') {
            $this->form_action_url = 'https://www.paypal.com/cgi-bin/webscr';
        } else {
            $this->form_action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }

        if ($this->_status === true) {
            $this->order_status = MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID : (int)ORDERS_STATUS_PAID;

            if ((int)MODULE_PAYMENT_PAYPAL_STANDARD_ZONE > 0) {
                $check_flag = false;

                $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
                $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
                $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_PAYPAL_STANDARD_ZONE);
                $Qcheck->bindInt(':zone_country_id', $osC_ShoppingCart->getBillingAddress('country_id'));
                $Qcheck->execute();

                while ($Qcheck->next()) {
                    if ($Qcheck->valueInt('zone_id') < 1) {
                        $check_flag = true;
                        break;
                    } elseif ($Qcheck->valueInt('zone_id') == $osC_ShoppingCart->getBillingAddress('zone_id')) {
                        $check_flag = true;
                        break;
                    }
                }

                if ($check_flag == false) {
                    $this->_status = false;
                }
            }
        }
    }

    function selection() {
        return array('id' => $this->_code,
                        'module' => $this->_method_title);
    }

    function pre_confirmation_check() {
        global $osC_ShoppingCart;

        $cart_id = $osC_ShoppingCart->getCartID();
        if (empty($cart_id)) {
            $osC_ShoppingCart->generateCartID();
        }
    }

    function confirmation() {
        $this->_order_id = osC_Order::insert(ORDERS_STATUS_PREPARING);
    }

    function process_button() {
        global $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Tax, $osC_Language;

        $process_button_string = '';
        $params = array('business' => MODULE_PAYMENT_PAYPAL_STANDARD_ID,
                        'currency_code' => $osC_Currencies->getCode(),
                        'invoice' => $this->_order_id,
                        'custom' => $osC_Customer->getID(),
                        'no_note' => '1',
                        'notify_url' =>  HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . FILENAME_CHECKOUT . '?callback&module=' . $this->_code,
                        'return' => osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL', null, null, true),
                        'rm' => '2',
                        'cancel_return' => osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL', null, null, true),
                        'bn' => 'Tomatocart_Default_ST',
                        'paymentaction' => ((MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD == 'Sale') ? 'sale' : 'authorization'));

        if ($osC_ShoppingCart->hasShippingAddress()) {
            $params['address_override'] = '1';
            $params['first_name'] = $osC_ShoppingCart->getShippingAddress('firstname');
            $params['last_name'] =  $osC_ShoppingCart->getShippingAddress('lastname');
            $params['address1'] = $osC_ShoppingCart->getShippingAddress('street_address');
            $params['city'] = $osC_ShoppingCart->getShippingAddress('city');
            $params['state'] = $osC_ShoppingCart->getShippingAddress('zone_code');
            $params['zip'] = $osC_ShoppingCart->getShippingAddress('postcode');
            $params['country'] = $osC_ShoppingCart->getShippingAddress('country_iso_code_2');
        } else {
            $params['no_shipping'] = '1';
            $params['first_name'] = $osC_ShoppingCart->getBillingAddress('firstname');
            $params['last_name'] = $osC_ShoppingCart->getBillingAddress('lastname');
            $params['address1'] = $osC_ShoppingCart->getBillingAddress('street_address');
            $params['city'] = $osC_ShoppingCart->getBillingAddress('city');
            $params['state'] = $osC_ShoppingCart->getBillingAddress('zone_code');
            $params['zip'] = $osC_ShoppingCart->getBillingAddress('postcode');
            $params['country'] = $osC_ShoppingCart->getBillingAddress('country_iso_code_2');
        }

        if (MODULE_PAYMENT_PAYPAL_STANDARD_TRANSFER_CART == '-1') {
            $params['cmd'] = '_xclick';
            $params['item_name'] = STORE_NAME;

            $shipping_tax = ($osC_ShoppingCart->getShippingMethod('cost')) * ($osC_Tax->getTaxRate($osC_ShoppingCart->getShippingMethod('tax_class_id'), $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id')) / 100);

            if (DISPLAY_PRICE_WITH_TAX == '1') {
                $shipping = $osC_ShoppingCart->getShippingMethod('cost');
            } else {
                $shipping = $osC_ShoppingCart->getShippingMethod('cost') + $shipping_tax;
            }
            $params['shipping'] = $osC_Currencies->formatRaw($shipping);

            $total_tax = $osC_ShoppingCart->getTax() - $shipping_tax;
            $params['tax'] = $osC_Currencies->formatRaw($total_tax);
            $params['amount'] = $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal() - $shipping - $total_tax);
        }else {
            $params['cmd'] = '_cart';
            $params['upload'] = '1';
            if (DISPLAY_PRICE_WITH_TAX == '-1') {
                $params['tax_cart'] = $osC_Currencies->formatRaw($osC_ShoppingCart->getTax());
            }

            //products
            $products = array();
            if ($osC_ShoppingCart->hasContents()) {
                $i = 1;

                $products = $osC_ShoppingCart->getProducts();
                foreach($products as $product) {
                    $product_name = $product['name'];

                    //gift certificate
                    if ($product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
                        $product_name .= "\n" . ' - ' . $osC_Language->get('senders_name') . ': ' . $product['gc_data']['senders_name'];

                        if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                            $product_name .= "\n" . ' - ' . $osC_Language->get('senders_email')  . ': ' . $product['gc_data']['senders_email'];
                        }

                        $product_name .= "\n" . ' - ' . $osC_Language->get('recipients_name') . ': ' . $product['gc_data']['recipients_name'];

                        if ($product['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
                            $product_name .= "\n" . ' - ' . $osC_Language->get('recipients_email')  . ': ' . $product['gc_data']['recipients_email'];
                        }

                        $product_name .= "\n" . ' - ' . $osC_Language->get('message')  . ': ' . $product['gc_data']['message'];
                    }

                    if ($osC_ShoppingCart->hasVariants($product['id'])) {
                        foreach ($osC_ShoppingCart->getVariants($product['id']) as $variant) {
                            $product_name .= ' - ' . $variant['groups_name'] . ': ' . $variant['values_name'];
                        }
                    }

                    $product_data = array('item_name_' . $i => $product_name, 'item_number_' . $i => $product['sku'], 'quantity_' . $i  => $product['quantity']);

                    $tax = $osC_Tax->getTaxRate($product['tax_class_id'], $osC_ShoppingCart->getTaxingAddress('country_id'), $osC_ShoppingCart->getTaxingAddress('zone_id'));
                    $price = $osC_Currencies->addTaxRateToPrice($product['final_price'], $tax);
                    $product_data['amount_' . $i] = $osC_Currencies->formatRaw($price);

                    $params = array_merge($params,$product_data);

                    $i++;
                }
            }

            //order totals
            foreach ($osC_ShoppingCart->getOrderTotals() as $total) {
                if ( !in_array($total['code'], $this->_ignore_order_totals) ) {
                    if ( ($total['code'] == 'coupon') || ($total['code'] == 'gift_certificate') ) {
                        $params['discount_amount_cart'] += $osC_Currencies->formatRaw(abs($total['value']));
                    } else {
                        $order_total = array('item_name_' . $i => $total['title'], 'quantity_' . $i => 1, 'amount_' . $i => round($total['value'], 2));
                        $params = array_merge($params, $order_total);

                        $i++;
                    }
                }
            }
        }

        if ( osc_not_null('MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE') ) {
            $params['page_style'] = MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE;
        }

        $process_button_string = '';

        foreach ($params as $key => $value) {
            $process_button_string .= osc_draw_hidden_field($key, $value);
        }

        return $process_button_string;
    }

    function process() {
        global $osC_ShoppingCart, $osC_Database;

        $prep = explode('-', $_SESSION['prepOrderID']);
        if ($prep[0] == $osC_ShoppingCart->getCartID()) {
            $Qcheck = $osC_Database->query('select orders_status_id from :table_orders_status_history where orders_id = :orders_id');
            $Qcheck->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
            $Qcheck->bindInt(':orders_id', $prep[1]);
            $Qcheck->execute();

            $paid = false;
            if ($Qcheck->numberOfRows() > 0) {
                while($Qcheck->next()) {
                    if ($Qcheck->valueInt('orders_status_id') == $this->order_status) {
                        $paid = true;
                    }
                }
            }

            if ($paid === false) {
                if (osc_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_PROCESSING_ORDER_STATUS_ID)) {
                    osC_Order::process($_POST['invoice'], MODULE_PAYMENT_PAYPAL_STANDARD_PROCESSING_ORDER_STATUS_ID, 'PayPal Processing Transaction');
                }
            }
        }

        unset($_SESSION['prepOrderID']);
    }

function callback() {
        global $osC_Database, $osC_Currencies, $osC_Language;

        $post_string = 'cmd=_notify-validate&';

        foreach ($_POST as $key => $value) {
            $post_string .= $key . '=' . urlencode($value) . '&';
        }

        $post_string = substr($post_string, 0, -1);

        $this->_transaction_response = $this->sendTransactionToGateway($this->form_action_url, $post_string);

        if (strtoupper(trim($this->_transaction_response)) == 'VERIFIED') {
            if (isset($_POST['invoice']) && is_numeric($_POST['invoice']) && ($_POST['invoice'] > 0)) {
                $Qcheck = $osC_Database->query('select orders_status, currency, currency_value from :table_orders where orders_id = :orders_id and customers_id = :customers_id');
                $Qcheck->bindTable(':table_orders', TABLE_ORDERS);
                $Qcheck->bindInt(':orders_id', $_POST['invoice']);
                $Qcheck->bindInt(':customers_id', $_POST['custom']);
                $Qcheck->execute();

                if ($Qcheck->numberOfRows() > 0) {
                    $order = $Qcheck->toArray();

                    $Qtotal = $osC_Database->query('select value from :table_orders_total where orders_id = :orders_id and class = "total" limit 1');
                    $Qtotal->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
                    $Qtotal->bindInt(':orders_id', $_POST['invoice']);
                    $Qtotal->execute();

                    $total = $Qtotal->toArray();

                    $comment_status = $_POST['payment_status'] . ' (' . ucfirst($_POST['payer_status']) . '; ' . $osC_Currencies->format($_POST['mc_gross'], false, $_POST['mc_currency']) . ')';

                    if ($_POST['payment_status'] == 'Pending') {
                        $comment_status .= '; ' . $_POST['pending_reason'];
                    } elseif ($_POST['payment_status'] == 'Reversed' || $_POST['payment_status'] == 'Refunded') {
                        $comment_status .= '; ' . $_POST['reason_code'];
                    }

                    if ( $_POST['mc_gross'] != number_format($total['value'] * $order['currency_value'], $osC_Currencies->getDecimalPlaces($order['currency'])) ) {
                        $comment_status .= '; PayPal transaction value (' . osc_output_string_protected($_POST['mc_gross']) . ') does not match order value (' . number_format($total['value'] * $order['currency_value'], $osC_Currencies->getDecimalPlaces($order['currency'])) . ')';
                    }

                    $comments = 'PayPal IPN Verified [' . $comment_status . ']';

                    osC_Order::process($_POST['invoice'], $this->order_status, $comments);
                    
                    $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
                    $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
                    $Qtransaction->bindInt(':orders_id', $_POST['invoice']);
                    $Qtransaction->bindInt(':transaction_code', 1);
                    $Qtransaction->bindValue(':transaction_return_value', $this->_transaction_response);
                    $Qtransaction->bindInt(':transaction_return_status', 1);
                    $Qtransaction->execute();
                    
                    $Qtransaction->freeResult();
                }
            }
        } else {
            if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL')) {
                $email_body = 'PAYPAL_STANDARD_DEBUG_POST_DATA:' . "\n\n";

                reset($_POST);
                foreach($_POST as $key=>$value) {
                    $email_body .= $key . '=' . $value . "\n";
                }

                $email_body .= "\n" . 'PAYPAL_STANDARD_DEBUG_GET_DATA:' . "\n\n";
                reset($_GET);
                foreach($_GET as $key=>$value) {
                    $email_body .= $key . '=' . $value . "\n";
                }

                osc_email('', MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL, 'PayPal IPN Invalid Process', $email_body, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            }

            if (isset($_POST['invoice']) && is_numeric($_POST['invoice']) && $_POST['invoice'] > 0) {
                $Qcheck = $osC_Database->query('select orders_id from :table_orders where orders_id=:orders_id and customers_id=:customers_id');
                $Qcheck->bindTable(':table_orders', TABLE_ORDERS);
                $Qcheck->bindInt('orders_id', $_POST['invoice']);
                $Qcheck->bindInt('customers_id', $_POST['custom']);
                $Qcheck->execute();

                if ($Qcheck->numberOfRows() > 0) {
                    $comment_status = $_POST['payment_status'];

                    if ($_POST['payment_status'] == 'Pending') {
                        $comment_status .= '; ' . $_POST['pending_reason'];
                    }elseif ( ($_POST['payment_status'] == 'Reversed') || ($_POST['payment_status'] == 'Refunded') ) {
                        $comment_status .= '; ' . $_POST['reason_code'];
                    }
                    $comments = 'PayPal IPN Invalid [' . $comment_status . ']';                    

                    osC_Order::insertOrderStatusHistory($_POST['invoice'], $this->order_status, $comments);
                }
            }
            
            //process the transaction history
            $Qtransaction_status = $osC_Database->query('select count(*) as total from :table_orders_transactions_status where status_name = :status_name');
            $Qtransaction_status->bindTable(':table_orders_transactions_status', TABLE_ORDERS_TRANSACTIONS_STATUS);
            $Qtransaction_status->bindValue(':status_name', $_POST['payment_status']);
            $Qtransaction_status->execute();
            
            $transaction_status = $Qtransaction_status->toArray();

            $Qtransaction_status->freeResult();

            
            //verify whether there is already the specific transactions status
            if ($transaction_status['total'] == 0) {
                //get the max status id
                $Qtransaction_status_max = $osC_Database->query('select max(id) as max_id from :table_orders_transactions_status');
                $Qtransaction_status_max->bindTable(':table_orders_transactions_status', TABLE_ORDERS_TRANSACTIONS_STATUS);
                $Qtransaction_status_max->execute();
                
                $transaction_status_max = $Qtransaction_status_max->toArray();
                
                $Qtransaction_status_max->freeResult();
                
                 //insert the specific transaction status for this module
                foreach($osC_Language->getAll() as $l) {
                    $Qinsert_transaction_status = $osC_Database->query('insert into :table_orders_transactions_status values (:id, :language_id, :status_name)');
                    $Qinsert_transaction_status->bindTable(':table_orders_transactions_status', TABLE_ORDERS_TRANSACTIONS_STATUS);
                    $Qinsert_transaction_status->bindInt(':id', $transaction_status_max['max_id'] + 1);
                    $Qinsert_transaction_status->bindInt(':language_id', $l['id']);
                    $Qinsert_transaction_status->bindValue(':status_name', $_POST['payment_status']);
                    $Qinsert_transaction_status->execute();
                }                      
            }

          
            //get the transaction status id
            $Qtransaction_satus_id =  $osC_Database->query('select id from :table_orders_transactions_status where language_id = :language_id and status_name = :status_name limit 1');
            $Qtransaction_satus_id->bindTable(':table_orders_transactions_status', TABLE_ORDERS_TRANSACTIONS_STATUS);
            $Qtransaction_satus_id->bindInt(':language_id', $osC_Language->getID());
            $Qtransaction_satus_id->bindValue(':status_name', $_POST['payment_status']);
            $Qtransaction_satus_id->execute(); 
            
            $transaction_satus_id = $Qtransaction_satus_id->toArray();
            
            $Qtransaction_satus_id->freeResult();

            //insert the order transactions history
            $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
            $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
            $Qtransaction->bindInt(':orders_id', $_POST['invoice']);
            $Qtransaction->bindInt(':transaction_code', $transaction_satus_id['id']);
            $Qtransaction->bindValue(':transaction_return_value', $this->_transaction_response);
            $Qtransaction->bindInt(':transaction_return_status', 1);
            $Qtransaction->execute();
            
            $Qtransaction->freeResult();
        }
    }
}
?>
