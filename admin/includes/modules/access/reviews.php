<?php
/*
  $Id: reviews.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
  class osC_Access_Reviews extends osC_Access {
    var $_module = 'reviews',
        $_group = 'content',
        $_icon = 'icon-reviews-win',
        $_title,
        $_sort_order = 800;

    function osC_Access_Reviews() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_reviews_ratings_title');
      
      $this->_subgroups = array(array('iconCls' => 'icon-reviews-win',
                                      'shortcutIconCls' => 'icon-reviews-shortcut',
                                      'title' => $osC_Language->get('access_reviews_title'),
                                      'identifier' => 'reviews-win',
                                      'params' => array('set' => 'reviews')), 
                                array('iconCls' => 'icon-ratings-win',
                                      'shortcutIconCls' => 'icon-ratings-shortcut',
                                      'title' => $osC_Language->get('access_ratings_title'),
                                      'identifier' => 'ratings-win',
                                      'params' => array('set' => 'ratings')));
    }
  }
?>
