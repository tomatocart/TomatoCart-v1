<?php
/*
  $Id: new_customers.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Portlet_New_Customers extends toC_Portlet {

  var $_title,
      $_code = 'new_customers';
  
  function toC_Portlet_New_Customers() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('portlet_new_customers_title');
  }
  
  function renderView() {
    global $osC_Language;
    
    $config = array('title' => '"' . $osC_Language->get('portlet_new_customers_title') . '"', 
                    'code' => '"' . $this->_code . '"',
                    'layout' => '"fit"',
                    'height' => 200,
                    'items' => $this->_createGrid());  
    
    $response = array('success' => true, 'view' => $config);
    return $this->encodeArray($response);
  }
  
  function renderData() {
    global $toC_Json, $osC_Database, $osC_Language;

    $Qcustomers = $osC_Database->query('select customers_id, customers_gender, customers_lastname, customers_firstname, customers_status, date_account_created from :table_customers order by date_account_created desc limit 6');
    $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
    $Qcustomers->execute();

    $records = array();
    while ( $Qcustomers->next() ) {
      $customer_icon = osc_icon('people.png');

      if ( ACCOUNT_GENDER > -1 ) {
        switch ( $Qcustomers->value('customers_gender') ) {
          case 'm':
            $customer_icon = osc_icon('user_male.png');

            break;

          case 'f':
            $customer_icon = osc_icon('user_female.png');

            break;
        }
      }
        
      $records[] = array(
        'customers_name' => $customer_icon . '&nbsp;' . $Qcustomers->valueProtected('customers_firstname') . ' ' . $Qcustomers->valueProtected('customers_lastname'),
        'date_account_created' => osC_DateTime::getShort($Qcustomers->value('date_account_created')),
        'customers_status' => osc_icon(($Qcustomers->valueInt('customers_status') === 1) ? 'checkbox_ticked.gif' : 'checkbox_crossed.gif')
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
        baseParams: {module: "dashboard", action: "render_data", portlet: "' . $this->_code . '"},
        reader: new Ext.data.JsonReader(
          {
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: "customers_id"
          },
          [
            "customers_name",
            "date_account_created",
            "customers_status"
          ]
        ),
        autoLoad: true
      }),
      cm: new Ext.grid.ColumnModel([
        {
          id: "customers_name",
          header: "'. $osC_Language->get('portlet_new_customers_table_heading_customers') .'",
          dataIndex: "customers_name"
        },
        {
          header: "'. $osC_Language->get('portlet_new_customers_table_heading_date') .'",
          dataIndex: "date_account_created",
          align: "center"
        },
        {
          header: "'. $osC_Language->get('portlet_new_customers_table_heading_status').'",
          dataIndex: "customers_status",
          align: "center",
          width: 50
        }
      ]),
      border: false,
      autoExpandColumn: "customers_name"
    })'; 
  }
}  
?>