<?php
/*
  $Id: customers_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.customers.CustomersGrid = function(config) {
  config = config || {};
  
  config.region = 'center';
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords}; 

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'customers',
      action: 'list_customers'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'customers_id'
    },[
      'customers_id',
      'customers_lastname',
      'customers_firstname',
      'customers_credits',
      'date_account_created',
      'customers_status',
      'customers_info'
    ]),
    autoLoad: true
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
    
  var expander = new Ext.grid.RowExpander({
    tpl : new Ext.Template('{customers_info}')
  });
  config.plugins = [config.rowActions, expander];  
  
  renderStatus = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };
     
  config.cm = new Ext.grid.ColumnModel([
    expander,
    {
      id: 'customers_lastname',
      header: '<?php echo $osC_Language->get('table_heading_last_name'); ?>',
      dataIndex: 'customers_lastname'
    },
    {
      header: '<?php echo $osC_Language->get('table_heading_first_name'); ?>',
      dataIndex: 'customers_firstname',
      width: 85
    },
    {
      header: '<?php echo $osC_Language->get('table_heading_date_created'); ?>',
      dataIndex: 'date_account_created',
      width: 85,
      align: 'center'
    },
    {
      header: '<?php echo $osC_Language->get('table_heading_customers_credits'); ?>',
      dataIndex: 'customers_credits',
      width: 100,
      align: 'center'
    },
    {
      header: '<?php echo $osC_Language->get('table_heading_customers_status'); ?>',
      dataIndex: 'customers_status',
      width: 80,
      align: 'center',
      renderer: renderStatus
    },
    config.rowActions
  ]);
  config.selModel = new Ext.grid.RowSelectionModel({singleSelect: true});
  config.autoExpandColumn = 'customers_lastname';
  config.search = new Ext.form.TextField({name: 'search', width: 130});
  
  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls: 'add',
      handler: this.onAdd,
      scope: this
    },
    '-',
    { 
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    },
    '->',
    config.search,
    '',
    {
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
   }];  

  config.bbar = new Ext.PagingToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    iconCls: 'icon-grid',
    displayInfo: true,
    displayMsg: TocLanguage.displayMsg,
    emptyMsg: TocLanguage.emptyMsg
  });
  
  this.addEvents({'selectchange' : true});  
  
  Toc.customers.CustomersGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.customers.CustomersGrid, Ext.grid.GridPanel, {
  
  onAdd: function() {
    var dlg = this.owner.createCustomersDialog();
    dlg.setTitle('<?php echo $osC_Language->get('action_heading_new_customer'); ?>');
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);

    dlg.show();
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createCustomersDialog();
    dlg.setTitle(record.get('customers_lastname'));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get('customers_id'));
  },  
  
  onDelete: function(record) {
    var customersId = record.get('customers_id');
                  
    Ext.Msg.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function(btn) {
        if (btn == 'yes') {                                                                                                                                                                 
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: { 
              module: 'customers',
              action: 'delete_customer',
              customers_id: customersId                                        
            },
            callback: function(options, success, response) {
              result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                this.getStore().reload();
            } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this 
          });
        }
      }, this);
  },

  onRefresh: function() {
    this.getStore().load();
  },
  
  onSearch: function () {
    var store = this.getStore();

    store.baseParams['search'] = this.search.getValue() || null;
    store.reload();
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  },
  
  onClick: function(e, target) {
    var t = e.getTarget();
    var v = this.view;
    var row = v.findRowIndex(t);
    var col = v.findCellIndex(t);
    var action = false;
    
    if (row !== false) {
      var expander = e.getTarget(".x-grid3-row-body");

      if (col > 0 || (col == false && expander != null)) {
        var record = this.getStore().getAt(row);
        this.fireEvent('selectchange', record);
      }
    
      var btn = e.getTarget(".img-button");
      
      if (btn) {
        action = btn.className.replace(/img-button btn-/, '').trim();
        var customersId = this.getStore().getAt(row).get('customers_id');
        
        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.setStatus(customersId, flag);
            break;
        }
      } 
    }
  },
  
  setStatus: function(customersId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'customers',
        action: 'set_status',
        customers_id: customersId,
        flag: flag
      },
      callback: function(options, success, response) {
        result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(customersId).set('customers_status', flag);
          store.commitChanges();
        }

        this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
      },
      scope: this
    });
  }  
  
});

