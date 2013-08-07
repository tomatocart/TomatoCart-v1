<?php
/*
  $Id: products_scroller.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_products_scroller extends osC_Modules {
    var $_title,
        $_code = 'products_scroller',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'boxes';

    function osC_Boxes_products_scroller() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_products_scroller_heading');
    }

    function initialize() {
      global $osC_Cache, $osC_Database, $osC_Services, $osC_Currencies, $osC_Specials, $osC_Language, $osC_Image, $osC_Template;

      $this->_title_link = osc_href_link(FILENAME_PRODUCTS, 'new');

      if (MODULE_BOX_PRODUCTS_SCROLLER_PRODUCTS_TYPE == 'New Products') {
        $this->_title = $osC_Language->get('products_scroller_new_products_title');
        $Qproducts = $osC_Database->query('select p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, i.image from :table_products p, :table_products_images i, :table_products_description pd where p.products_status = 1 and p.products_id = pd.products_id and p.products_id = i.products_id and i.default_flag = :default_flag and pd.language_id = :language_id order by p.products_date_added desc limit :max_display_products');
      }else if (MODULE_BOX_PRODUCTS_SCROLLER_PRODUCTS_TYPE == 'Special Products') {
        $this->_title = $osC_Language->get('products_scroller_special_products_title');
        $Qproducts = $osC_Database->query('select p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, i.image, s.specials_new_products_price from :table_products p, :table_products_images i, :table_products_description pd, :table_specials s where s.status = 1 and s.products_id = p.products_id and p.products_status = 1 and p.products_id = pd.products_id and p.products_id = i.products_id and i.default_flag = :default_flag and pd.language_id = :language_id order by s.specials_date_added desc limit :max_display_products');
        $Qproducts->bindTable(':table_specials', TABLE_SPECIALS);
      }else if (MODULE_BOX_PRODUCTS_SCROLLER_PRODUCTS_TYPE == 'Best Sellers') {
        $this->_title = $osC_Language->get('products_scroller_best_sellers_title');
        $Qproducts = $osC_Database->query('select p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, i.image from :table_products p, :table_products_images i, :table_products_description pd where p.products_status = 1 and p.products_id = pd.products_id and p.products_id = i.products_id and i.default_flag = :default_flag and pd.language_id = :language_id and p.products_ordered > 0 order by p.products_ordered desc limit :max_display_products');
      }

      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':default_flag', 1);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->bindInt(':max_display_products', MODULE_BOX_PRODUCTS_SCROLLER_MAX_DISPLAY);

      $content  =
        '<div id="productsScrollerWrapper">
          <div id="productsScroller">';
      while ($Qproducts->next()) {
        $content .= '<span>';
        $products_price = '<b>' . $osC_Currencies->displayPrice($Qproducts->valueDecimal('products_price'), $Qproducts->valueInt('products_tax_class_id')) . '</b>';

        if ($osC_Services->isStarted('specials') && $osC_Specials->isActive($Qproducts->valueInt('products_id'))) {
          $products_price = '<s>' . $products_price . '</s><br/><font class="productSpecialPrice">' . $osC_Currencies->displayPrice($osC_Specials->getPrice($Qproducts->valueInt('products_id')), $Qproducts->valueInt('products_tax_class_id')) . '</font>';
        }

        $image = $Qproducts->value('image');
        if (empty($image) === false) {
          $content .= osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->valueInt('products_id')), $osC_Image->show($image, $Qproducts->value('products_name'), 'hspace="5" vspace="5"', 'thumbnails')) . '<br />';
        }

        $content .= osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->valueInt('products_id')), $Qproducts->value('products_name')) . '<br />' . $products_price;
        $content .= '</span>';
      }
      $content .=
          '</div>
        </div>';

      $width = MODULE_BOX_PRODUCTS_SCROLLER_WIDTH;
      $height = MODULE_BOX_PRODUCTS_SCROLLER_HEIGHT;
      $direction = MODULE_BOX_PRODUCTS_SCROLLER_DIRECTION;
      $duration = MODULE_BOX_PRODUCTS_SCROLLER_DURATION;
      $interval = MODULE_BOX_PRODUCTS_SCROLLER_INTERVAL;

      $num_of_images = floor($height / ($osC_Image->getHeight('thumbnails') + 40));
      $image_height = floor($height / $num_of_images + 40);

      $css = '#productsScrollerWrapper {position:relative;width:' . $width . 'px;height:' . $height . 'px;overflow:hidden;}' . "\n" .
             '#productsScroller {position:absolute;text-align:center;width:' . $width . 'px;}' . "\n" .
             '#productsScroller span {display:block;height:' . $image_height . 'px}';

      $osC_Template->addStyleDeclaration($css);
      $osC_Template->addJavascriptFilename('ext/noobslide/noobslide.js');

      $js .= '<script type="text/javascript">
              window.addEvent(\'domready\',function(){
                var scroller = new noobSlide({
                    box: $(\'productsScroller\'),
                    mode: \'vertical\',
                    items: $$(\'#productsScroller span\'),
                    size: ' . $image_height . ',
                    interval:  ' . $interval . ',
                    autoPlay: true
                  });
              });
              </script>';
      $this->_content = $js . "\n" . $content;
    }

      function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum entries to display', 'MODULE_BOX_PRODUCTS_SCROLLER_MAX_DISPLAY', '10', 'Maximum number of new products to display', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Products type', 'MODULE_BOX_PRODUCTS_SCROLLER_PRODUCTS_TYPE', 'Best Sellers', 'The products type to be displayed in slider', '6', '0', 'osc_cfg_set_boolean_value(array(\'New Products\', \'Special Products\', \'Best Sellers\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Scroll Width', 'MODULE_BOX_PRODUCTS_SCROLLER_WIDTH', '200', 'the width of the scroller (px)', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Scroll Height', 'MODULE_BOX_PRODUCTS_SCROLLER_HEIGHT', '620', 'the height of the scroller (px)', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Scroll Direction', 'MODULE_BOX_PRODUCTS_SCROLLER_DIRECTION', 'up', 'The direction of the scroller', '6', '0', 'osc_cfg_set_boolean_value(array(\'up\', \'down\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Scroll Duration', 'MODULE_BOX_PRODUCTS_SCROLLER_DURATION', '500', 'The duration of the scroller', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Scroll Interval', 'MODULE_BOX_PRODUCTS_SCROLLER_INTERVAL', '3000', 'The interval of the scroller', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_BOX_PRODUCTS_SCROLLER_PRODUCTS_TYPE',
                             'MODULE_BOX_PRODUCTS_SCROLLER_MAX_DISPLAY',
                             'MODULE_BOX_PRODUCTS_SCROLLER_WIDTH',
                             'MODULE_BOX_PRODUCTS_SCROLLER_HEIGHT',
                             'MODULE_BOX_PRODUCTS_SCROLLER_DIRECTION',
                             'MODULE_BOX_PRODUCTS_SCROLLER_DURATION',
                             'MODULE_BOX_PRODUCTS_SCROLLER_INTERVAL');
      }

      return $this->_keys;
    }
  }
?>
