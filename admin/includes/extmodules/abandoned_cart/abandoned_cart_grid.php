<?php
/*
  $Id: abandoned_cart_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.abandoned_cart.AbandonedCartGrid = function(config) {
  
  config = config || {};
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.GroupingStore({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'abandoned_cart',
      action: 'list_abandoned_cart'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'customers_id'
    }, [
      'customers_id',
      'products',
      'date_contacted',
      'date_added',
      'customers_name',
      'email',
      'total',
      'action'
    ]),
    autoLoad: true
  });
  
  config.expander =  new Ext.grid.RowExpander({
    tpl : new Ext.Template('<p style="margin-left:34px">{products}</p>')
  });
    
  config.rowActions = new Ext.ux.grid.RowActions({
    tpl: new Ext.XTemplate(
      '<div class="ux-row-action">'
      +'<tpl for="action">'
      +'<div class="ux-row-action-item {class}" qtip="{qtip}"></div>'
      +'</tpl>'
      +'</div>'
    ),
    actions:['',''],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this); 
     
  config.plugins = [config.expander, config.rowActions];
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    config.expander,
    { id: 'abandoned_cart_customer_name', header: '<?php echo $osC_Language->get('table_heading_customer_name'); ?>', dataIndex: 'customers_name'},
    { header: '<?php echo $osC_Language->get('table_heading_cart_total'); ?>', dataIndex: 'total', width: 110 },
    { header: '<?php echo $osC_Language->get('table_heading_date'); ?>', dataIndex: 'date_added', width: 110},
    { header: '<?php echo $osC_Language->get('table_heading_customer_email'); ?>', dataIndex: 'email', width: 160},
    { header: '<?php echo $osC_Language->get('table_heading_contacted_date'); ?>', dataIndex: 'date_contacted', width: 110},
    config.rowActions
  ]); 
  config.autoExpandColumn = 'abandoned_cart_customer_name';
  
  config.tbar = [
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
  
  Toc.abandoned_cart.AbandonedCartGrid.superclass.constructor.call(this, config); 
  
};

Ext.extend(Toc.abandoned_cart.AbandonedCartGrid, Ext.grid.GridPanel, {
  onDelete: function(record) {
    var customersId = record.get('customers_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'abandoned_cart',
              action: 'delete_abandoned_cart',
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
      }, 
      this
    );
  },
  
  onBatchDelete: function() {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {    
      var batch = keys.join(',');

      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'abandoned_cart',
                action: 'delete_abandoned_carts',
                batch: batch
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.onRefresh();
                }else{
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }
        }, this);

    }else{
       Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
   
  onSendEmails: function(record) {
    var customersId = record.get('customers_id');
    var customersName = record.get('customers_name');
    var cartCotents = record.get('products');
    var cartTotal = record.get('total');

    var dlg = this.owner.createSendEmailsDialog();
     
    dlg.on('sendSuccess', function() {
      this.onRefresh();
    }, this);
     
    dlg.show(customersId, customersName, cartCotents, cartTotal);      
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {       
      case 'icon-send-email-record':
        this.onSendEmails(record);
        break;
             
      case 'icon-delete-record':
        this.onDelete(record);
        break;       
    }
  },
  
  onRefresh: function() {
    this.getStore().reload();
  }
}); 