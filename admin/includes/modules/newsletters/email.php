<?php
/*
  $Id: email.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Newsletter_email {

/* Private methods */

    var $_title,
        $_has_audience_selection = true,
        $_newsletter_title,
        $_newsletter_content,
        $_newsletter_id,
        $_audience_size = 0;

/* Class constructor */

    function osC_Newsletter_email($title = '', $content = '', $newsletter_id = '') {
      global $osC_Language;

      $this->_title = $osC_Language->get('newsletter_email_title');

      $this->_newsletter_title = $title;
      $this->_newsletter_content = $content;
      $this->_newsletter_id = $newsletter_id;
    }

/* Public methods */

    function getTitle() {
      return $this->_title;
    }

    function hasAudienceSelection() {
      if ($this->_has_audience_selection === true) {
        return true;
      }

      return false;
    }

    function showAudienceSelectionForm() {
      global $osC_Database, $osC_Language, $osC_Template;

      $customers_array = array(array('id' => '***',
                                     'text' => $osC_Language->get('newsletter_email_all_customers')));

      $Qcustomers = $osC_Database->query('select customers_id, customers_firstname, customers_lastname, customers_email_address from :table_customers order by customers_lastname');
      $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomers->execute();

      while ( $Qcustomers->next() ) {
        $customers_array[] = array('id' => $Qcustomers->valueInt('customers_id'),
                                   'text' => $Qcustomers->value('customers_lastname') . ', ' . $Qcustomers->value('customers_firstname') . ' (' . $Qcustomers->value('customers_email_address') . ')');
      }

      $Qcustomers->freeResult();

      $audience_form = '<form name="customers" action="' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&nID=' . $this->_newsletter_id . '&action=send') . '" method="post">' .
                       '  <p align="center">' . osc_draw_pull_down_menu('customer', $customers_array, null, 'size="20" style="width: 100%;"') . '</p>' .
                       '  <p align="right">' . osc_draw_hidden_field('subaction', 'confirm') . '<input type="submit" value="' . $osC_Language->get('button_ok') . '" class="operationButton" />&nbsp;<input type="button" value="' . $osC_Language->get('button_cancel') . '" onclick="document.location.href=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page']) . '\';" class="operationButton" /></p>' .
                       '</form>';

      return $audience_form;
    }

    function showConfirmation() {
      global $osC_Database, $osC_Language, $osC_Template;

      if ( isset($_POST['customer']) && !empty($_POST['customer']) ) {
        $Qcustomers = $osC_Database->query('select count(customers_id) as total from :table_customers c left join :table_newsletters_log nl on (c.customers_email_address = nl.email_address and nl.newsletters_id = :newsletters_id) where nl.email_address is null');
        $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomers->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
        $Qcustomers->bindInt(':newsletters_id', $this->_newsletter_id);

        if ( is_numeric($_POST['customer']) ) {
          $Qcustomers->appendQuery('and c.customers_id = :customers_id');
          $Qcustomers->bindInt(':customers_id', $_POST['customer']);
        }

        $Qcustomers->execute();

        $this->_audience_size += $Qcustomers->valueInt('total');
      }

      $confirmation_string = '<p><font color="#ff0000"><b>' . sprintf($osC_Language->get('newsletter_email_total_recipients'), $this->_audience_size) . '</b></font></p>' .
                             '<p><b>' . $this->_newsletter_title . '</b></p>' .
                             '<p>' . nl2br(osc_output_string_protected($this->_newsletter_content)) . '</p>' .
                             '<form name="confirm" action="' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&nID=' . $this->_newsletter_id . '&action=send') . '" method="post">' .
                             '<p align="right">';

      if ( $this->_audience_size > 0 ) {
        $confirmation_string .= osc_draw_hidden_field('customer', $_POST['customer']) .
                                osc_draw_hidden_field('subaction', 'execute') .
                                '<input type="submit" value="' . $osC_Language->get('button_send') . '" class="operationButton" />&nbsp;';
      }

      $confirmation_string .= '<input type="button" value="' . $osC_Language->get('button_back') . '" onclick="document.location.href=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&nID=' . $this->_newsletter_id . '&action=send') . '\'" class="operationButton" />&nbsp;' .
                              '<input type="button" value="' . $osC_Language->get('button_cancel') . '" onclick="document.location.href=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page']) . '\'" class="operationButton"/></p>' .
                              '</form>';

      return $confirmation_string;
    }

    function sendEmail() {
      global $osC_Database, $osC_Language;

      $max_execution_time = 0.8 * (int)ini_get('max_execution_time');
      $time_start = explode(' ', PAGE_PARSE_START_TIME);

      $audience = array();

      $customer = '';
      if (isset($_POST['customer']) && !empty($_POST['customer'])) {
        $customer = $_POST['customer'];
      } elseif (isset($_GET['customer']) && !empty($_GET['customer'])) {
        $customer = $_GET['customer'];
      }

      if (!empty($customer)) {
        $Qcustomers = $osC_Database->query('select customers_id, customers_firstname, customers_lastname, customers_email_address from :table_customers c left join :table_newsletters_log nl on (c.customers_email_address = nl.email_address and nl.newsletters_id = :newsletters_id) where nl.email_address is null');
        $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomers->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
        $Qcustomers->bindInt(':newsletters_id', $this->_newsletter_id);

        if (is_numeric($customer)) {
          $Qcustomers->appendQuery('and c.customers_id = :customers_id');
          $Qcustomers->bindInt(':customers_id', $customer);
        }

        $Qcustomers->execute();

        while ($Qcustomers->next()) {
          if (!isset($audience[$Qcustomers->valueInt('customers_id')])) {
            $audience[$Qcustomers->valueInt('customers_id')] = array('firstname' => $Qcustomers->value('customers_firstname'),
                                                                     'lastname' => $Qcustomers->value('customers_lastname'),
                                                                     'email_address' => $Qcustomers->value('customers_email_address'));
          }
        }

        $Qcustomers->freeResult();

        if (sizeof($audience) > 0) {
          $osC_Mail = new osC_Mail(null, null, null, EMAIL_FROM, $this->_newsletter_title);
          $osC_Mail->setBodyPlain($this->_newsletter_content);

          foreach ($audience as $key => $value) {
            $osC_Mail->clearTo();
            $osC_Mail->addTo($value['firstname'] . ' ' . $value['lastname'], $value['email_address']);
            $osC_Mail->send();

            $Qlog = $osC_Database->query('insert into :table_newsletters_log (newsletters_id, email_address, date_sent) values (:newsletters_id, :email_address, now())');
            $Qlog->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
            $Qlog->bindInt(':newsletters_id', $this->_newsletter_id);
            $Qlog->bindValue(':email_address', $value['email_address']);
            $Qlog->execute();

            $time_end = explode(' ', microtime());
            $timer_total = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);

            if ($timer_total > $max_execution_time) {
              echo '<p><font color="#38BB68"><b>' . $osC_Language->get('sending_refreshing_page') . '</b></font></p>' .
                   '<META HTTP-EQUIV="refresh" content="2; URL=' . osc_href_link_admin(FILENAME_DEFAULT, 'newsletters&page=' . $_GET['page'] . '&nmID=' . $this->_newsletter_id . '&action=nmSendConfirm&customer=' . $customer) . '">';
              exit;
            }
          }
        }

        $Qupdate = $osC_Database->query('update :table_newsletters set date_sent = now(), status = 1 where newsletters_id = :newsletters_id');
        $Qupdate->bindTable(':table_newsletters', TABLE_NEWSLETTERS);
        $Qupdate->bindInt(':newsletters_id', $this->_newsletter_id);
        $Qupdate->execute();
      }
    }
  }
?>
