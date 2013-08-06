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
  
  class toC_Gadget_New_Customers extends toC_Gadget{
    var $_title,
        $_code = 'new_customers',
        $_type = 'grid',
        $_icon = 'new_customers.png',
        $_autorun = true,
        $_interval = 180000,
        $_description;     
    
    function toC_Gadget_New_Customers() {
      global $osC_Language;
      
      $this->_title = $osC_Language->get('gadget_new_customers_title');
      $this->_description = $osC_Language->get('gadget_new_customers_description');   
    }
    
    function renderView() {
      global $osC_Language;
      
      $config = array('title' => '" "',
                      'id' => '"sidebar_' . $this->_code . '"',
                      'code' => '"' . $this->_code . '"',
                      'layout' => '"fit"',
                      'items' => $this->_createGrid(),
                      'task' => 'function() {this.items.itemAt(0).getStore().reload();}');
      
      $response = array('success' => true, 'view' => $config);
      
      return $this->encodeArray($response);   
    }
    
    function renderData() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qcustomers = $osC_Database->query('select customers_id, customers_gender, customers_lastname, customers_firstname, customers_status, unix_timestamp(date_account_created) as date_created from :table_customers order by date_created desc limit 3');
      $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomers->execute();
      
      $records = array();
      while ( $Qcustomers->next() ) {
      	$customers_name = $Qcustomers->valueProtected('customers_firstname') . ' ' . $Qcustomers->valueProtected('customers_lastname');
      	
      	if (strlen($customers_name) > 18) {
      		$customers_name = substr($customers_name, 0 , 18) . '..';
      	}
      	
      	$date_created = $Qcustomers->value('date_created');
      	$date_created = $this->getDateOrTime($date_created);
      	$customers_info = '<span class="' . $Qcustomers->value('customers_gender') . '">' . $customers_name . '<br /><span class="date">' . $date_created . '</span></span>';
        
        $records[] = array(
          'id' => $Qcustomers->valueInt('customers_id'),
          'customers_name' => $customers_name,
          'customers_info' => $customers_info        
        );
      }      
      $Qcustomers->freeResult();

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
          baseParams: {module: "desktop_settings", action: "render_data", gadget: "' . $this->_code . '"},
          reader: new Ext.data.JsonReader(
            {
              root: Toc.CONF.JSON_READER_ROOT,
              totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
              id: "id"
            },
            [
              "id",
              "customers_name",
              "customers_info"
            ]
          ),
          autoLoad: false
        }),
        cm: new Ext.grid.ColumnModel([
          {
            id: "customers_name",
            header: "<a href=\'javascript:void(0);\'>' . $osC_Language->get('gadget_new_customers_title') . '</a>",
            align: "left",
            dataIndex: "customers_info"
          }
        ]),
        border: false,
        autoExpandColumn: "customers_name",
        viewConfig: {forceFit: true, headersDisabled: true},
        listeners: {
        	"rowclick": function(grid, rowIndex) {
        		var record = grid.getStore().getAt(rowIndex);
        		
        		grid.findParentByType("gadget").app.callModuleFunc("customers", "createCustomersDialog", function(dlg) {
              dlg.setTitle(record.get("customers_name")); 
              dlg.show(record.get("id"));
              dlg.on("saveSuccess", function() {grid.getStore().reload();});}
            );
    			},
    			"headerclick": function(grid, columnIndex, e) {
	          e.stopEvent();
	          grid.findParentByType("gadget").app.callModuleFunc("customers", "createWindow");
          }
    		}
      })'; 
    }
  }
?>