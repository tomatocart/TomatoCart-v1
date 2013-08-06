<?php
/*
  $Id: currencies_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.currencies.CurrenciesGrid = function(config) {
  
  config = config || {};
    
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'currencies',
      action: 'list_currencies'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'currencies_id'
    }, [
      'currencies_id',
      'title',
      'code',
      'value',
      'example'
    ]),
    autoLoad: true
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
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'currencies_title', header: '<?php echo $osC_Language->get('table_heading_currencies'); ?>', dataIndex: 'title'},
    { header: '<?php echo $osC_Language->get('table_heading_code'); ?>', dataIndex: 'code', width: 100,align: 'right'},
    { header: '<?php echo $osC_Language->get('table_heading_value'); ?>', dataIndex: 'value', width: 100,align: 'right'},
    { header: '<?php echo $osC_Language->get('table_heading_example'); ?>', dataIndex: 'example', width: 100,align: 'right'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'currencies_title';
  
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
    },
    '->',
    { 
      text: '<?php echo $osC_Language->get('button_update_currency_exchange_rates'); ?>',
      iconCls: 'icon-update-exchange-rates',
      handler: this.updateCurrencyRates,
      scope: this
    }
  ];
    
  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnAdd,
        iconCls: 'add',
        handler: function() {
          thisObj.onAdd();
        }
      },
      {
        text: TocLanguage.btnDelete,
        iconCls: 'remove',
        handler: function() {
          thisObj.onBatchDelete();
        }
      }
    ],
    beforePageText : TocLanguage.beforePageText,
    firstText: TocLanguage.firstText,
    lastText: TocLanguage.lastText,
    nextText: TocLanguage.nextText,
    prevText: TocLanguage.prevText,
    afterPageText: TocLanguage.afterPageText,
    refreshText: TocLanguage.refreshText,
    displayInfo: true,
    displayMsg: TocLanguage.displayMsg,
    emptyMsg: TocLanguage.emptyMsg,
    prevStepText: TocLanguage.prevStepText,
    nextStepText: TocLanguage.nextStepText
  });
    
  Toc.currencies.CurrenciesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.currencies.CurrenciesGrid, Ext.grid.GridPanel, {
  
  onAdd: function() {
    var dlg = this.owner.createCurrenciesDialog();

    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createCurrenciesDialog();
    dlg.setTitle(record.get("title"));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);    
    
    dlg.show(record.get("currencies_id"));
  },
  
  updateCurrencyRates: function() {
    var waitMsgBox = Ext.MessageBox.wait(TocLanguage.formSubmitWaitMsg);
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'currencies',
        action: 'update_currency_rates'
      },
      callback: function(options, success, response){
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          Ext.destroy(waitMsgBox);
          Ext.MessageBox.alert(TocLanguage.msgSuccessTitle, result.feedback);
          
          this.getStore().reload();
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });   
  },
  
  onDelete: function(record) {
    var currenciesId = record.get('currencies_id');
    var code = record.get('code');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'currencies',
              action: 'delete_currency',
              cID: currenciesId,
              code: code
            },
            callback: function(options, success, response){
              var result = Ext.decode(response.responseText);
              
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
                module: 'currencies',
                action: 'delete_currencies',
                batch: batch
              },
              callback: function(options, success, response){
                var result = Ext.decode(response.responseText);
                
                if(result.success == true){
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.getStore().reload();
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
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction:function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  } 
});