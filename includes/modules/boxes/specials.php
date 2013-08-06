<?php
/*
  $Id: specials.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_specials extends osC_Modules {
    var $_title,
        $_code = 'specials',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'boxes';

    function osC_Boxes_specials() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_specials_heading');
    }

    function initialize() {
      global $osC_Database, $osC_Services, $osC_Currencies, $osC_Cache, $osC_Language, $osC_Image;

      $this->_title_link = osc_href_link(FILENAME_PRODUCTS, 'specials');

      if ($osC_Services->isStarted('specials')) {
        if ((BOX_SPECIALS_CACHE > 0) && $osC_Cache->read('box-specials-' . $osC_Language->getCode() . '-' . $osC_Currencies->getCode(), BOX_SPECIALS_CACHE)) {
          $data = $osC_Cache->getCache();
        } else {
          $Qspecials = $osC_Database->query('select p.products_id, p.products_price, p.products_tax_class_id, pd.products_name, pd.products_keyword, s.specials_new_products_price, i.image from :table_products p left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag), :table_products_description pd, :table_specials s where s.status = 1 and s.products_id = p.products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id order by s.specials_date_added desc limit :max_random_select_specials');
          $Qspecials->bindTable(':table_products', TABLE_PRODUCTS);
          $Qspecials->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
          $Qspecials->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
          $Qspecials->bindInt(':default_flag', 1);
          $Qspecials->bindTable(':table_specials', TABLE_SPECIALS);
          $Qspecials->bindInt(':language_id', $osC_Language->getID());
          $Qspecials->bindInt(':max_random_select_specials', BOX_SPECIALS_RANDOM_SELECT);
          $Qspecials->executeRandomMulti();

          $data = array();

          if ($Qspecials->numberOfRows()) {
            $data = $Qspecials->toArray();

            $data['products_price'] = '<s>' . $osC_Currencies->displayPrice($Qspecials->valueDecimal('products_price'), $Qspecials->valueInt('products_tax_class_id')) . '</s>&nbsp;<span class="productSpecialPrice">' . $osC_Currencies->displayPrice($Qspecials->valueDecimal('specials_new_products_price'), $Qspecials->valueInt('products_tax_class_id')) . '</span>';

            
            $osC_Cache->write('box-specials-' . $osC_Language->getCode() . '-' . $osC_Currencies->getCode(), $data);
          }
        }

        if (empty($data) === false) {
          $this->_content = '';

          if (empty($data['image']) === false) {
            $this->_content = osc_link_object(osc_href_link(FILENAME_PRODUCTS, $data['products_id']), $osC_Image->show($data['image'], $data['products_name'])) . '<br />';
          }

          $this->_content .= '<span>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $data['products_id']), $data['products_name']) . '</span><br /><span class="productPrice">' . $data['products_price'] . '</span>';
        }
      }
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Random Product Specials Selection', 'BOX_SPECIALS_RANDOM_SELECT', '10', 'Select a random product on special from this amount of the newest products on specials available', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Cache Contents', 'BOX_SPECIALS_CACHE', '1', 'Number of minutes to keep the contents cached (0 = no cache)', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('BOX_SPECIALS_RANDOM_SELECT', 'BOX_SPECIALS_CACHE');
      }

      return $this->_keys;
    }
  }
?>
