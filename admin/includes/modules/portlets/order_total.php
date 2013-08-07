<?php
/*
  $Id: order_total.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Portlet_Order_Total extends toC_Portlet {

  var $_title,
      $_code = 'order_total';

  function toC_Portlet_Order_Total() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('portlet_order_total_title');
  }
  
  function renderView() {
    $config = array(
      'title' => '"' . $this->_title . '"',
      'code' => '"' . $this->_code . '"', 
      'height' => 200,
      'layout' => '"fit"',
      'swf' => '"' . osc_href_link_admin('external/open-flash-chart/open-flash-chart.swf') . '"', 
      'flashvars' => array('data' => '"' . osc_href_link_admin(FILENAME_JSON, 'module=dashboard&action=render_data&portlet=' . $this->_code) . '"'),
      'plugins' => 'new Ext.ux.PortletFlashPlugin()');
    
    $response = array('success' => true, 'view' => $config);
    return $this->encodeArray($response);
  }
  
  function renderData() {
    global $osC_Database;
    
    require_once('includes/classes/flash_bar.php');
      
    $end_date = date("Y-m-d");
    $start_date = date("Y-m-d", strtotime('-2 weeks'));    

    $Qorders = $osC_Database->query('SELECT date( o.date_purchased ) as purchased_date, ot.value FROM :table_orders o, :table_orders_total ot WHERE o.orders_id = ot.orders_id AND ot.class = \'total\' and o.date_purchased >= :start_date ORDER BY o.date_purchased');
    $Qorders->bindTable(':table_orders', TABLE_ORDERS);
    $Qorders->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
    $Qorders->bindValue(':start_date', $start_date);
    $Qorders->execute();

    $data = array();
    for($i = 14; $i >= 2; $i--) {
      $data[date("Y-m-d", strtotime('-' . $i . ' days'))] = 0;
    }
    $data[date("Y-m-d", strtotime('-1 days'))] = 0;
    $data[$end_date] = 0;

    while ($Qorders->next()) {
      $data[$Qorders->value('purchased_date')] += $Qorders->value('value');
    }

    $chart = new toC_Flash_Bar_Order_Total(" ");
    $chart->setData($data);
    $chart->render();
  }
}
?>