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

/**
 * Get language code
 * 
 * @access public
 * @return string
 */
function get_lang_code() {
    global $osC_Language;
    
    $code = explode('_', $osC_Language->getCode());

    return $code[0];
}

/**
 * Echo Site logo
 * 
 * @access public
 * @return string
 */
function site_logo() {
    global $osC_Template;
    
    $logo = $osC_Template->getLogo();
    $logo = ($logo == 'images/store_logo.png') ? 'templates/' . $osC_Template->getCode() . '/images/store_logo.png' : $logo;

    return osc_link_object(osc_href_link(FILENAME_DEFAULT), osc_image($logo, STORE_NAME), 'id="siteLogo"');
}

/**
 * Build categories dropdown menu
 * 
 * @access public
 * @param $categories 
 * @param $data
 * @param $level
 * @param $parents_id
 * @return string
 */
function build_categories_dropdown_menu($parents_id = 0, $categories = null, $data = null, $level = 0) {
    global $osC_CategoryTree;
    
    //if it is top category
    if ($parents_id == 0) {
        $data = $osC_CategoryTree->data;
        $categories = $data[0];
        $result = '<ul class="nav">';
    } else {
        $result = ($parents_id == 0) ? '<ul role="menu" class="dropdown-menu" aria-labelledby="drop' . $parents_id . '">' : '<ul class="dropdown-menu">';
    }
    
    //add menu items
    if (is_array($categories) && !empty($categories)) {
        foreach ($categories as $categories_id => $categories) {
            $has_sub_category = in_array($categories_id, array_keys($data));
            $name = (($parents_id == 0) && ($has_sub_category == TRUE)) ? $categories['name']  . '&nbsp;&nbsp;<b class="caret"></b>' : $categories['name'];
            
            //li element
            if ($parents_id == 0) {
                $result .= ($has_sub_category == TRUE) ? '<li class="dropdown">' : '<li>';
            } else {
                $result .= ($has_sub_category == TRUE) ? '<li class="dropdown-submenu">' : '<li>';
            } 
            
            $link_attributes = (($parents_id == 0) && ($has_sub_category == TRUE)) ? 'data-toggle="dropdown" class="dropdown-toggle" role="button" id="drop' . $categories_id . '"' : '';
            
            $result .= osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $categories_id), $name, $link_attributes);
            
            if($has_sub_category) 
            {
                $result .= build_categories_dropdown_menu($categories_id, $data[$categories_id], $data, $level + 1);
            }
            
            $result .=  '</li>';
        }
    }
    
    $result .= '</ul>';
    
    return $result;
}

/**
 * Output header javascripts
 * 
 * @access public
 * @return void
 */
function output_javascripts() {
    global $osC_Template;
    
    $javascript_filenames = array();
    
    //get header javascript
    if ( isset($osC_Template->_header_javascript_filenames) && is_array($osC_Template->_header_javascript_filenames) ) {
        $javascript_filenames = array_merge($javascript_filenames, $osC_Template->_header_javascript_filenames);
    }
    
    //get header javascript
    if ( isset($osC_Template->_javascript_filenames) && is_array($osC_Template->_javascript_filenames) ) {
        $javascript_filenames = array_merge($javascript_filenames, $osC_Template->_javascript_filenames);
    }
    
    //add mootools 1.2.6
    $js_files = '<script type="text/javascript" src="templates/' . $osC_Template->getCode() . '/javascript/mootools-1.2.6.min.js"></script>' . "\n";
    
    //add other javascript files
    foreach ($javascript_filenames as $filename) {
        //if it is mootools javascript, omit it
        if (strpos($filename, 'mootools') === FALSE || strpos($filename, 'ajax_shopping_cart') === FALSE) {
            $js_files .= '<script type="text/javascript" src="' . $filename . '"></script>' . "\n";
        } 
    }
    
    echo $js_files;
    
    //output header php javascript
    if (!empty($osC_Template->_header_javascript_php_filenames)) {
        $osC_Template->_getHeaderJavascriptPhpFilenames();
    }
    
    //ouput header javascript bolck
    if (!empty($osC_Template->_header_javascript_blocks)) {
        echo $osC_Template->_getHeaderJavascriptBlocks();
    }
    
    //output php javascript
    if (!empty($osC_Template->_javascript_php_filenames)) {
        $osC_Template->_getJavascriptPhpFilenames();
    }
    
    //ouput javascript bolck
    if (!empty($osC_Template->_javascript_blocks)) {
        echo $osC_Template->_getJavascriptBlocks();
    }
}

