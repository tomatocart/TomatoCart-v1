<?php
/*
  $Id: faqs.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_faqs extends osC_Modules {
    var $_title,
        $_code = 'faqs',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'boxes';

    function osC_Boxes_faqs() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_faqs_heading');
    }

    function initialize() {
      global $osC_Database, $osC_Language, $current_category_id;

      $this->_title_link = osc_href_link(FILENAME_INFO,'faqs');
      
      $Qfaqs = $osC_Database->query('select distinct f.faqs_id, fd.faqs_question from :table_faqs f, :table_faqs_description fd where f.faqs_status = 1 and f.faqs_id = fd.faqs_id and fd.language_id = :language_id order by f.faqs_order desc, fd.faqs_question limit :max_display_faqs');
      $Qfaqs->bindTable(':table_faqs', TABLE_FAQS);
      $Qfaqs->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
      $Qfaqs->bindInt(':language_id', $osC_Language->getID());
      $Qfaqs->bindInt(':max_display_faqs', BOX_FAQ_MAX_LIST);
      $Qfaqs->setCache('box-faqs-' . $osC_Language->getCode());
      
      $Qfaqs->execute();

      if ($Qfaqs->numberOfRows() >= 0) {
        $this->_content = '<ul>';
        while ($Qfaqs->next()) {
          $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_INFO, 'faqs&faqs_id='.$Qfaqs->value('faqs_id')), $Qfaqs->value('faqs_question')) . '</li>';
        }
        $this->_content .= '</ul>';
      }
      $Qfaqs->freeResult();
    }

    function install() {
      global $osC_Database;

      parent::install();
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum List Size', 'BOX_FAQ_MAX_LIST', '10', 'Maximum amount of faq to show in the listing', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('BOX_FAQ_MAX_LIST');
      }

      return $this->_keys;
    }
  }
?>
