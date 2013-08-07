<?php
/**
 * TomatoCart Open Source Shopping Cart Solution
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 (2007)
 * as published by the Free Software Foundation.
 *
 * @package      TomatoCart
 * @author       TomatoCart Dev Team
 * @copyright    Copyright (c) 2009 - 2012, TomatoCart. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html
 * @link         http://tomatocart.com
 * @since        Version 1.1.8
 * @filesource
 */

require_once('includes/classes/order.php');
require_once('includes/classes/account.php');
require_once('includes/classes/address_book.php');
require_once('includes/classes/gift_certificates.php');

class toC_Json_Checkout {
    function loadCheckoutMethodForm() {
        global $osC_Language, $osC_Customer, $osC_ShoppingCart, $toC_Json, $osC_Template;

        $osC_Language->load('account');
        $osC_Language->load('checkout');

        ob_start();

        require_once('templates/' . $osC_Template->getCode() . '/modules/checkout_method_form.php');
        $form = ob_get_contents();

        ob_end_clean();

        $response = array('success' => true, 'form' => $form);

        echo $toC_Json->encode($response);
    }

    function loadBillingInformationForm() {
        global $osC_Language, $osC_Customer, $osC_Database, $osC_ShoppingCart, $toC_Json, $osC_Template;

        $osC_Language->load('account');
        $osC_Language->load('checkout');

        ob_start();

        if (isset($_REQUEST['template']) && !empty($_REQUEST['template'])) {
            require_once('templates/' . $_REQUEST['template'] . '/modules/billing_address_details.php');
        } else {
            require_once('includes/modules/billing_address_details.php');
        }

        $form = ob_get_contents();

        ob_end_clean();

        $response = array('success' => true, 'form' => $form);

        echo $toC_Json->encode($response);
    }

    function loadShippingInformationForm() {
        global $toC_Json;

        $form = self::_getShippingInformationForm();

        $response = array('success' => true, 'form' => $form);

        echo $toC_Json->encode($response);
    }

    function loadShippingMethodForm() {
        global $toC_Json;

        $form = self::_getShippingMethodForm();

        $response = array('success' => true, 'form' => $form);

        echo $toC_Json->encode($response);
    }

    function loadPaymentInformationForm() {
        global $toC_Json;

        $form = self::_getPaymentMethodForm();

        $response = array('success' => true, 'form' => $form);

        echo $toC_Json->encode($response);
    }

    function loadOrderConfirmationForm() {
        global $toC_Json;

        $form = self::_getOrderConfirmationForm();

        $response = array('success' => true, 'form' => $form);

        echo $toC_Json->encode($response);
    }

