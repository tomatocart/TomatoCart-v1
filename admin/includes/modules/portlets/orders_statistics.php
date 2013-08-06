<?php
/*
  $Id: orders_statistics.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Portlet_Orders_Statistics extends toC_Portlet {

  var $_title,
      $_code = 'orders_statistics';

  function toC_Portlet_Orders_Statistics() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('portlet_orders_statistics_title');
  }
  
  function renderView() {
    global $osC_Language;
    
    $config = array('title' => '"' . $osC_Language->get('portlet_orders_statistics_title') . '"',
                    'code' => '"' . $this->_code . '"', 
                    'height' => 200,
                    'layout' => '"fit"', 
                    'items' => $this->_createGrid());  
                   
    $response = array('success' => true, 'view' => $config);
    return $this->encodeArray($response);
  }

  function renderData() {
    global $toC_Json, $osC_Database, $osC_Language;
      
    $QorderStatus = $osC_Database->query('select orders_status_id, orders_status_name from :table_orders_status where language_id = :language_id');
    $QorderStatus->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
    $QorderStatus->bindInt(':language_id', $osC_Language->getID());
    $QorderStatus->execute();
    
    $records = array();
    while ($QorderStatus->next()) {
    
      $QorderTotal = $osC_Database->query('select count(*) as total from :table_orders where orders_status = :orders_status');
      $QorderTotal->bindTable(':table_orders', TABLE_ORDERS);
      $QorderTotal->bindInt(':orders_status', $QorderStatus->valueInt('orders_status_id'));
      $QorderTotal->execute();
    
      $records[] = array(
        'orders_status' => osc_icon('orders.png') . '&nbsp;' . $QorderStatus->value('orders_status_name'),
        'number' => $QorderTotal->valueInt('total')
      );
      $QorderTotal->freeResult();
    }
    $QorderStatus->freeResult();
    
    $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                      EXT_JSON_READER_ROOT => $records);
                    
    echo $toC_Json->encode($response);  
  }
  
  function _createGrid() {
    global $osC_Language;
    
    return '
      new Ext.grid.GridPanel({
       ds: new Ext.data.Store({
         url: Toc.CONF.CONN_URL,
         baseParams: {module: "dashboard", action: "render_data", portlet: "' . $this->_code . '"},
         reader: new Ext.data.JsonReader(
           {
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: "orders_status"
           },
           [
            "orders_status",
            "number",
           ]
         ),
         autoLoad: true
       }),
           
       cm: new Ext.grid.ColumnModel([
         {
           id: "orders_status",
           header: "'. $osC_Language->get('portlet_orders_statistics_table_heading_orders_status') .'",
           dataIndex: "orders_status"
         },
         {
           header: "&nbsp;",
           dataIndex: "number",
           align: "center",
           width: 60
         }
       ]),
               
       border: false,
       autoExpandColumn: "orders_status"
    })'; 
  }  
}
?>