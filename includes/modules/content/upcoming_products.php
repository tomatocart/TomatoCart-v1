<?php
/*
  $Id: upcoming_products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Content_upcoming_products extends osC_Modules {
    var $_title,
        $_code = 'upcoming_products',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'content';

/* Class constructor */

    function osC_Content_upcoming_products() {
      global $osC_Language;

      $this->_title = $osC_Language->get('upcoming_products_title');
    }

    function initialize() {
      global $osC_Database, $osC_Language, $osC_Currencies, $osC_Image, $osC_Template;

      $Qupcoming = $osC_Database->query('select p.products_id, p.products_price, p.products_tax_class_id, p.products_date_available as date_expected, pd.products_name, pd.products_keyword, s.specials_new_products_price, i.image from :table_products p left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag) left join :table_specials s on (p.products_id = s.products_id and s.status = 1), :table_products_description pd where to_days(p.products_date_available) >= to_days(now()) and p.products_status = :products_status and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_date_available limit :max_display_upcoming_products');
      $Qupcoming->bindTable(':table_products', TABLE_PRODUCTS);
      $Qupcoming->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qupcoming->bindTable(':table_specials', TABLE_SPECIALS);
      $Qupcoming->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qupcoming->bindInt(':default_flag', 1);
      $Qupcoming->bindInt(':products_status', 1);
      $Qupcoming->bindInt(':language_id', $osC_Language->getID());
      $Qupcoming->bindInt(':max_display_upcoming_products', MODULE_CONTENT_UPCOMING_PRODUCTS_MAX_DISPLAY);

      if (MODULE_CONTENT_UPCOMING_PRODUCTS_CACHE > 0) {
        $Qupcoming->setCache('upcoming_products-' . $osC_Language->getCode() . '-' . $osC_Currencies->getCode(), MODULE_CONTENT_UPCOMING_PRODUCTS_CACHE);
      }

      $Qupcoming->execute();
      
      $i = 0;
      if ($Qupcoming->numberOfRows() > 0) {
        $this->_content = '<div class="upcomingProductsModule clearfix">';

        while ($Qupcoming->next()) {
          $osC_Product = new osC_Product($Qupcoming->valueInt('products_id'));
          
          if(($i % 3 == 0) && ($i != 0)) {
            $this->_content .= '<div class="productItem clearLeft">';
          }else {
            $this->_content .= '<div class="productItem">';
          }
          
          $this->_content .= '<div class="productName">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qupcoming->value('products_id')), $Qupcoming->value('products_name')) . '</div>' .
                             '<div>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qupcoming->value('products_id')), $osC_Image->show($Qupcoming->value('image'), $Qupcoming->value('products_name'))) . '</div>' .
                             '<div>' . $osC_Product->getPriceFormated(true) . '</div>' .
                             '<div><strong>' . osC_DateTime::getLong($Qupcoming->value('date_expected')) . '</strong></div>';
                             
          $this->_content .= '</div>';
          
          $i++;
        }

        $this->_content .= '</div>';
        
        //add the css block for this module
        $osC_Template->addStyleDeclaration('.clearLeft{clear:left;}
	                                          .upcomingProductsModule{overflow: auto; height: 100%;}
	                                          .upcomingProductsModule .productItem{width: 32%; float: left; text-align: center; padding: 2px;}
	                                          .upcomingProductsModule .productItem .productName{height:30px;}
	                                          .upcomingProductsModule .productItem div {margin:3px 0;}');
      }
      
      $Qupcoming->freeResult();
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum Entries To Display', 'MODULE_CONTENT_UPCOMING_PRODUCTS_MAX_DISPLAY', '10', 'Maximum number of upcoming products to display', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Cache Contents', 'MODULE_CONTENT_UPCOMING_PRODUCTS_CACHE', '1440', 'Number of minutes to keep the contents cached (0 = no cache)', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_CONTENT_UPCOMING_PRODUCTS_MAX_DISPLAY', 'MODULE_CONTENT_UPCOMING_PRODUCTS_CACHE');
      }

      return $this->_keys;
    }

  }
?>