<?php
/*
  $Id: tax_rates_grid $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.tax_classes.TaxRatesGrid = function(config) {
  
  config = config || {};
  
  this.taxClassId = null;
  this.taxClassTitle = null;
  
  config.title = '<?php echo $osC_Language->get('section_tax_rates'); ?>';
  config.region = 'east';
  config.split = true;
  config.minWidth = 280;
  config.maxWidth = 360;
  config.width = 300;
  config.viewConfig = {
    emptyText: TocLanguage.gridNoRecords,
    forceFit: true
  };
     
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'tax_classes',
      action: 'list_tax_rates'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'tax_rates_id'
    },
    [
      'tax_rates_id',
      'tax_priority',
      'tax_rate',
      'geo_zone_name'
    ])
  });  
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions: [
     {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
     {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
     
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {header: '<?php echo $osC_Language->get('table_heading_tax_rate_zone'); ?>', dataIndex: 'geo_zone_name'},
    {header: '<?php echo $osC_Language->get('table_heading_tax_rate_priority'); ?>', dataIndex: 'tax_priority', width:70},
    {header: '<?php echo $osC_Language->get('table_heading_tax_rate'); ?>', dataIndex: 'tax_rate', width: 80, align: 'right'},
    config.rowActions     
  ]);
  
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
    { text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];    

  Toc.tax_classes.TaxRatesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tax_classes.TaxRatesGrid, Ext.grid.GridPanel, {

  iniGrid: function(record) {
    this.taxClassId = record.get('tax_class_id');
    this.taxClassTitle = record.get('tax_class_title');
    var store = this.getStore();
    
    store.baseParams['tax_class_id'] = this.taxClassId;  
    store.load();  
  },
  
  onAdd: function() {
    if (this.taxClassId) {
      var dlg = this.owner.createTaxRatesDialog();
       
      dlg.on('saveSuccess', function() {
        this.onRefresh();
      }, this)
       
      dlg.show(this.taxClassId);
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }   
  },
  
  onEdit: function(record) {
    var taxRatesId = record.get('tax_rates_id');
    var dlg = this.owner.createTaxRatesDialog();
    dlg.setTitle(this.taxClassTitle);
    
    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show(this.taxClassId, taxRatesId);
  },
  
  onDelete: function(record) {
    var taxRatesId = record.get('tax_rates_id');
                  
    Ext.Msg.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function(btn) {
        if(btn == 'yes') {                                                                                                                                                                 
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: { 
              module: 'tax_classes',
              action: 'delete_tax_rate',
              rateId: taxRatesId
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                this.onRefresh();
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
    
      Ext.Msg.confirm(
        TocLanguage.msgWarningTitle,
        TocLanguage.msgDeleteConfirm,
        function(btn, text, s) {
          if(btn == 'yes') {                                                                                                                                                                 
            Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: { 
              module: 'tax_classes',
              action: 'delete_tax_rates',
              batch: batch                                        
            },
            callback: function(options, success, response){
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                this.onRefresh();
              }
              else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this                     
            });                
          }                                              
        }, this); 
                  
    }
    else{
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
  },
  
  reset: function() {
    this.setTitle('<?php echo $osC_Language->get('section_tax_rates'); ?>');
    this.taxClassId = null;
    this.taxClassTitle = null;
    this.getStore().removeAll();
  } 
});
