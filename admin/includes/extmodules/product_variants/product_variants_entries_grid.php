<?php
/*
  $Id: product_variants_entries_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.product_variants.ProductVariantsEntriesGrid = function (config) {

  config = config || {};
  
  this.variantsGroupsId = null;
  this.variantsGroupsName = null;
    
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.region = 'east';
  config.border = false;
  config.split = true;
  config.minWidth = 240;
  config.maxWidth = 320;
  config.width = 260;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'product_variants',
      action: 'list_product_variants_entries'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'products_variants_values_id'
    }, [
      'products_variants_values_id',
      'products_variants_values_name',
      'sort_order'
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
    {id: 'name', header: '<?php echo $osC_Language->get("table_heading_entries");?>', dataIndex: 'products_variants_values_name'},
    {header: '<?php echo $osC_Language->get("table_heading_order");?>', dataIndex: 'sort_order', align: 'center'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'name';
  
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
  
  Toc.product_variants.ProductVariantsEntriesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.product_variants.ProductVariantsEntriesGrid, Ext.grid.GridPanel, {

  iniGrid: function (record) {
    this.variantsGroupsId = record.get('products_variants_groups_id');
    this.variantsGroupsName = record.get('products_variants_groups_name');
    var store = this.getStore();
    
    store.baseParams['products_variants_groups_id'] = record.get('products_variants_groups_id');
    store.load();
  },

  onAdd: function () {
    if (this.variantsGroupsId) {
      var dlg = this.owner.createProductVariantsEntriesDialog();
      
      dlg.on('saveSuccess', function() {
        this.onRefresh();
      }, this);
      
      dlg.show(this.variantsGroupsId);
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onEdit: function (record) {
    var variantsValuesId = record.get('products_variants_values_id');
    var dlg = this.owner.createProductVariantsEntriesDialog();
    dlg.setTitle(this.variantsGroupsName);
    
    dlg.on('saveSuccess', function() {
        this.onRefresh();
      }, this);
      
    dlg.show(this.variantsGroupsId, variantsValuesId);
  },

  onDelete: function (record) {
    var variantsValuesId = record.get('products_variants_values_id');
    var variantsGroupsId = this.variantsGroupsId;
     
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function (btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'product_variants',
              action: 'delete_product_variants_entry',
              products_variants_values_id: variantsValuesId,
              products_variants_groups_id: variantsGroupsId
            },
            callback: function (options, success, response) {
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
  },
  
  onBatchDelete: function () {
    var keys = this.getSelectionModel().selections.keys;
    var variantsGroupsId = this.variantsGroupsId;
    
    if (keys.length > 0) {
      var batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm, 
        function (btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              waitMsg: TocLanguage.formSubmitWaitMsg,
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'product_variants',
                action: 'delete_product_variants_entries',
                batch: batch,
                products_variants_groups_id: variantsGroupsId
              },
              callback: function (options, success, response) {
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
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRefresh: function () {
    this.getStore().load();
  },
  
  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  },
   
  reset: function() {
    this.setTitle('<?php echo $osC_Language->get('table_heading_entries'); ?>');
    this.variantsGroupsId = null;
    this.variantsGroupsName = null;
    this.getStore().removeAll();
  } 
});