/**
 * Return an array of products listing sort parameters
 * 
 * @access public
 * @return array
 */
function get_products_listing_sort() {
    global $osC_Language;
    
    return array(array('id' => 'name', 'text' => $osC_Language->get('product') . ' (ASC)'), 
                 array('id' => 'name|d', 'text' => $osC_Language->get('product') . ' (DESC)'), 
                 array('id' => 'price', 'text' => $osC_Language->get('listing_price_heading') . ' (ASC)'), 
                 array('id' => 'price|d', 'text' => $osC_Language->get('listing_price_heading') . ' (DESC)'));
}


/**
 * Return an array of products listing sort parameters
 * 
 * @access public
 * @return array
 */
function get_products_listing_view_type() {
    //check view type
    $view_type = 'list';
    if (isset($_GET['view'])) {
        if ($_GET['view'] == 'grid') {
            $view_type = 'grid';
        }
    } else if (isset($_SESSION['view_type'])) {
        if ($_SESSION['view_type'] == 'grid') {
            $view_type = 'grid';
        }
    }
    //set view type to session
    $_SESSION['view_type'] = $view_type;
    
    return $view_type;
}

/**
 * Generate the filters params for the product listing page
 *
 * @access public
 *
 * @return mixed
 */
function get_filters_params() {
  global $cPath, $osC_Services, $osC_Template;

  $params = '';
  
  //pass the manufacturers or cPath param as seo is disabled
  if (!$osC_Services->isStarted('sefu') || $osC_Template->getGroup() == 'search') {
    if (isset($_GET['manufacturers']) && !empty($_GET['manufacturers'])) {
      $params .= osc_draw_hidden_field('manufacturers', $_GET['manufacturers']);
    } else if (isset($_GET['cPath']) && !empty($_GET['cPath'])) {
      $params .= osc_draw_hidden_field('cPath', $cPath);
    }
  }
  
  //pass the cpath or manufacturer filter for the search result page
  if ($osC_Template->getGroup() == 'search') {
    if (isset($_GET['filter']) && !empty($_GET['filter'])) {
      $params .= osc_draw_hidden_field('filter', $_GET['filter']);
    }
  }
 
  
  $params .= osc_draw_hidden_session_id_field();
  
  //whether the products attributes filter and the category/manufacturer filter is linked
  if (defined('PRODUCT_LINK_FILTER') && (PRODUCT_LINK_FILTER == '1')) {
    if (isset($_GET['products_attributes']) && is_array($_GET['products_attributes'])) {
      foreach($_GET['products_attributes'] as $att_value_id => $att_value) {
        $params .= osc_draw_hidden_field('products_attributes[' . $att_value_id . ']', $att_value);
      }
    }
  }
  
  //pass search params
  $keys = array('keywords', 'pfrom', 'pto', 'datefrom_days', 'datefrom_months', 'datefrom_years', 'dateto_days', 'dateto_months', 'dateto_years');
  foreach ($keys as $key) {
    if (isset($_GET[$key])) {
      $params .= osc_draw_hidden_field($key, $_GET[$key]);
    }
  }
  
  return $params;
}

/**
 * Show the product images for the product listing page
 *
 * @access public
 *
 * @return string
 */
function show_products_listing_image($image, $title, $parameters = '', $group = '', $type = 'products') {
	global $osC_Image;
	
	if (empty($group) || !$osC_Image->exists($group)) {
		$group = $osC_Image->getCode(DEFAULT_IMAGE_GROUP_ID);
	}
	
	$group_id = $osC_Image->getID($group);
	
	$width = $height = '';
	
	if ( ($osC_Image->_groups[$group_id]['force_size'] == '1') || empty($image) ) {
		$width = $osC_Image->_groups[$group_id]['size_width'];
		$height = $osC_Image->_groups[$group_id]['size_height'];
	}
	
	if (empty($image)) {
		$image = 'no_image.png';
	} else {
		$image = $type . '/' . $osC_Image->_groups[$group_id]['code'] . '/' . $image;
	}
	
	return osc_image(DIR_WS_IMAGES . $image, $title, $width, $height, $parameters);
}