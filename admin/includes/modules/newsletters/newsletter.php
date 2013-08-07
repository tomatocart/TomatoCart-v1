<?php
/*
  $Id: newsletter.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Newsletter_newsletter {

/* Private methods */

    var $_title,
        $_has_audience_selection = false,
        $_newsletter_title,
        $_newsletter_content,
        $_newsletter_id,
        $_audience_size = 0;

/* Class constructor */

    function osC_Newsletter_newsletter($title = '', $content = '', $newsletter_id = '') {
      global $osC_Language;

      $this->_title = $osC_Language->get('newsletter_newsletter_title');

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
      return false;
    }

    function showConfirmation() {
      global $osC_Database, $osC_Language, $osC_Template;

      $Qrecipients = $osC_Database->query('select count(*) as total from :table_customers c left join :table_newsletters_log nl on (c.customers_email_address = nl.email_address and nl.newsletters_id = :newsletters_id) where c.customers_newsletter = 1 and nl.email_address is null');
      $Qrecipients->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qrecipients->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
      $Qrecipients->bindInt(':newsletters_id', $this->_newsletter_id);
      $Qrecipients->execute();

      $this->_audience_size = $Qrecipients->valueInt('total');

      $confirmation_string = '<p><font color="#ff0000"><b>' . sprintf($osC_Language->get('newsletter_newsletter_total_recipients'), $this->_audience_size) . '</b></font></p>' .
                             '<p><b>' . $this->_newsletter_title . '</b></p>' .
                             '<p>' . nl2br(osc_output_string_protected($this->_newsletter_content)) . '</p>' .
                             '<form name="execute" action="' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&nID=' . $this->_newsletter_id . '&action=send') . '" method="post">' .
                             '<p align="right">';

      if ($this->_audience_size > 0) {
        $confirmation_string .= osc_draw_hidden_field('subaction', 'execute') .
                                '<input type="submit" value="' . $osC_Language->get('button_send') . '" class="operationButton" />&nbsp;' .
                                '<input type="button" value="' . $osC_Language->get('button_cancel') . '" onclick="document.location.href=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page']) . '\'" class="operationButton" />';
      } else {
        $confirmation_string .= '<input type="button" value="' . $osC_Language->get('button_back') . '" onclick="document.location.href=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page']) . '\'" class="operationButton" />';
      }

      $confirmation_string .= '</p>' .
                              '</form>';

      return $confirmation_string;
    }

    function sendEmail() {
      global $osC_Database, $osC_Language, $osC_Template;

      $max_execution_time = 0.8 * (int)ini_get('max_execution_time');
      $time_start = explode(' ', PAGE_PARSE_START_TIME);

      $Qrecipients = $osC_Database->query('select c.customers_firstname, c.customers_lastname, c.customers_email_address from :table_customers c left join :table_newsletters_log nl on (c.customers_email_address = nl.email_address and nl.newsletters_id = :newsletters_id) where c.customers_newsletter = 1 and nl.email_address is null');
      $Qrecipients->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qrecipients->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
      $Qrecipients->bindInt(':newsletters_id', $this->_newsletter_id);
      $Qrecipients->execute();

      if ( $Qrecipients->numberOfRows() > 0 ) {
        $osC_Mail = new osC_Mail(null, null, null, EMAIL_FROM, $this->_newsletter_title);
        $osC_Mail->setBodyPlain($this->_newsletter_content);

        while ( $Qrecipients->next() ) {
          $osC_Mail->clearTo();
          $osC_Mail->addTo($Qrecipients->value('customers_firstname') . ' ' . $Qrecipients->value('customers_lastname'), $Qrecipients->value('customers_email_address'));
          $osC_Mail->send();

          $Qlog = $osC_Database->query('insert into :table_newsletters_log (newsletters_id, email_address, date_sent) values (:newsletters_id, :email_address, now())');
          $Qlog->bindTable(':table_newsletters_log', TABLE_NEWSLETTERS_LOG);
          $Qlog->bindInt(':newsletters_id', $this->_newsletter_id);
          $Qlog->bindValue(':email_address', $Qrecipients->value('customers_email_address'));
          $Qlog->execute();

          $time_end = explode(' ', microtime());
          $timer_total = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);

          if ( $timer_total > $max_execution_time ) {
            echo '<p><font color="#38BB68"><b>' . $osC_Language->get('sending_refreshing_page') . '</b></font></p>' .
                 '<form name="execute" action="' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&nID=' . $this->_newsletter_id . '&action=send') . '" method="post">' .
                 '<p>' . osc_draw_hidden_field('subaction', 'execute') . '</p>' .
                 '</form>' .
                 '<script language="javascript">' .
                 'var counter = 3;' .
                 'function counter() {' .
                 '  count--;' .
                 '  if (count > 0) {' .
                 '    Id = window.setTimeout("counter()", 1000);' .
                 '  } else {' .
                 '    document.execute.submit();' .
                 '  }' .
                 '}' .
                 '</script>';

            exit;
          }
        }

        $Qrecipients->freeResult();
      }

      $Qupdate = $osC_Database->query('update :table_newsletters set date_sent = now(), status = 1 where newsletters_id = :newsletters_id');
      $Qupdate->bindTable(':table_newsletters', TABLE_NEWSLETTERS);
      $Qupdate->bindInt(':newsletters_id', $this->_newsletter_id);
      $Qupdate->execute();
    }
  }
?>
