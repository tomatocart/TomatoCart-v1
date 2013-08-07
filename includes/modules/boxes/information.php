<?php
/*
  $Id: information.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_information extends osC_Modules {
    var $_title,
        $_code = 'information',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'boxes';

    function osC_Boxes_information() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_information_heading');
    }

    function initialize() {
      global $osC_Database,$osC_Language;

      $Qarticles = $osC_Database->query('select ad.articles_id, ad.articles_name from :table_articles a, :table_articles_description ad where a.articles_id = ad.articles_id and ad.language_id = :language_id and a.articles_status = 1 and a.articles_categories_id = 1 order by a.articles_order');
      $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
      $Qarticles->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
      $Qarticles->bindInt(':language_id', $osC_Language->getID());
      $Qarticles->setCache('box-information-' . $osC_Language->getCode(), 100);
      $Qarticles->execute();

      $this->_content = '<ul>';

      while ($Qarticles->next()) {
        $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id='.$Qarticles->value('articles_id')), $Qarticles->value('articles_name')) . '</li>';
      }
        
      $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_INFO, 'contact'), $osC_Language->get('box_information_contact')) . '</li>';
      $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_INFO, 'sitemap'), $osC_Language->get('box_information_sitemap')) . '</li>';
      $this->_content .= '</ul>';

      $Qarticles->freeResult();
    }
  }
?>