    function saveBillingAddress() {
        global $toC_Json, $osC_Language, $osC_Database, $osC_ShoppingCart, $osC_Customer;

        $data = array();
        $errors = array();

        $osC_Language->load('checkout');

        if (!$osC_Customer->isLoggedOn()) {
            if (!isset($_REQUEST['billing_email_address']) || !(strlen(trim($_REQUEST['billing_email_address'])) >= ACCOUNT_EMAIL_ADDRESS)) {
                $errors[] = sprintf($osC_Language->get('field_customer_email_address_error'), ACCOUNT_EMAIL_ADDRESS);
            } else {
                if (!osc_validate_email_address($_REQUEST['billing_email_address'])) {
                    $errors[] = $osC_Language->get('field_customer_email_address_check_error');
                } else {
                    if (osC_Account::checkDuplicateEntry($_REQUEST['billing_email_address']) === true) {
                        $errors[] = $osC_Language->get('field_customer_email_address_exists_error');
                    } else {
                        $data['email_address'] = $_REQUEST['billing_email_address'];
                    }
                }
            }

            if ( (isset($_REQUEST['billing_password']) === false) || (isset($_REQUEST['billing_password']) && (strlen(trim($_REQUEST['billing_password'])) < ACCOUNT_PASSWORD)) ) {
                $errors[] = sprintf($osC_Language->get('field_customer_password_error'), ACCOUNT_PASSWORD);
            } elseif ( (isset($_REQUEST['billing_confirm_password']) === false) || (isset($_REQUEST['billing_confirm_password']) && (trim($_REQUEST['billing_password']) != trim($_REQUEST['billing_confirm_password']))) ) {
                $errors[] = $osC_Language->get('field_customer_password_mismatch_with_confirmation');
            } else {
                $data['password'] = $_REQUEST['billing_password'];
            }
        }

        if ((!$osC_Customer->isLoggedOn()) || ($osC_Customer->isLoggedOn() && isset($_REQUEST['create_billing_address']) && ($_REQUEST['create_billing_address'] == 1))) {
            if (ACCOUNT_GENDER == '1') {
                if (isset($_REQUEST['billing_gender']) && (($_REQUEST['billing_gender'] == 'm') || ($_REQUEST['billing_gender'] == 'f'))) {
                    $data['gender'] = $_REQUEST['billing_gender'];
                } else {
                    $errors[] = $osC_Language->get('field_customer_gender_error');
                }
            } else {
                $data['gender'] = isset($_REQUEST['billing_gender']) ? $_REQUEST['billing_gender'] : '';
            }

            if (isset($_REQUEST['billing_firstname']) && (strlen(trim($_REQUEST['billing_firstname'])) >= ACCOUNT_FIRST_NAME)) {
                $data['firstname'] = $_REQUEST['billing_firstname'];
            } else {
                $errors[] = sprintf($osC_Language->get('field_customer_first_name_error'), ACCOUNT_FIRST_NAME);
            }

            if (isset($_REQUEST['billing_lastname']) && (strlen(trim($_REQUEST['billing_lastname'])) >= ACCOUNT_LAST_NAME)) {
                $data['lastname'] = $_REQUEST['billing_lastname'];
            } else {
                $errors[] = sprintf($osC_Language->get('field_customer_last_name_error'), ACCOUNT_LAST_NAME);
            }

            if (ACCOUNT_COMPANY > -1) {
                if (isset($_REQUEST['billing_company']) && (strlen(trim($_REQUEST['billing_company'])) >= ACCOUNT_COMPANY)) {
                    $data['company'] = $_REQUEST['billing_company'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_company_error'), ACCOUNT_COMPANY);
                }
            }

            if (isset($_REQUEST['billing_street_address']) && (strlen(trim($_REQUEST['billing_street_address'])) >= ACCOUNT_STREET_ADDRESS)) {
                $data['street_address'] = $_REQUEST['billing_street_address'];
            } else {
                $errors[] = sprintf($osC_Language->get('field_customer_street_address_error'), ACCOUNT_STREET_ADDRESS);
            }

            if (ACCOUNT_SUBURB >= 0) {
                if (isset($_REQUEST['billing_suburb']) && (strlen(trim($_REQUEST['billing_suburb'])) >= ACCOUNT_SUBURB)) {
                    $data['suburb'] = $_REQUEST['billing_suburb'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_suburb_error'), ACCOUNT_SUBURB);
                }
            }

            if (ACCOUNT_POST_CODE > -1) {
                if (isset($_REQUEST['billing_postcode']) && (strlen(trim($_REQUEST['billing_postcode'])) >= ACCOUNT_POST_CODE)) {
                    $data['postcode'] = $_REQUEST['billing_postcode'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_post_code_error'), ACCOUNT_POST_CODE);
                }
            }

            if (isset($_REQUEST['billing_city']) && (strlen(trim($_REQUEST['billing_city'])) >= ACCOUNT_CITY)) {
                $data['city'] = $_REQUEST['billing_city'];
            } else {
                $errors[] = sprintf($osC_Language->get('field_customer_city_error'), ACCOUNT_CITY);
            }

            if (ACCOUNT_STATE >= 0) {
                $Qcheck = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id limit 1');
                $Qcheck->bindTable(':table_zones', TABLE_ZONES);
                $Qcheck->bindInt(':zone_country_id', $_REQUEST['billing_country']);
                $Qcheck->execute();

                $entry_state_has_zones = ($Qcheck->numberOfRows() > 0);

                if ($entry_state_has_zones === true) {
                    $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_code like :zone_code');
                    $Qzone->bindTable(':table_zones', TABLE_ZONES);
                    $Qzone->bindInt(':zone_country_id', $_REQUEST['billing_country']);
                    $Qzone->bindValue(':zone_code', $_REQUEST['billing_state']);
                    $Qzone->execute();

                    if ($Qzone->numberOfRows() === 1) {
                        $data['zone_id'] = $Qzone->valueInt('zone_id');
                    } else {
                        $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_name like :zone_name');
                        $Qzone->bindTable(':table_zones', TABLE_ZONES);
                        $Qzone->bindInt(':zone_country_id', $_REQUEST['billing_country']);
                        $Qzone->bindValue(':zone_name', $_REQUEST['billing_state'] . '%');
                        $Qzone->execute();

                        if ($Qzone->numberOfRows() === 1) {
                            $data['zone_id'] = $Qzone->valueInt('zone_id');
                        } else {
                            $errors[] = $osC_Language->get('field_customer_state_select_pull_down_error');
                        }
                    }
                } else {
                    if (strlen(trim($_REQUEST['billing_state'])) >= ACCOUNT_STATE) {
                        $data['state'] = $_REQUEST['billing_state'];
                    } else {
                        $errors[] = sprintf($osC_Language->get('field_customer_state_error'), ACCOUNT_STATE);
                    }
                }
            } else {
                if (strlen(trim($_REQUEST['billing_state'])) >= ACCOUNT_STATE) {
                    $data['state'] = $_REQUEST['billing_state'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_state_error'), ACCOUNT_STATE);
                }
            }

            if (isset($_REQUEST['billing_country']) && is_numeric($_REQUEST['billing_country']) && ($_REQUEST['billing_country'] >= 1)) {
                $data['country_id'] = $_REQUEST['billing_country'];
            } else {
                $errors[] = $osC_Language->get('field_customer_country_error');
            }

            if (ACCOUNT_TELEPHONE >= 0) {
                if (isset($_REQUEST['billing_telephone']) && (strlen(trim($_REQUEST['billing_telephone'])) >= ACCOUNT_TELEPHONE)) {
                    $data['telephone'] = $_REQUEST['billing_telephone'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_telephone_number_error'), ACCOUNT_TELEPHONE);
                }
            }

            if (ACCOUNT_FAX >= 0) {
                if (isset($_REQUEST['billing_fax']) && (strlen(trim($_REQUEST['billing_fax'])) >= ACCOUNT_FAX)) {
                    $data['fax'] = $_REQUEST['billing_fax'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_fax_number_error'), ACCOUNT_FAX);
                }
            }
        }

        if (sizeof($errors) > 0) {
            $response = array('success' => false, 'errors' => $errors);
        } else {

            $data['ship_to_this_address'] = 0;
            if(isset($_REQUEST['ship_to_this_address']) && ($_REQUEST['ship_to_this_address'] == '1')) {
                $data['ship_to_this_address'] = 1;
            }

            if ($osC_Customer->isLoggedOn()) {
                if(isset($_REQUEST['create_billing_address']) && ($_REQUEST['create_billing_address'] == '1')) {
                    $osC_ShoppingCart->setRawBillingAddress($data);

                    if(isset($_REQUEST['ship_to_this_address']) && ($_REQUEST['ship_to_this_address'] == '1')) {
                        $osC_ShoppingCart->setRawShippingAddress($data);
                    }
                } else {
                    $osC_ShoppingCart->setBillingAddress($_REQUEST['billing_address_id']);

                    if(isset($_REQUEST['ship_to_this_address']) && ($_REQUEST['ship_to_this_address'] == '1')) {
                        $osC_ShoppingCart->setShippingAddress($_REQUEST['billing_address_id']);
                    }
                }
            } else {
                $osC_ShoppingCart->setRawBillingAddress($data);

                if(isset($_REQUEST['ship_to_this_address']) && ($_REQUEST['ship_to_this_address'] == '1')) {
                    $osC_ShoppingCart->setRawShippingAddress($data);
                }
            }

            if ($osC_ShoppingCart->isVirtualCart()) {

                $form = self::_getPaymentMethodForm();

                $response = array('success' => true, 'form' => $form['form'], 'javascript' => $form['javascript']);

            } else if (isset($_REQUEST['ship_to_this_address']) && ($_REQUEST['ship_to_this_address'] == '1')) {

                $form = self::_getShippingMethodForm();

                $response = array('success' => true, 'form' => $form);

            } else {

                $form = self::_getShippingInformationForm();

                $response = array('success' => true, 'form' => $form);

            }
        }

        echo $toC_Json->encode($response);
    }

    function saveShippingAddress() {
        global $toC_Json, $osC_Language, $osC_Database, $osC_ShoppingCart, $osC_Customer, $osC_Currencies;

        $errors = array();
        $data = array();

        $osC_Language->load('checkout');

        if ((!$osC_Customer->isLoggedOn()) || ($osC_Customer->isLoggedOn() && isset($_REQUEST['create_shipping_address']) && ($_REQUEST['create_shipping_address'] == 1))) {
            if (ACCOUNT_GENDER == '1') {
                if (isset($_REQUEST['shipping_gender']) && (($_REQUEST['shipping_gender'] == 'm') || ($_REQUEST['shipping_gender'] == 'f'))) {
                    $data['gender'] = $_REQUEST['shipping_gender'];
                } else {
                    $errors[] = $osC_Language->get('field_customer_gender_error');
                }
            } else {
                $data['gender'] = isset($_REQUEST['shipping_gender']) ? $_REQUEST['shipping_gender'] : '';
            }

            if (isset($_REQUEST['shipping_firstname']) && (strlen(trim($_REQUEST['shipping_firstname'])) >= ACCOUNT_FIRST_NAME)) {
                $data['firstname'] = $_REQUEST['shipping_firstname'];
            } else {
                $errors[] = sprintf($osC_Language->get('field_customer_first_name_error'), ACCOUNT_FIRST_NAME);
            }

            if (isset($_REQUEST['shipping_lastname']) && (strlen(trim($_REQUEST['shipping_lastname'])) >= ACCOUNT_LAST_NAME)) {
                $data['lastname'] = $_REQUEST['shipping_lastname'];
            } else {
                $errors[] = sprintf($osC_Language->get('field_customer_last_name_error'), ACCOUNT_LAST_NAME);
            }

            if (ACCOUNT_COMPANY > -1) {
                if (isset($_REQUEST['shipping_company']) && (strlen(trim($_REQUEST['shipping_company'])) >= ACCOUNT_COMPANY)) {
                    $data['company'] = $_REQUEST['shipping_company'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_company_error'), ACCOUNT_COMPANY);
                }
            }

            if (isset($_REQUEST['shipping_street_address']) && (strlen(trim($_REQUEST['shipping_street_address'])) >= ACCOUNT_STREET_ADDRESS)) {
                $data['street_address'] = $_REQUEST['shipping_street_address'];
            } else {
                $errors[] = sprintf($osC_Language->get('field_customer_street_address_error'), ACCOUNT_STREET_ADDRESS);
            }

            if (ACCOUNT_SUBURB >= 0) {
                if (isset($_REQUEST['shipping_suburb']) && (strlen(trim($_REQUEST['shipping_suburb'])) >= ACCOUNT_SUBURB)) {
                    $data['suburb'] = $_REQUEST['shipping_suburb'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_suburb_error'), ACCOUNT_SUBURB);
                }
            }

            if (ACCOUNT_POST_CODE > -1) {
                if (isset($_REQUEST['shipping_postcode']) && (strlen(trim($_REQUEST['shipping_postcode'])) >= ACCOUNT_POST_CODE)) {
                    $data['postcode'] = $_REQUEST['shipping_postcode'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_post_code_error'), ACCOUNT_POST_CODE);
                }
            }

            if (isset($_REQUEST['shipping_city']) && (strlen(trim($_REQUEST['shipping_city'])) >= ACCOUNT_CITY)) {
                $data['city'] = $_REQUEST['shipping_city'];
            } else {
                $errors[] = sprintf($osC_Language->get('field_customer_city_error'), ACCOUNT_CITY);
            }

            if (ACCOUNT_STATE >= 0) {
                $Qcheck = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id limit 1');
                $Qcheck->bindTable(':table_zones', TABLE_ZONES);
                $Qcheck->bindInt(':zone_country_id', $_REQUEST['shipping_country']);
                $Qcheck->execute();

                $entry_state_has_zones = ($Qcheck->numberOfRows() > 0);

                if ($entry_state_has_zones === true) {
                    $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_code like :zone_code');
                    $Qzone->bindTable(':table_zones', TABLE_ZONES);
                    $Qzone->bindInt(':zone_country_id', $_REQUEST['shipping_country']);
                    $Qzone->bindValue(':zone_code', $_REQUEST['shipping_state']);
                    $Qzone->execute();

                    if ($Qzone->numberOfRows() === 1) {
                        $data['zone_id'] = $Qzone->valueInt('zone_id');
                    } else {
                        $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_name like :zone_name');
                        $Qzone->bindTable(':table_zones', TABLE_ZONES);
                        $Qzone->bindInt(':zone_country_id', $_REQUEST['shipping_country']);
                        $Qzone->bindValue(':zone_name', $_REQUEST['shipping_state'] . '%');
                        $Qzone->execute();

                        if ($Qzone->numberOfRows() === 1) {
                            $data['zone_id'] = $Qzone->valueInt('zone_id');
                        } else {
                            $errors[] = $osC_Language->get('field_customer_state_select_pull_down_error');
                        }
                    }
                } else {
                    if (strlen(trim($_REQUEST['shipping_state'])) >= ACCOUNT_STATE) {
                        $data['state'] = $_REQUEST['shipping_state'];
                    } else {
                        $errors[] = sprintf($osC_Language->get('field_customer_state_error'), ACCOUNT_STATE);
                    }
                }
            } else {
                if (strlen(trim($_REQUEST['shipping_state'])) >= ACCOUNT_STATE) {
                    $data['state'] = $_REQUEST['shipping_state'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_state_error'), ACCOUNT_STATE);
                }
            }

            if (isset($_REQUEST['shipping_country']) && is_numeric($_REQUEST['shipping_country']) && ($_REQUEST['shipping_country'] >= 1)) {
                $data['country_id'] = $_REQUEST['shipping_country'];
            } else {
                $errors[] = $osC_Language->get('field_customer_country_error');
            }

            if (ACCOUNT_TELEPHONE >= 0) {
                if (isset($_REQUEST['shipping_telephone']) && (strlen(trim($_REQUEST['shipping_telephone'])) >= ACCOUNT_TELEPHONE)) {
                    $data['telephone'] = $_REQUEST['shipping_telephone'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_telephone_number_error'), ACCOUNT_TELEPHONE);
                }
            }

            if (ACCOUNT_FAX >= 0) {
                if (isset($_REQUEST['shipping_fax']) && (strlen(trim($_REQUEST['shipping_fax'])) >= ACCOUNT_FAX)) {
                    $data['fax'] = $_REQUEST['shipping_fax'];
                } else {
                    $errors[] = sprintf($osC_Language->get('field_customer_fax_number_error'), ACCOUNT_FAX);
                }
            }
        }

        if (sizeof($errors) > 0) {
            $response = array('success' => false, 'errors' => $errors);
        } else {
            if ($osC_Customer->isLoggedOn()) {
                if(isset($_REQUEST['create_shipping_address']) && ($_REQUEST['create_shipping_address'] == '1')) {
                    $osC_ShoppingCart->setRawShippingAddress($data);
                } else {
                    $osC_ShoppingCart->setShippingAddress($_REQUEST['shipping_address_id']);
                }
            } else {
                $osC_ShoppingCart->setRawShippingAddress($data);
            }

            $form = self::_getShippingMethodForm();

            $response = array('success' => true, 'form' => $form);
        }

        echo $toC_Json->encode($response);
    }

    function saveShippingMethod() {
        global $osC_Language, $osC_ShoppingCart, $osC_Shipping, $toC_Json, $osC_Customer, $osC_Payment, $osC_Currencies;

        $errors = array();

        // load all enabled shipping modules
        if (class_exists('osC_Shipping') === false) {
            require_once('includes/classes/shipping.php');
        }

        $osC_Shipping = new osC_Shipping();

        // if no shipping method has been selected, automatically select the cheapest method.
        //      if ($osC_ShoppingCart->hasShippingMethod() === false) {
        //        $osC_ShoppingCart->setShippingMethod($osC_Shipping->getCheapestQuote());
        //      }

        if (!empty($_POST['shipping_comments'])) {
            $_SESSION['comments'] = osc_sanitize_string($_POST['shipping_comments']);
        }

        if ($osC_Shipping->hasQuotes()) {
            if (isset($_REQUEST['shipping_mod_sel']) && strpos($_REQUEST['shipping_mod_sel'], '_')) {
                list($module, $method) = explode('_', $_REQUEST['shipping_mod_sel']);
                $module = 'osC_Shipping_' . $module;

                if (is_object($GLOBALS[$module]) && $GLOBALS[$module]->isEnabled()) {
                    $quote = $osC_Shipping->getQuote($_REQUEST['shipping_mod_sel']);

                    if (isset($quote['error'])) {
                        $osC_ShoppingCart->resetShippingMethod();

                        $errors[] = $quote['error'];
                    } else {
                        $osC_ShoppingCart->setShippingMethod($quote);
                    }
                } else {
                    $osC_ShoppingCart->resetShippingMethod();
                }
            }
        } else {
            $osC_ShoppingCart->resetShippingMethod();
        }

        //gift wrapping
        if (isset($_POST['gift_wrapping']) && ($_POST['gift_wrapping'] == 'true')) {
            $osC_ShoppingCart->setGiftWrapping(true);

            if (!empty($_POST['gift_wrapping_comments'])) {
                $_SESSION['gift_wrapping_comments'] = osc_sanitize_string($_POST['gift_wrapping_comments']);
            }
        } else {
            $osC_ShoppingCart->setGiftWrapping(false);

            unset($_SESSION['gift_wrapping_comments']);
        }

        if (sizeof($errors) > 0) {
            $response = array('success' => false, 'errors' => $errors);
        } else {
            $form = self::_getPaymentMethodForm();

            $response = array('success' => true, 'form' => $form['form'], 'javascript' => $form['javascript']);
        }

        echo $toC_Json->encode($response);
    }

    function savePaymentMethod() {
        global $osC_Language, $osC_ShoppingCart, $osC_Payment, $messageStack, $toC_Json, $osC_Currencies;

        $errors = array();

        $osC_Language->load('account');
        $osC_Language->load('checkout');
        $osC_Language->load('order');

        if ( (isset($_POST['payment_comments'])) && (isset($_SESSION['payment_comments'])) && (empty($_POST['payment_comments'])) ) {
            unset($_SESSION['comments']);
        } elseif (!empty($_POST['payment_comments'])) {
            $_SESSION['comments'] = osc_sanitize_string($_POST['payment_comments']);
        }

        if (DISPLAY_CONDITIONS_ON_CHECKOUT == '1') {
            if (!isset($_POST['conditions']) || ($_POST['conditions'] != '1')) {
                $errors[] = $osC_Language->get('error_conditions_not_accepted');
            }
        }

        if($osC_ShoppingCart->isTotalZero() == false) {
            // load the selected payment module
            require_once('includes/classes/payment.php');
            $osC_Payment = new osC_Payment((isset($_REQUEST['payment_method']) ? $_REQUEST['payment_method'] : $osC_ShoppingCart->getBillingMethod('id')));

            if (isset($_REQUEST['payment_method'])) {
                $osC_ShoppingCart->setBillingMethod(array('id' => $_REQUEST['payment_method'], 'title' => $GLOBALS['osC_Payment_' . $_REQUEST['payment_method']]->getMethodTitle()));
            }

            if ( $osC_Payment->hasActive() && ((isset($GLOBALS['osC_Payment_' . $osC_ShoppingCart->getBillingMethod('id')]) === false) || (isset($GLOBALS['osC_Payment_' . $osC_ShoppingCart->getBillingMethod('id')]) && is_object($GLOBALS['osC_Payment_' . $osC_ShoppingCart->getBillingMethod('id')]) && ($GLOBALS['osC_Payment_' . $osC_ShoppingCart->getBillingMethod('id')]->isEnabled() === false))) ) {
                $errors[] = $osC_Language->get('error_no_payment_module_selected');
            }

            if ($osC_Payment->hasActive()) {
                $osC_Payment->pre_confirmation_check();
            }

            if ($messageStack->size('checkout_payment') > 0) {
                $errors =  array_merge($errors, $messageStack->getMessages('checkout_payment'));
            }
        } else {
            $osC_ShoppingCart->resetBillingMethod();
        }

        if (sizeof($errors) > 0) {
            $response = array('success' => false, 'errors' => $errors);
        } else {
            $form = toC_Json_Checkout::_getOrderConfirmationForm();

            $response = array('success' => true, 'form' => $form);
        }

        echo $toC_Json->encode($response);
    }

    function saveOrder() {
        global $toC_Json, $osC_Language, $osC_Payment, $osC_ShoppingCart;

        // load selected payment module
        require_once('includes/classes/payment.php');
        $osC_Payment = new osC_Payment($osC_ShoppingCart->getBillingMethod('id'));

        if ($osC_Payment->hasActive() && ($osC_ShoppingCart->hasBillingMethod() === false)) {
            osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment', 'SSL'));
        }

        require_once('includes/classes/order.php');

        $osC_Payment->process();

        $response = array('success' => true);

        echo $toC_Json->encode($response);
    }

    function countryChange() {
        global $toC_Json, $osC_Database, $osC_Language;

        $Qzones = $osC_Database->query('select zone_name from :table_zones where zone_country_id = :zone_country_id order by zone_name');
        $Qzones->bindTable(':table_zones', TABLE_ZONES);
        $Qzones->bindInt(':zone_country_id', $_REQUEST['country_id']);
        $Qzones->execute();

        $zones_array = array();
        while ($Qzones->next()) {
            $zones_array[] = array('id' => $Qzones->value('zone_name'), 'text' => $Qzones->value('zone_name'));
        }

        $html =
          '<label class="control-label" for="billing_state">' . $osC_Language->get('field_customer_state') . ((ACCOUNT_STATE > 0) ? '<em>*</em>' : '') . '</label>' .
          '<div class="controls">';

        if (sizeof($zones_array) > 0) {
            $html .= osc_draw_pull_down_menu($_REQUEST['type'] . '_state', $zones_array);
        } else {
            $html .= osc_draw_input_field($_REQUEST['type'] . '_state');

        }

        $html .= '</div>';

        $response = array('success' => true, 'html' => $html);

        echo $toC_Json->encode($response);
    }

    function redeemGiftCertificate() {
        global $toC_Json, $osC_Language, $osC_Payment, $osC_ShoppingCart, $osC_Currencies;

        $osC_Language->load('checkout');

        $errors = array();

        if ($osC_ShoppingCart->isTotalZero()) {
            $errors[] = $osC_Language->get('error_shopping_cart_total_zero');
        }

        if ($osC_ShoppingCart->containsGiftCertifcate($_POST['gift_certificate_code'])) {
            $errors[] = $osC_Language->get('error_gift_certificate_exist');
        }

        if (!toC_Gift_Certificates::isGiftCertificateValid($_POST['gift_certificate_code'])) {
            $errors[] = $osC_Language->get('error_invalid_gift_certificate');
        }

        if(sizeof($errors) == 0){
            $osC_ShoppingCart->addGiftCertificateCode($_POST['gift_certificate_code']);

            $form = toC_Json_Checkout::_getPaymentMethodForm();

            $response = array('success' => true, 'form' => $form, 'isTotalZero' => $osC_ShoppingCart->isTotalZero());
        } else {
            $response = array('success' => false, 'errors' => $errors);
        }

        echo $toC_Json->encode($response);
    }

    function deleteGiftCertificate() {
        global $toC_Json, $osC_Payment, $osC_ShoppingCart;

        $osC_ShoppingCart->deleteGiftCertificate($_POST['gift_certificate_code']);

        $form = toC_Json_Checkout::_getPaymentMethodForm();

        $response = array('success' => true, 'form' => $form, 'go_to_payment_form' => $go_to_payment_form);

        echo $toC_Json->encode($response);
    }

    function redeemCoupon() {
        global $toC_Json, $osC_Language, $osC_Payment, $osC_ShoppingCart, $osC_Currencies, $osC_Payment;

        $osC_Language->load('checkout');

        require_once('includes/classes/coupon.php');
        $toC_Coupon = new toC_Coupon($_POST['coupon_redeem_code']);

        $errors = array();

        if(!$toC_Coupon->isExist()){
            $errors[] = $osC_Language->get('error_coupon_not_exist');
        }

        if(!$toC_Coupon->isValid()){
            $errors[] = $osC_Language->get('error_coupon_not_valid');
        }

        if(!$toC_Coupon->isDateValid()){
            $errors[] = $osC_Language->get('error_coupon_invalid_date');
        }

        if(!$toC_Coupon->isUsesPerCouponValid()){
            $errors[] = $osC_Language->get('error_coupon_exceed_uses_per_coupon');
        }

        if(!$toC_Coupon->isUsesPerCustomerValid()){
            $errors[] = $osC_Language->get('error_coupon_exceed_uses_per_customer');
        }

        if($toC_Coupon->hasRestrictCategories() || $toC_Coupon->hasRestrictProducts()){
            if(!$toC_Coupon->containRestrictProducts()){
                $errors[] = $osC_Language->get('error_coupon_no_match_products');
            }
        }

        if(!$toC_Coupon->checkMinimumOrderQuantity()){
            $errors[] = $osC_Language->get('error_coupon_minimum_order_quantity');
        }

        if(sizeof($errors) == 0){
            $osC_ShoppingCart->setCouponCode($_POST['coupon_redeem_code']);

            $form = toC_Json_Checkout::_getPaymentMethodForm();

            $response = array('success' => true, 'form' => $form, 'isTotalZero' => $osC_ShoppingCart->isTotalZero());
        } else {
            $response = array('success' => false, 'errors' => $errors);
        }

        echo $toC_Json->encode($response);
    }

    function deleteCoupon() {
        global $toC_Json, $osC_Language, $osC_Payment, $osC_ShoppingCart, $osC_Currencies, $osC_Payment;

        $osC_ShoppingCart->deleteCoupon();

        $form = toC_Json_Checkout::_getPaymentMethodForm();

        $response = array('success' => true, 'form' => $form, 'go_to_payment_form' => $go_to_payment_form);

        echo $toC_Json->encode($response);
    }

    function useStoreCredit() {
        global $toC_Json, $osC_Language, $osC_ShoppingCart, $osC_Customer, $osC_Payment, $osC_Currencies;

        $errors = array();

        if (isset($_REQUEST['value']) && ($_REQUEST['value'] == 'true')) {
            if ($osC_ShoppingCart->isTotalZero()) {
                $errors[] = $osC_Language->get('error_shopping_cart_order_total_zero');
            } else {
                $osC_ShoppingCart->setUseStoreCredit(true);
            }
        } else {
            $osC_ShoppingCart->setUseStoreCredit(false);
        }

        if(sizeof($errors) == 0){
            $response = array('success' => true, 'isTotalZero' => $osC_ShoppingCart->isTotalZero());
        } else {
            $response = array('success' => false, 'errors' => $errors);
        }

        echo $toC_Json->encode($response);
    }

    /*
     * Private
     */

    function _getShippingInformationForm() {
        global $osC_ShoppingCart, $osC_Language, $osC_Database, $osC_Customer, $osC_Currencies;

        $osC_Language->load('checkout');

        ob_start();

        if (isset($_REQUEST['template']) && !empty($_REQUEST['template'])) {
            require_once('templates/' . $_REQUEST['template'] . '/modules/shipping_address_details.php');
        } else {
            require_once('includes/modules/shipping_address_details.php');
        }

        $form = ob_get_contents();

        ob_end_clean();

        return $form;
    }

    function _getShippingMethodForm() {
        global $osC_ShoppingCart, $osC_Customer, $osC_Language, $osC_Currencies;

        $osC_Language->load('checkout');

        if (class_exists('osC_Shipping') === false) {
            require_once('includes/classes/shipping.php');
        }
        $osC_Shipping = new osC_Shipping();

        ob_start();

        //load all order total modules
        if (!class_exists('osC_OrderTotal')) {
            require_once('includes/classes/order_total.php');
        }
        $osC_OrderTotal = new osC_OrderTotal();

        if (isset($_REQUEST['template']) && !empty($_REQUEST['template'])) {
            require_once('templates/' . $_REQUEST['template'] . '/modules/shipping_method_form.php');
        } else {
            require_once('includes/modules/shipping_method_form.php');
        }

        $form = ob_get_contents();

        ob_end_clean();

        return $form;
    }

    function _getPaymentMethodForm() {
        global $osC_ShoppingCart, $osC_Customer, $osC_Currencies, $osC_Language;

        $osC_Customer->synchronizeStoreCreditWithDatabase();

        ob_start();

        // load all enabled payment modules
        require_once('includes/classes/payment.php');
        $osC_Payment = new osC_Payment();

        $osC_Language->load('account');
        $osC_Language->load('checkout');

        if (isset($_REQUEST['template']) && !empty($_REQUEST['template'])) {
            require_once('templates/' . $_REQUEST['template'] . '/modules/payment_method_form.php');
        } else {
            require_once('includes/modules/payment_method_form.php');
        }

        $form = ob_get_contents();

        ob_end_clean();

        $javascript = $osC_Payment->getJavascriptBlocks();

        return array('form' => $form, 'javascript' => $javascript);
    }

    function _getOrderConfirmationForm() {
        global $osC_Language, $osC_ShoppingCart, $osC_Payment, $osC_Currencies, $osC_Tax;

        $osC_Language->load('account');
        $osC_Language->load('checkout');
        $osC_Language->load('order');

        if (!is_object($osC_Payment)) {
            require_once('includes/classes/payment.php');
            $osC_Payment = new osC_Payment($osC_ShoppingCart->getBillingMethod('id'));

            if ($osC_Payment->hasActive()) {
                $osC_Payment->pre_confirmation_check();
            }
        }

        ob_start();

        if (isset($_REQUEST['template']) && !empty($_REQUEST['template'])) {
            require_once('templates/' . $_REQUEST['template'] . '/modules/order_confirmation_form.php');
        } else {
            require_once('includes/modules/order_confirmation_form.php');
        }

        $form = ob_get_contents();

        ob_end_clean();

        return $form;
    }
}
?>