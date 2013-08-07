<?php
/*
  $Id: recently_visited.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Content_recently_visited extends osC_Modules {
    var $_title,
        $_code = 'recently_visited',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'content';

/* Class constructor */

    function osC_Content_recently_visited() {
      global $osC_Language;

      $this->_title = $osC_Language->get('recently_visited_title');
    }

    function initialize() {
      global $osC_Services, $osC_RecentlyVisited, $osC_Language, $osC_Image;
      
      if ($osC_Services->isStarted('recently_visited') && $osC_RecentlyVisited->hasHistory()) {
        $this->_content = '<table border="0" width="100%" cellspacing="3" cellpadding="2">' .
                          '  <tr>';

        if ($osC_RecentlyVisited->hasProducts()) {
          $this->_content .= '    <td valign="top">' .
                             '      <h6>' . $osC_Language->get('recently_visited_products_title') . '</h6>' .
                             '      <ol style="list-style: none; margin: 0; padding: 0;">';

          foreach ($osC_RecentlyVisited->getProducts() as $product) {
            $this->_content .= '<li style="padding-bottom: 10px;">';

            if (SERVICE_RECENTLY_VISITED_SHOW_PRODUCT_IMAGES == '1') {
              $this->_content .= '<span style="float: left; width: ' . ($osC_Image->getWidth('mini') + 10) . 'px; margin-right: 5px">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $product['id']), $osC_Image->show($product['image'], $product['name'], null, 'mini')) . '</span>';
            }

            $this->_content .= '<div style="">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $product['id']), $product['name']) . '<br />';

            if (SERVICE_RECENTLY_VISITED_SHOW_PRODUCT_PRICES == '1') {
              $this->_content .= $product['price'] . '&nbsp;';
            }

            $this->_content .= '<i>(' . sprintf($osC_Language->get('recently_visited_item_in_category'), osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $product['category_path']), $product['category_name'])) . ')</i></div>' .
                               '<div style="clear: both;"></div>' .
                               '</li>';
          }

          $this->_content .= '      </ol>' .
                             '    </td>';
        }

        if ($osC_RecentlyVisited->hasCategories()) {
          $this->_content .= '      <td valign="top">' .
                             '        <h6>' . $osC_Language->get('recently_visited_categories_title') . '</h6>' .
                             '        <ol style="list-style: none; margin: 0; padding: 0;">';

          foreach ($osC_RecentlyVisited->getCategories() as $category) {
            $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $category['path']), $category['name']);

            if (!empty($category['parent_id'])) {
              $this->_content .= '&nbsp;<i>(' . sprintf($osC_Language->get('recently_visited_item_in_category'), osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $category['parent_id']), $category['parent_name'])) . ')</i>';
            }

            $this->_content .= '</li>';
          }

          $this->_content .= '      </ol>' .
                             '    </td>';
        }

        if ($osC_RecentlyVisited->hasSearches()) {
          $this->_content .= '      <td valign="top">' .
                             '        <h6>' . $osC_Language->get('recently_visited_searches_title') . '</h6>' .
                             '        <ol style="list-style: none; margin: 0; padding: 0;">';

          foreach ($osC_RecentlyVisited->getSearches() as $searchphrase) {
            $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_SEARCH, 'keywords=' . $searchphrase['keywords']), osc_output_string_protected($searchphrase['keywords'])) . ' <i>(' . number_format($searchphrase['results']) . ' results)</i></li>';
          }

          $this->_content .= '      </ol>' .
                             '    </td>';
        }

        $this->_content .= '  </tr>' .
                           '</table>';
      }
    }
  }
?>
