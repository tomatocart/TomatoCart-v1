<?php
/*
  $Id: rss.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_RSS {

  function getCategories() {
    global $osC_Database, $osC_Language;
    
    $Qcategories = $osC_Database->query('select categories_id, categories_name from :table_categories_description where language_id = :language_id order by categories_name');
    $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
    $Qcategories->bindInt(':language_id', $osC_Language->getID());
    $Qcategories->execute();
    
    return $Qcategories;
  }
  
  function buildCategoriesRSS($categories_id) {
    global $osC_Database, $osC_Language, $osC_Image, $osC_CategoryTree;
    
    $rss = array();
    $rss['rss'] = array();
    $rss['rss attr'] = array('xmlns:atom'=>'http://www.w3.org/2005/Atom','version'=>'2.0');
    
    //channel
    $rss['rss']['channel'] = array(
      'title' => '<![CDATA[' . $osC_CategoryTree->getCategoryName($categories_id) . ']]>', 
      'link' => '<![CDATA[' . osc_href_link(FILENAME_DEFAULT, '&cPath=' . $categories_id, 'NONSSL', false, false, true) .']]>',
      'description' => '<![CDATA[' . $osC_CategoryTree->getCategoryName($categories_id) . ']]>',
      'pubDate' => date("D, d M Y H:i:s O"));
    
    //get sub categories
    $categories = array($categories_id);
    $osC_CategoryTree->getChildren($categories_id, $categories);
    
    //items
    $QProduct = $osC_Database->query('select products_id from :table_products_to_categories  where categories_id IN (' . implode(',', $categories) . ')');
    $QProduct->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
    $QProduct->execute();
    
    $items = array();
    while($QProduct->next()){
      $osC_Product = new osC_Product($QProduct->valueInt('products_id'));
      
      $link = osc_href_link(FILENAME_PRODUCTS, $QProduct->valueInt('products_id') . '&cPath=' . $categories_id, 'NONSSL', false, false, true);
      
      $description = '
        <![CDATA[
        <table>
          <tr>
            <td align="center" valign="top">' . osc_link_object($link, osc_image($osC_Image->getImageUrl($osC_Product->getImage(), 'product_info'), $osC_Product->getTitle())) . '</td>
            <td valign="top">' . $osC_Product->getDescription() . '</td>
          </tr>
        </table>
        ]]>';
      
      $items[] = array(
        'title' => '<![CDATA[' . $osC_Product->getTitle() . ' -- ' . $osC_Product->getPriceFormated() . ']]>',
        'link' => '<![CDATA[' . $link . ']]>',
        'description' => $description,
        'pubDate' => date("D, d M Y H:i:s O"));
    }
    $rss['rss']['channel']['item'] = $items;
    
    return $rss;
  }
  
  function buildProductsRss($group) {
    global $osC_Language, $osC_Image;
    
    $group_title = $group . '_products';
    
    $rss = array();
    $rss['rss'] = array();
    $rss['rss attr'] = array('xmlns:atom'=>'http://www.w3.org/2005/Atom','version'=>'2.0');
    
    //channel
    $rss['rss']['channel'] = array(
      'title' => '<![CDATA[' . $osC_Language->get($group_title) . ']]>', 
      'link' => '<![CDATA[' . osc_href_link(FILENAME_PRODUCTS, $group) .']]>',
      'description' => '<![CDATA[' . $osC_Language->get($group_title) . ']]>',
      'pubDate' => date("D, d M Y H:i:s O"));
    
    //items
    
    if ($group == "new") {
      $Qproducts = osC_Product::getListingNew();
    }else if ($group == 'special') {
      $Qproducts = osC_Specials::getListing();
    }else if ($group == 'feature') {
      $Qproducts = osC_Product::getListingFeature();
    }
    
    $items = array();
    while($Qproducts->next()) {
      $osC_Product = new osC_Product($Qproducts->valueInt('products_id'));
      $link = osc_href_link(FILENAME_PRODUCTS, $Qproducts->valueInt('products_id'), 'NONSSL', false, false, true);
      
      $description = '
        <![CDATA[
        <table>
          <tr>
            <td align="center" valign="top">' . osc_link_object($link, osc_image($osC_Image->getImageUrl($osC_Product->getImage(), 'product_info'), $osC_Product->getTitle())) . '</td>
            <td valign="top">' . $osC_Product->getDescription() . '</td>
          </tr>
        </table>
        ]]>';
      
      $items[] = array(
        'title' => '<![CDATA[' . $osC_Product->getTitle() . ' -- ' . $osC_Product->getPriceFormated() . ']]>',
        'link' => '<![CDATA[' . $link . ']]>',
        'description' => $description,
        'pubDate' => date("D, d M Y H:i:s O"));
    }
    $rss['rss']['channel']['item'] = $items;
    
    return $rss;
  }
}
?>