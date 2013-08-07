<?php
/*
  $Id: quantity_discount_groups_entries_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.quantity_discount_groups.QuantityDiscountEntriesGrid = function(config) {

  config = config || {};
  
  this.quantityDiscountGroupsId = null;
  this.quantityDiscountGroupsName = null;
  
  config.title = '<?php echo $osC_Language->get('heading_title_quantity_discount_entries'); ?>';
  config.region = 'east';
  config.border = false;
  config.split = true;
  config.minWidth = 360;
  config.maxWidth = 420;
  config.width = 360;  
  config.viewConfig = {
    emptyText: TocLanguage.gridNoRecords,
    forceFit: true
  };
      
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'quantity_discount_groups',
      action: 'list_quantity_discount_entries'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'quantity_discount_groups_values_id'
    }, [
      'quantity_discount_groups_values_id',
      'quantity_discount_groups_id',
      'customers_groups_id',
      'quantity',
      'discount',
      'customers_groups_name'
    ]),
    autoLoad: true
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
    {id:'customers_groups_name',header:'<?php echo $osC_Language->get('table_heading_customer_group'); ?>',dataIndex: 'customers_groups_name'},
    {header: '<?php echo $osC_Language->get('table_heading_quantity_discount_group_quantity'); ?>',dataIndex: 'quantity', width: 80,align: 'center'},
    {header: '<?php echo $osC_Language->get('table_heading_quantity_discount_group_discount'); ?>',dataIndex: 'discount',width: 80,align: 'center'},
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
    { 
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];
    
  Toc.quantity_discount_groups.QuantityDiscountEntriesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.quantity_discount_groups.QuantityDiscountEntriesGrid, Ext.grid.GridPanel, {
  
  iniGrid: function(record) {
    this.quantityDiscountGroupsId = record.get('quantity_discount_groups_id');
    this.quantityDiscountGroupsName = record.get('quantity_discount_groups_name');
    var store = this.getStore();
    
    store.baseParams['quantity_discount_groups_id'] = this.quantityDiscountGroupsId;
    store.load();
  },
  
  onAdd: function() {
    if (this.quantityDiscountGroupsId > 0) {
      var dlg = this.owner.createQuantityDiscountEntriesDialog();
     
      dlg.on('saveSuccess', function() {
        this.onRefresh();
      }, this);
      
      dlg.show(this.quantityDiscountGroupsId);
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onEdit: function(record) {
    var quantityDiscountGroupsValuesId = record.get('quantity_discount_groups_values_id');
    var dlg = this.owner.createQuantityDiscountEntriesDialog();
    dlg.setTitle(this.quantityDiscountGroupsName);

    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(this.quantityDiscountGroupsId, quantityDiscountGroupsValuesId);
  },
  
  onDelete: function(record){
    var valuesId = record.get('quantity_discount_groups_values_id');
    
    Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'quantity_discount_groups',
                action: 'delete_quantity_discount_groups_value',
                quantity_discount_groups_values_id: valuesId
              },
              callback: function(options, success, response) {
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
    var keys = this.selModel.selections.keys;
    
    if(keys.length > 0) {   
      var batch = keys.join(',');
      
      Ext.Msg.confirm(
        TocLanguage.msgWarningTitle,
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if(btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: { 
                module: 'quantity_discount_groups',
                action: 'delete_quantity_discount_groups_values',
                batch: batch                                        
              },
              callback: function(options, success, response) {
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
        }, 
        this
      ); 
    } else{
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
    this.setTitle('<?php echo $osC_Language->get('heading_title_quantity_discount_entries'); ?>');
    this.quantityDiscountGroupsId = null;
    this.quantityDiscountGroupsName = null;
    this.getStore().removeAll();
  }
});
