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

require_once realpath(dirname(__FILE__) . '/../../') . '/includes/classes/template_info.php';

/**
 * TomatoCart bootstrap Template
 */
class osC_Template_bootstrap extends osC_TemplateInfo {
    var $_id,
    $_title = 'TomatoCart Bootstrap',
    $_code = 'bootstrap',
    $_author_name = 'TomatoCart',
    $_author_www = 'http://www.tomatocart.com',
    $_markup_version = 'XHTML 1.0 Transitional',
    $_css_based = '1', /* 0=No; 1=Yes*/
    $_medium = 'Screen',
    $_groups = array('boxes' => array('left', 'footer-col-1', 'footer-col-2', 'footer-col-3', 'footer-col-4'),
                         'content' => array('slideshow', 'before', 'after')),
    $_keys,
    $_logo_width = '180',
    $_logo_height = '50';

    function install() {
        global $osC_Database;

        //insert template into database
        $Qinstall = $osC_Database->query('insert into :table_templates (title, code, author_name, author_www, markup_version, css_based, medium) values (:title, :code, :author_name, :author_www, :markup_version, :css_based, :medium)');
        $Qinstall->bindTable(':table_templates', TABLE_TEMPLATES);
        $Qinstall->bindValue(':title', $this->_title);
        $Qinstall->bindValue(':code', $this->_code);
        $Qinstall->bindValue(':author_name', $this->_author_name);
        $Qinstall->bindValue(':author_www', $this->_author_www);
        $Qinstall->bindValue(':markup_version', $this->_markup_version);
        $Qinstall->bindValue(':css_based', $this->_css_based);
        $Qinstall->bindValue(':medium', $this->_medium);
        $Qinstall->execute();

        $id = $osC_Database->nextID();

        $data = array('categories' => array('boxes', '*', 'left', '100'),
        							'compare_products' => array('boxes', '*', 'left', '200'),
                      'manufacturers' => array('boxes', '*', 'left', '300'),
                      'manufacturer_info' => array('boxes', 'products/info', 'left', '400'),
                      'order_history' => array('boxes', '*', 'left', '500'),
                      'best_sellers' => array('boxes', '*', 'left', '600'),
                      'product_notifications' => array('boxes', 'products/info', 'left', '700'),
                      'tell_a_friend' => array('boxes', 'products/info', 'left', '800'),
                      'reviews' => array('boxes', '*', 'left', '900'),
                      'currencies' => array('boxes', '*', 'left', '1000'),
                      
                      'new_products' => array('content', 'index/category_listing', 'after', 100),
                      'new_products' => array('content', 'index/index', 'after', 200),
                      'upcoming_products' => array('content', 'index/index', 'after', 350),
                      'recently_visited' => array('content', '*', 'after', 400),
                      'also_purchased_products' => array('content', 'products/info', 'after', 500),
                        
                      'information' => array('boxes', '*', 'footer-col-1', '100'),
                      'whats_new' => array('boxes', '*', 'footer-col-2', '100'),
                      'specials' => array('boxes', '*', 'footer-col-3', '100'),
                      'slide_show' => array('index/index', 'slideshow', 100));

        $Qboxes = $osC_Database->query('select id, code, modules_group from :table_templates_boxes');
        $Qboxes->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
        $Qboxes->execute();

        while ($Qboxes->next()) {
            $modules_group = $Qboxes->value('modules_group');
            
            if (isset($data[$Qboxes->value('code')]) && ($modules_group == $data[$Qboxes->value('code')][0])) {
                $Qrelation = $osC_Database->query('insert into :table_templates_boxes_to_pages (templates_boxes_id, templates_id, content_page, boxes_group, sort_order, page_specific) values (:templates_boxes_id, :templates_id, :content_page, :boxes_group, :sort_order, :page_specific)');
                $Qrelation->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
                $Qrelation->bindInt(':templates_boxes_id', $Qboxes->valueInt('id'));
                $Qrelation->bindInt(':templates_id', $id);
                $Qrelation->bindValue(':content_page', $data[$Qboxes->value('code')][1]);
                $Qrelation->bindValue(':boxes_group', $data[$Qboxes->value('code')][2]);
                $Qrelation->bindInt(':sort_order', $data[$Qboxes->value('code')][3]);
                $Qrelation->bindInt(':page_specific', 0);
                $Qrelation->execute();
            }
        }

        $this->resizeLogo();
    }

    function remove() {
        global $osC_Database;

        $Qdel = $osC_Database->query('delete from :table_templates_boxes_to_pages where templates_id = :templates_id');
        $Qdel->bindTable(':table_templates_boxes_to_pages', TABLE_TEMPLATES_BOXES_TO_PAGES);
        $Qdel->bindValue(':templates_id', $this->getID());
        $Qdel->execute();

        $Qdel = $osC_Database->query('delete from :table_templates where id = :id');
        $Qdel->bindTable(':table_templates', TABLE_TEMPLATES);
        $Qdel->bindValue(':id', $this->getID());
        $Qdel->execute();

        if ($this->hasKeys()) {
            $Qdel = $osC_Database->query('delete from :table_configuration where configuration_key in (":configuration_key")');
            $Qdel->bindTable(':table_configuration', TABLE_CONFIGURATION);
            $Qdel->bindRaw(':configuration_key', implode('", "', $this->getKeys()));
            $Qdel->execute();
        }

        $this->deleteLogo();
    }

}
?>