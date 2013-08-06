<?php
/*
  $Id: new_order_created.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class toC_Email_Template_new_order_created extends toC_Email_Template {

/* Private variables */

    var $_template_name = 'new_order_created',
        $_keywords = array( '%%order_number%%',
                            '%%invoice_link%%',
                            '%%date_ordered%%',
                            '%%order_details%%',
                            '%%delivery_address%%',
                            '%%billing_address%%',
                            '%%order_status%%',
                            '%%order_comments%%',
                            '%%store_name%%',
                            '%%store_owner_email_address%%');

/* Class constructor */

    function toC_Email_Template_new_order_created() {
      parent::toC_Email_Template($this->_template_name);
    }


/* Private methods */

  function setData($order_id){
      $this->_order_id = $order_id;
  }

    function buildMessage() {
    global $osC_Database, $osC_Language, $osC_Currencies;

      $Qorder = $osC_Database->query('select * from :table_orders where orders_id = :orders_id limit 1');
      $Qorder->bindTable(':table_orders', TABLE_ORDERS);
      $Qorder->bindInt(':orders_id', $this->_order_id);
      $Qorder->execute();

      if ($Qorder->numberOfRows() === 1) {
        $this->addRecipient($Qorder->value('customers_name'), $Qorder->value('customers_email_address'));

        $order_number = $this->_order_id;
        $invoice_link = osc_href_link(FILENAME_ACCOUNT, 'orders=' . $this->_order_id, 'SSL', false, true, true);
        $date_ordered = osC_DateTime::getLong();

        $order_details = $osC_Language->get('email_order_products') . "<br />" . $osC_Language->get('email_order_separator') . "<br />";
        $Qproducts = $osC_Database->query('select orders_products_id, products_sku, products_name, final_price, products_tax, products_quantity from :table_orders_products where orders_id = :orders_id order by orders_products_id');
        $Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
        $Qproducts->bindInt(':orders_id', $this->_order_id);
        $Qproducts->execute();

        while ($Qproducts->next()) {
          $order_details .= $Qproducts->valueInt('products_quantity') . ' x ' . $Qproducts->value('products_name') . ' (' . $Qproducts->value('products_sku') . ') = ' . $osC_Currencies->displayPriceWithTaxRate($Qproducts->value('final_price'), $Qproducts->value('products_tax'), $Qproducts->valueInt('products_quantity'), $Qorder->value('currency'), $Qorder->value('currency_value')) . "<br />";

          $Qvariants = $osC_Database->query('select products_variants_groups as groups_name, products_variants_values as values_name from :table_orders_products_variants where orders_id = :orders_id and orders_products_id = :orders_products_id order by orders_products_variants_id');
          $Qvariants->bindTable(':table_orders_products_variants', TABLE_ORDERS_PRODUCTS_VARIANTS);
          $Qvariants->bindInt(':orders_id', $this->_order_id);
          $Qvariants->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
          $Qvariants->execute();

          while ($Qvariants->next()) {
            $order_details .= "\t" . $Qvariants->value('groups_name') . ': ' . $Qvariants->value('values_name') . "<br />";
          }
        }

        unset($Qproducts);
        unset($Qvariants);

        $order_details .= $osC_Language->get('email_order_separator') . "<br />";

        $Qtotals = $osC_Database->query('select title, text from :table_orders_total where orders_id = :orders_id order by sort_order');
        $Qtotals->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
        $Qtotals->bindInt(':orders_id', $this->_order_id);
        $Qtotals->execute();

        while ($Qtotals->next()) {
          $order_details .= strip_tags($Qtotals->value('title') . ' ' . $Qtotals->value('text')) . "<br />";
        }

        unset($Qtotals);

        if ( (osc_empty($Qorder->value('delivery_name') === false)) && (osc_empty($Qorder->value('delivery_street_address') === false)) ) {
          $address = array('name' => $Qorder->value('delivery_name'),
                           'company' => $Qorder->value('delivery_company'),
                           'street_address' => $Qorder->value('delivery_street_address'),
                           'suburb' => $Qorder->value('delivery_suburb'),
                           'city' => $Qorder->value('delivery_city'),
                           'state' => $Qorder->value('delivery_state'),
                           'zone_code' => $Qorder->value('delivery_state_code'),
                           'country_title' => $Qorder->value('delivery_country'),
                           'country_iso2' => $Qorder->value('delivery_country_iso2'),
                           'country_iso3' => $Qorder->value('delivery_country_iso3'),
                           'postcode' => $Qorder->value('delivery_postcode'),
                           'format' => $Qorder->value('delivery_address_format'));

          $delivery_address = osC_Address::format($address, "<br />");

          unset($address);
        }

        $address = array('name' => $Qorder->value('billing_name'),
                         'company' => $Qorder->value('billing_company'),
                         'street_address' => $Qorder->value('billing_street_address'),
                         'suburb' => $Qorder->value('billing_suburb'),
                         'city' => $Qorder->value('billing_city'),
                         'state' => $Qorder->value('billing_state'),
                         'zone_code' => $Qorder->value('billing_state_code'),
                         'country_title' => $Qorder->value('billing_country'),
                         'country_iso2' => $Qorder->value('billing_country_iso2'),
                         'country_iso3' => $Qorder->value('billing_country_iso3'),
                         'postcode' => $Qorder->value('billing_postcode'),
                         'format' => $Qorder->value('billing_address_format'));

        $billing_address = osC_Address::format($address, "<br />");

        unset($address);

        $Qstatus = $osC_Database->query('select orders_status_name from :table_orders_status where orders_status_id = :orders_status_id and language_id = :language_id');
        $Qstatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
        $Qstatus->bindInt(':orders_status_id', $Qorder->valueInt('orders_status'));
        $Qstatus->bindInt(':language_id', $osC_Language->getID());
        $Qstatus->execute();

        $order_status = $Qstatus->value('orders_status_name');

        unset($Qstatus);

        $Qstatuses = $osC_Database->query('select date_added, comments from :table_orders_status_history where orders_id = :orders_id and comments != "" order by orders_status_history_id');
        $Qstatuses->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
        $Qstatuses->bindInt(':orders_id', $this->_order_id);
        $Qstatuses->execute();

        $order_comments = '';
        while ($Qstatuses->next()) {
          $order_comments .= osC_DateTime::getLong($Qstatuses->value('date_added')) . "<br />\t" . wordwrap(str_replace("<br />", "<br />\t", $Qstatuses->value('comments')), 60, "<br />\t", 1) . "<br /><br />";
        }

        unset($Qstatuses);

        $replaces = array($order_number, $invoice_link, $date_ordered, $order_details, $delivery_address, $billing_address, $order_status, $order_comments, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

        $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
        $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
      }
      unset($Qorder);
    }
  }
?>
