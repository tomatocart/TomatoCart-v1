<?php
/*
  $Id: products_slider.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Content_products_slider extends osC_Modules {
    var $_title,
        $_code = 'products_slider',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'content';

/* Class constructor */

    function osC_Content_products_slider() {
      global $osC_Language;

      $this->_title = $osC_Language->get('products_slider_title');
    }

    function initialize() {
      global $osC_Database, $osC_Language, $osC_Currencies, $osC_Services, $osC_Specials, $osC_Image, $osC_Template;

      if (MODULE_CONTENT_PRODUCTS_SLIDER_PRODUCTS_TYPE == 'New Products') {
        $this->_title = $osC_Language->get('products_slider_new_products_title');
        $Qproducts = $osC_Database->query('select p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, i.image from :table_products p, :table_products_images i, :table_products_description pd where p.products_status = 1 and p.products_id = pd.products_id and p.products_id = i.products_id and i.default_flag = :default_flag and pd.language_id = :language_id order by p.products_date_added desc limit :max_display_products');
      }else if (MODULE_CONTENT_PRODUCTS_SLIDER_PRODUCTS_TYPE == 'Special Products') {
        $this->_title = $osC_Language->get('products_slider_special_products_title');
        $Qproducts = $osC_Database->query('select p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, i.image, s.specials_new_products_price from :table_products p, :table_products_images i, :table_products_description pd, :table_specials s where s.status = 1 and s.products_id = p.products_id and p.products_status = 1 and p.products_id = pd.products_id and p.products_id = i.products_id and i.default_flag = :default_flag and pd.language_id = :language_id order by s.specials_date_added desc limit :max_display_products');
        $Qproducts->bindTable(':table_specials', TABLE_SPECIALS);
      }else if (MODULE_CONTENT_PRODUCTS_SLIDER_PRODUCTS_TYPE == 'Best Sellers') {
        $this->_title = $osC_Language->get('products_slider_best_sellers_title');
        $Qproducts = $osC_Database->query('select p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, i.image from :table_products p, :table_products_images i, :table_products_description pd where p.products_status = 1 and p.products_id = pd.products_id and p.products_id = i.products_id and i.default_flag = :default_flag and pd.language_id = :language_id and p.products_ordered > 0 order by p.products_ordered desc limit :max_display_products');
      }

      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':default_flag', 1);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->bindInt(':max_display_products', MODULE_CONTENT_PRODUCTS_SLIDER_MAX_DISPLAY);

      $content = '<div id="sliderLeft"><a href="javascript:void(0);">&nbsp;</a></div>';
      $content .=
        '<div id="productsSliderWrapper">' .
          '<div id="productsSlider">';

      while ($Qproducts->next()) {
        $content .= '<span>';
        $products_price = $osC_Currencies->displayPrice($Qproducts->valueDecimal('products_price'), $Qproducts->valueInt('products_tax_class_id'));

        if ($osC_Services->isStarted('specials') && $osC_Specials->isActive($Qproducts->valueInt('products_id'))) {
          $products_price = '<s>' . $products_price . '</s><br/><b class="productSpecialPrice">' . $osC_Currencies->displayPrice($osC_Specials->getPrice($Qproducts->valueInt('products_id')), $Qproducts->valueInt('products_tax_class_id')) . '</b>';
        }

        $image = $Qproducts->value('image');
        if (empty($image) === false) {
          $content .= osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->valueInt('products_id')), $osC_Image->show($image, $Qproducts->value('products_name'), 'hspace="5" vspace="5"', 'thumbnails')) . '<br />';
        }

        $content .= osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->valueInt('products_id')), $Qproducts->value('products_name'), '' , 'thumbnails') . '<br />' . $products_price;
        $content .= '</span>';
      }
      $content .=
          '</div>
        </div>';
      $content .= '<div id="sliderRight"><a href="javascript:void(0);">&nbsp;</a></div>';

      $width = MODULE_CONTENT_PRODUCTS_SLIDER_WIDTH;
      $height = MODULE_CONTENT_PRODUCTS_SLIDER_HEIGHT;
      $direction = MODULE_CONTENT_PRODUCTS_SLIDER_DIRECTION;
      $duration = MODULE_CONTENT_PRODUCTS_SLIDER_DURATION;
      $interval = MODULE_CONTENT_PRODUCTS_SLIDER_INTERVAL;

      $wrapper_width = $width - 60;
      $num_of_images = floor($wrapper_width / ($osC_Image->getWidth('thumbnails') + 30));
      $image_width = floor($wrapper_width / $num_of_images);

      $css = '#sliderLeft, #sliderRight {height:' . $height . 'px;line-height:' . $height . 'px;width:30px;float:left;vertical-align:middle}' . "\n" .
             '#sliderLeft a {background:#fff url(images/arrow_left_gray.gif) no-repeat center center;display:block;width:30px;}' . "\n" .
             '#sliderLeft a:hover {background:#fff url(images/arrow_left_blue.gif) no-repeat center center; text-decoration:none}' . "\n" .
             '#sliderRight a {background:#fff url(images/arrow_right_gray.gif) no-repeat center center;display:block;width:30px;}' . "\n" .
             '#sliderRight a:hover {background:#fff url(images/arrow_right_blue.gif) no-repeat center center; text-decoration:none}' . "\n" .
             '#productsSliderWrapper {position:relative;background:#fff;width:' . $wrapper_width . 'px;height:' . $height . 'px;overflow:hidden;float:left;}' . "\n" .
             '#productsSlider {position:absolute;text-align:center}' . "\n" .
             '#productsSlider span {display:block; float:left;width:' . $image_width . 'px}';

      $osC_Template->addStyleDeclaration($css);
      $osC_Template->addJavascriptFilename('ext/noobslide/noobslide.js');

      $js .= '<script type="text/javascript">
              window.addEvent(\'domready\',function(){

                var slider = new noobSlide({
                    box: $(\'productsSlider\'),
                    items: $$(\'#productsSlider span\'),
                    size: ' . $image_width . ',
                    interval:  ' . $interval . ',
                    fxOptions: {
                      duration: ' . $duration . ',
                      transition: Fx.Transitions.Sine.easeInOut,
                      link: \'cancel\'
                    },
                    autoPlay: true,
                    addButtons: {
                      previous: $(\'sliderRight\'),
                      next: $(\'sliderLeft\')
                    }
                  });
              });
              </script>';
      $this->_content = $js . "\n" . $content;
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Products type', 'MODULE_CONTENT_PRODUCTS_SLIDER_PRODUCTS_TYPE', 'New Products', 'The products type to be displayed in slider', '6', '0', 'osc_cfg_set_boolean_value(array(\'New Products\', \'Special Products\', \'Best Sellers\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum entries to display', 'MODULE_CONTENT_PRODUCTS_SLIDER_MAX_DISPLAY', '20', 'Maximum number of products to display', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Slider width', 'MODULE_CONTENT_PRODUCTS_SLIDER_WIDTH', '530', 'Slider width', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Slider height', 'MODULE_CONTENT_PRODUCTS_SLIDER_HEIGHT', '150', 'Slider height', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Slideshow direction', 'MODULE_CONTENT_PRODUCTS_SLIDER_DIRECTION', 'left', 'Slideshow Direction', '6', '0', 'osc_cfg_set_boolean_value(array(\'left\', \'right\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Slideshow duration', 'MODULE_CONTENT_PRODUCTS_SLIDER_DURATION', '500', 'Slideshow duration', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Slideshow interval', 'MODULE_CONTENT_PRODUCTS_SLIDER_INTERVAL', '3000', 'Slideshow interval', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_CONTENT_PRODUCTS_SLIDER_PRODUCTS_TYPE',
                             'MODULE_CONTENT_PRODUCTS_SLIDER_MAX_DISPLAY',
                             'MODULE_CONTENT_PRODUCTS_SLIDER_WIDTH',
                             'MODULE_CONTENT_PRODUCTS_SLIDER_HEIGHT',
                             'MODULE_CONTENT_PRODUCTS_SLIDER_DIRECTION',
                             'MODULE_CONTENT_PRODUCTS_SLIDER_DURATION',
                             'MODULE_CONTENT_PRODUCTS_SLIDER_INTERVAL');
      }

      return $this->_keys;
    }
  }
?>
