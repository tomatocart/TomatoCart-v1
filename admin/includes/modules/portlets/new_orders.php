<?php
/*
  $Id: new_orders.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Portlet_New_Orders extends toC_Portlet {

  var $_title,
      $_code = 'new_orders';
  
  function toC_Portlet_New_Orders() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('portlet_new_orders_title');
  }
  
  function renderView() {
    global $osC_Language;
    
    $config = array('title' => '"' . $osC_Language->get('portlet_new_orders_title') . '"', 
                    'code' => '"' . $this->_code . '"',
                    'layout' => '"fit"',
                    'height' => 200,
                    'items' => $this->_createGrid());  
    
    $response = array('success' => true, 'view' => $config);
    return $this->encodeArray($response);
  }
  
  function renderData() {
    global $toC_Json, $osC_Database, $osC_Language;

    $Qorders = $osC_Database->query('select o.orders_id, o.customers_name, greatest(o.date_purchased, ifnull(o.last_modified, 0)) as date_last_modified, s.orders_status_name, ot.text as order_total from :table_orders o, :table_orders_total ot, :table_orders_status s where o.orders_id = ot.orders_id and ot.class = "total" and o.orders_status = s.orders_status_id and s.language_id = :language_id order by date_last_modified desc limit 4');
    $Qorders->bindTable(':table_orders', TABLE_ORDERS);
    $Qorders->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
    $Qorders->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
    $Qorders->bindInt(':language_id', $osC_Language->getID());
    $Qorders->execute();
    
    $records = array();
    while ( $Qorders->next() ) {
      $records[] = array(
        'orders_id' => $Qorders->valueInt('orders_id'),
        'customers_name' => $Qorders->valueProtected('customers_name'),
        'order_total' => strip_tags($Qorders->value('order_total')), 
        'date_purchased' => osC_DateTime::getShort($Qorders->value('date_last_modified')), 
        'orders_status_name' => $Qorders->value('orders_status_name') 
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
        ds: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {module: "dashboard", action: "render_data", portlet: "'.$this->_code.'"},
        reader: new Ext.data.JsonReader(
          {
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: "orders_id"
          },
          [
            "orders_id",
            "customers_name",
            "order_total",
            "date_purchased",
            "orders_status_name"
          ]
        ),
        autoLoad: true
      }),
           
      cm: new Ext.grid.ColumnModel([
        {
          id: "customers_name",
          header:"'. $osC_Language->get('portlet_new_orders_table_heading_customers'). '",
          dataIndex: "customers_name"
        },
        {
          header: "'. $osC_Language->get('portlet_new_orders_table_heading_total'). '",
          dataIndex: "order_total"
        },
        {
          header: "'. $osC_Language->get('portlet_new_orders_table_heading_date'). '",
          dataIndex: "date_purchased",
          align: "center"
        },
        {
          header: "' . $osC_Language->get('portlet_new_orders_table_heading_status') . '",
          dataIndex: "orders_status_name",
          align: "center"
        }
      ]),
       border: false,
       viewConfig: {forceFit: true}
    })'; 
  }
}  
?>