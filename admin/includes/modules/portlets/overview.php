<?php
/*
  $Id: overview.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Portlet_Overview extends toC_Portlet {

  var $_title,
      $_code = 'overview';

  function toC_Portlet_Overview() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('portlet_overview_title');
  }
  
  function renderView() {
    global $osC_Language;
    
    $config = array('title' => '"' . $osC_Language->get('portlet_overview_title') . '"', 
                    'code' => '"' . $this->_code . '"',
                    'height' => 200,
                    'layout' => '"fit"', 
                    'items' => $this->_createGrid());  
                   
    $response = array('success' => true, 'view' => $config);
    return $this->encodeArray($response);
  }

  function renderData() {
    global $toC_Json, $osC_Database, $osC_Language;
      
    $records = array();
    
    $Qcustomers = $osC_Database->query('select count(*) as total from :table_customers');
    $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
    $Qcustomers->execute();
    $records[] = array(
      'class' => osc_icon('people.png') . '&nbsp;' . $osC_Language->get('summary_statistics_text_customers'),
      'number' => $Qcustomers->valueInt('total')
    );
    $Qcustomers->freeResult();
    
    $Qorders = $osC_Database->query('select count(*) as total from :table_orders');
    $Qorders->bindTable(':table_orders', TABLE_ORDERS);
    $Qorders->execute();
    $records[] = array(
      'class' => osc_icon('orders.png') . '&nbsp;' . $osC_Language->get('summary_statistics_text_orders'),
      'number' => $Qorders->valueInt('total')
    );
    $Qorders->freeResult();
    
    $Qproducts = $osC_Database->query('select count(*) as total from :table_products');
    $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
    $Qproducts->execute();
    $records[] = array(
      'class' => osc_icon('products.png') . '&nbsp;' . $osC_Language->get('summary_statistics_text_products'),
      'number' => $Qproducts->valueInt('total')
    );
    $Qproducts->freeResult();
    
    $Qreviews = $osC_Database->query('select count(*) as total from :table_reviews');
    $Qreviews->bindTable(':table_reviews', TABLE_REVIEWS);
    $Qreviews->execute();
    $records[] = array(
      'class' => osc_icon('reviews.png') . '&nbsp;' . $osC_Language->get('summary_statistics_text_reviews'),
      'number' => $Qreviews->valueInt('total')
    );
    $Qreviews->freeResult();
    
    $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                      EXT_JSON_READER_ROOT => $records);
                    
    echo $toC_Json->encode($response);  
  }
  
  function _createGrid() {
    return '
      new Ext.grid.GridPanel({
        ds: new Ext.data.Store({
          url: Toc.CONF.CONN_URL,
          baseParams: {module: "dashboard", action: "render_data", portlet: "' . $this->_code . '"},
          reader: new Ext.data.JsonReader(
            {
              root: Toc.CONF.JSON_READER_ROOT,
              totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
              id: "class"
            },
            ["class", "number"]
          ),
           autoLoad: true
        }),
           
        cm: new Ext.grid.ColumnModel([
          {
            id: "class",
            header: "&nbsp;",
            dataIndex: "class"
          },
          {
            header: "&nbsp;",
            dataIndex: "number",
            width: 40,
            align: "center"
          }
        ]),
        border: false,
        autoExpandColumn: "class"
    })'; 
  }  
}
?>