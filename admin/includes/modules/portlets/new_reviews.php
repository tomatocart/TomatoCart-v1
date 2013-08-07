<?php
/*
  $Id: new_reviews.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Portlet_New_Reviews extends toC_Portlet {

  var $_title,
      $_code = 'new_reviews';
  
  function toC_Portlet_New_Reviews() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('portlet_new_reviews_title');
  }
  
  function renderView() {
    global $osC_Language;
    
    $config = array('title' => '"' . $osC_Language->get('portlet_new_reviews_title') . '"',
                    'code' => '"' . $this->_code . '"', 
                    'layout' => '"fit"',
                    'height' => 200,
                    'items' => $this->_createGrid());  
    
    $response = array('success' => true, 'view' => $config);
    return $this->encodeArray($response);
  }
  
  function renderData() {
    global $toC_Json, $osC_Database, $osC_Language;

    $Qreviews = $osC_Database->query('select r.reviews_id, r.products_id, greatest(r.date_added, ifnull(r.last_modified, 0)) as date_last_modified, r.reviews_rating, pd.products_name, l.name as languages_name, l.code as languages_code from :table_reviews r left join :table_products_description pd on (r.products_id = pd.products_id and r.languages_id = pd.language_id), :table_languages l where r.languages_id = l.languages_id order by date_last_modified desc limit 6');
    $Qreviews->bindTable(':table_reviews', TABLE_REVIEWS);
    $Qreviews->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
    $Qreviews->bindTable(':table_languages', TABLE_LANGUAGES);
    $Qreviews->execute();

    $records = array();
    while ( $Qreviews->next() ) {
      $records[] = array(
        'reviews_id' => $Qreviews->valueInt('reviews_id'),
        'products_name' => $Qreviews->value('products_name'),
        'languages_code' => $osC_Language->showImage($Qreviews->value('languages_code')),
        'reviews_rating' => osc_image('../images/stars_' . $Qreviews->valueInt('reviews_rating') . '.png', $Qreviews->valueInt('reviews_rating') . '/5'),
        'date_last_modified' => osC_DateTime::getShort($Qreviews->value('date_last_modified'))
      );
    }
    
    $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                      EXT_JSON_READER_ROOT => $records);
                    
    echo $toC_Json->encode($response);  
  }

  function _createGrid() {
    global $osC_Language;
    
    return '
      new Ext.grid.GridPanel({
       region: "center",
       ds: new Ext.data.Store({
         url: Toc.CONF.CONN_URL,
         baseParams: {module: "dashboard", action: "render_data", portlet: "' . $this->_code . '"},
         reader: new Ext.data.JsonReader(
           {
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: "reviews_id"
           },
           [
            "reviews_id",
            "products_name",
            "languages_code",
            "reviews_rating",
            "date_last_modified",
           ]
         ),
         autoLoad: true
       }),
           
       cm: new Ext.grid.ColumnModel([
         {
           id: "products_name",
           header: "' . $osC_Language->get('portlet_new_reviews_table_heading_products') .'",
           dataIndex: "products_name"
         },
         {
           header: "'. $osC_Language->get('portlet_new_reviews_table_heading_language') .'",
           dataIndex: "languages_code",
           align: "center"
         },
         {
           header: "'. $osC_Language->get('portlet_new_reviews_table_heading_rate') .'",
           dataIndex: "reviews_rating",
           align: "center"
         },
         {
           header: "' . $osC_Language->get('portlet_new_reviews_table_heading_date') .'",
           dataIndex: "date_last_modified",
           align: "center"
         }
       ]),
       border: false,
       viewConfig: {forceFit: true}
    })'; 
  }
}  
?>