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

  class toC_Gadget_New_Orders extends toC_Gadget {
  
    var $_title,
        $_code = 'new_orders',
        $_type = 'grid',
        $_icon = 'new_orders.png',
        $_autorun = true,
        $_interval = 180000,
        $_description;
    
    function toC_Gadget_New_Orders() {
      global $osC_Language;
      
      $this->_title = $osC_Language->get('gadget_new_orders_title');
      $this->_description = $osC_Language->get('gadget_new_orders_description');
    }
    
    function renderView() {
      global $osC_Language;
      
      $config = array('title' => '" "',
                      'id' => '"sidebar_' . $this->_code . '"',
                      'code' => '"' . $this->_code . '"',
                      'layout' => '"fit"',
                      'items' => $this->_createGrid(),
                      'task' => 'function() {this.items.itemAt(0).getStore().reload()}');  
      
      $response = array('success' => true, 'view' => $config);
      return $this->encodeArray($response);
    }
    
	  function renderData() {
	    global $toC_Json, $osC_Database, $osC_Language;
	
	    $Qorders = $osC_Database->query('select o.orders_id, o.customers_name, c.customers_gender, unix_timestamp(o.date_purchased) as date_last_modified, s.orders_status_name, ot.text as order_total from :table_orders o, :table_orders_total ot, :table_orders_status s , :table_customers c where o.customers_id = c.customers_id and o.orders_id = ot.orders_id and ot.class = "total" and o.orders_status = s.orders_status_id and s.language_id = :language_id order by date_last_modified desc limit 3');
	    $Qorders->bindTable(':table_orders', TABLE_ORDERS);
	    $Qorders->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
	    $Qorders->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
	    $Qorders->bindTable(':table_customers', TABLE_CUSTOMERS);
	    $Qorders->bindInt(':language_id', $osC_Language->getID());
	    $Qorders->execute();
	    
	    $records = array();
	    while ( $Qorders->next() ) {
	      $customers_name = $Qorders->valueProtected('customers_name');
	      
	      if (strlen($customers_name) > 10) {
	        $customers_name = substr($customers_name, 0 , 10) . '..';
	      }
	      
	      $date_order = $Qorders->value('date_last_modified');
	      
	      $date_order = $this->getDateOrTime($date_order);
	      $records[] = array(
	        'orders_id' => $Qorders->valueInt('orders_id'),
	        'orders_name' => $Qorders->valueProtected('customers_name') . '<br /><span class="order-date">' . $date_order . '</span>',
	        'customers_name' => $customers_name,
	        'order_total_status' => strip_tags($Qorders->value('order_total')) . '<br /><span class="order-status">' . $Qorders->value('orders_status_name') . '</span>'
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
	        baseParams: {module: "desktop_settings", action: "render_data", gadget: "'.$this->_code.'"},
	        reader: new Ext.data.JsonReader(
	          {
	            root: Toc.CONF.JSON_READER_ROOT,
	            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
	            id: "orders_id"
	          },
	          [
	            "orders_id",
	            "orders_name",
	            "customers_name",
	            "order_total_status"
	          ]
	        ),
	        autoLoad: false
	      }),
	           
	      cm: new Ext.grid.ColumnModel([
	        {
	          header:"<a href=\'javascript:void(0);\'>' . $osC_Language->get('gadget_new_orders_title') . '</a>",
	          dataIndex: "orders_name",
	          align: "left"
	        },
	        {
	          dataIndex: "order_total_status",
	          align: "left",
	          hidden: true
	        }
	      ]),
	       border: false,
	       viewConfig: {forceFit: true, headersDisabled: true},
	       listeners: {
	        "rowclick": function(grid, rowIndex) {
	          var record = grid.getStore().getAt(rowIndex);
	          
	          grid.findParentByType("gadget").app.callModuleFunc("orders", "createOrdersDialog", function(dlg) {
              dlg.setTitle(record.get("orders_id") + ": " + record.get("customers_name")); 
              dlg.show();
              dlg.on("saveSuccess", function() {
                grid.getStore().reload();
	            })
	          }, [{ordersId: record.get("orders_id")}]);
	        },
	        "headerclick": function(grid, columnIndex, e) {
	          e.stopEvent();
	          grid.findParentByType("gadget").app.callModuleFunc("orders", "createWindow");
	        }
	      }
	    })'; 
	  }  
  }
?>