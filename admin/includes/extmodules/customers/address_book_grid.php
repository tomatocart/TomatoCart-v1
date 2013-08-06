<?php
/*
  $Id: address_book_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.AddressBookGrid = function(config) {
  config = config || {};
  
  this.customersId = null;
  
  config.title = '<?php echo $osC_Language->get('section_address_book'); ?>';
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords}; 
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'customers',
      action: 'list_address_books'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      id: 'address_book_id'
    },
    [
      'address_book_id',
      'address_html'
    ])
  });  
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions: [
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
     
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel(
    [
      config.sm,
      {id:'address_book', header:'<?php echo $osC_Language->get('section_address_book'); ?>', css: 'white-space:nowrap;', dataIndex: 'address_html'},
      config.rowActions     
    ]
  );
  config.autoExpandColumn = 'address_book';
      
  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls: 'add',
      handler: this.onAdd,
      scope: this
    },
    '-',
    {
      text: TocLanguage.btnDelete,
      iconCls: 'remove',
      handler: this.onBatchDelete,
      scope: this
    },
    '-',
    { 
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];    

  Toc.customers.AddressBookGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.customers.AddressBookGrid, Ext.grid.GridPanel, {
  iniGrid: function(record) {
    this.customersId = record.get('customers_id');
    this.customer = record.get('customers_lastname') + ' ' + record.get('customers_firstname');
    var store = this.getStore();
    
    store.baseParams['customers_id'] = this.customersId;
    store.load();  
  },
  
  onAdd: function() {
    if (this.customersId) {
      dlg = this.owner.createAddressBookDialog();
     
      dlg.on('saveSuccess', function() {
        this.onRefresh();
      }, this);
     
      dlg.show(this.customersId);
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onEdit: function(record) {
    var addressBookId = record.get('address_book_id');
    var dlg = this.owner.createAddressBookDialog();
    dlg.setTitle(this.customer);
   
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
   
    dlg.show(this.customersId, addressBookId);  
  },
  
  onDelete: function(record) {
    var addressBookId = record.get('address_book_id');
    var customersId = this.customersId;
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'customers',
              action: 'delete_address_book',
              address_book_id: addressBookId,
              customers_id: customersId
            },
            callback: function(options, success, response) {
              result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.getStore().reload();
                this.owner.app.showNotification( {title: TocLanguage.msgSuccessTitle, html: result.feedback} );
              }else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });
        }
      },
      this);       
  },
      
  onBatchDelete: function() {
    var customersId = this.customersId;
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {    
      var batch = keys.join(',');
    
      Ext.Msg.confirm(
        TocLanguage.msgWarningTitle,
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {                                                                                                                                                                 
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: { 
                module: 'customers',
                action: 'delete_address_books',
                batch: batch,
                customers_id: customersId                                        
              },
              callback: function(options, success, response) {
                result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.getStore().reload();
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                }
                else {
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this                     
            });                
          }                                              
        }, this); 
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRefresh: function() {
    this.getStore().reload();
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
  
  reset: function() {
    this.setTitle('<?php echo $osC_Language->get('section_address_book'); ?>');
    this.customersId = null;
    this.getStore().removeAll();
  }
});
