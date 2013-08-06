<?php
/*
  $Id: emails.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Gadget_Emails extends toC_Gadget {
  
    var $_title,
        $_code = 'emails',
        $_type = 'grid',
        $_icon = 'emails.png',
        $_autorun = true,
        $_interval = 180000,
        $_description;
    
    function toC_Gadget_Emails() {
      global $osC_Language;
      
      $this->_title = $osC_Language->get('gadget_emails_title');
      $this->_description = $osC_Language->get('gadget_emails_description');
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
  
      $Qemails = $osC_Database->query('select id, accounts_id, folders_id, messages_id, subject, fetch_timestamp, from_address from :table_email_messages order by fetch_timestamp desc limit 3');
      $Qemails->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qemails->execute();
      
      $records = array();
      while ( $Qemails->next() ) {
        $subject = $Qemails->value('subject');
        $address = $Qemails->value('from_address');
        
        if (strlen($subject) > 20) {
          $subject = substr($subject, 0 , 20) . '..';
        }
        
        if (strlen($address) > 20) {
          $address = substr($address, 0 , 20) . '..';
        }
        
        $records[] = array(
          'id' => $Qemails->valueInt('id'),
          'folders_id' => $Qemails->valueInt('folders_id'),
          'accounts_id' => $Qemails->valueInt('accounts_id'),
          'fetch_time' => $Qemails->value('fetch_timestamp'),
          'messages' => $subject  . '<br /><span class="email-address">' . $address . '</span>'
        );
      }
      $Qemails->freeResult();
      
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
	              id: "id"
	            },
	            [
	              "id",
	              "folders_id",
	              "accounts_id",
	              "fetch_time",            
	              "messages"
	            ]
	          ),
	          autoLoad: false
	        }),
	           
	        cm: new Ext.grid.ColumnModel([
	          {
	            id: "messages",
	            header:"<a href=\'javascript:void(0); \'>' . $osC_Language->get('gadget_emails_title') . '</a>",
	            width: 135,
	            dataIndex: "messages",
	            align: "left"
	          }
	        ]),
	        border: false,
	        autoExpandColumn: "messages",
	        viewConfig: {forceFit: true, headersDisabled: true},
	        listeners: {
	          "rowclick": function(grid, rowIndex) {
	            var record = grid.getStore().getAt(rowIndex);
	            
	            grid.findParentByType("gadget").app.callModuleFunc("email", "createMessageDetailDialog", ' . $this->_getCallback() . ');
	          },
	          "headerclick": function(grid, columnIndex, e) {
	            e.stopEvent();
	            grid.findParentByType("gadget").app.callModuleFunc("email", "createWindow");
	          }
	        }
	      })'; 
    }
    
    function _getCallback() {
    	$callbackFn = 'function(dlg) {
      	dlg.show();
      	dlg.pnlMessage.loadMessage(record.get("id"), record.get("accounts_id"), record.get("folders_id"), record.get("fetch_time"));
      	dlg.on("saveSuccess", function() {grid.getStore().reload();});}';
      	
    	return $callbackFn;
    }
  }
?>