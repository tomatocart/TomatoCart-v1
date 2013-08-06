<?php
/*
  $Id: feature_products_manager_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.feature_products_manager.ProductsManagerGrid = function (config) {
  config = config || {};
  
  config.border = false;
  config.store = new Ext.data.GroupingStore({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'feature_products_manager',
      action: 'list_products'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'products_id'
    }, [
      'products_id',
      'products_name',
      'sort_order'
    ]),
    autoLoad: true
  });
  
  var rowActions = new Ext.ux.grid.RowActions({
    actions: [
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4: 2
  });
  rowActions.on('action', this.onRowAction, this);
  config.plugins = rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm =  new Ext.grid.ColumnModel([
    config.sm,
    {id: 'frontpage_products_name', header: '<?php echo $osC_Language->get("table_heading_products"); ?>', dataIndex: 'products_name'}, 
    {header: '<?php echo $osC_Language->get("table_heading_sort_order"); ?>', dataIndex: 'sort_order', editor: new Ext.form.NumberField({allowBlank: false, allowNegative: true, minValue: 0}), align: 'right'}, 
    rowActions
  ]);
  config.autoExpandColumn = 'frontpage_products_name';
  
  config.clicksToEdit = 1;
  config.listeners = {
    'afteredit': this.onGrdAfterEdit,
    scope: this
  }; 
  
  var dsCategories = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'feature_products_manager',
      action: 'get_categories',
      top: 1
    },
    reader: new Ext.data.JsonReader({
      fields:['id','text'],
      root: Toc.CONF.JSON_READER_ROOT
    }),
    autoLoad: true
  });
  
  config.cboCategories = new Toc.CategoriesComboBox({
    store: dsCategories,
    valueField: 'id',
    displayField: 'text',
    emptyText: '<?php echo $osC_Language->get("top_category"); ?>',
    triggerAction: 'all',
    readOnly: true,
    listeners: {
      select: this.onSearch,
      scope: this
    }
  });
  
  config.tbar = [{
    text: TocLanguage.btnDelete,
    iconCls: 'remove',
    handler: this.onBatchDelete,
    scope: this
  },{
    text: TocLanguage.btnRefresh,
    iconCls: 'refresh',
    handler: this.onRefresh,
    scope: this
  }, '->', config.cboCategories];

  Toc.feature_products_manager.ProductsManagerGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.feature_products_manager.ProductsManagerGrid, Ext.grid.EditorGridPanel, {

  onDelete: function (record) {
    var productsId = record.get('products_id');
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function (btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'feature_products_manager',
              action: 'delete_product',
              products_id: productsId
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
                module: 'feature_products_manager',
                action: 'delete_products',
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
        }, this);

    } else {
       Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onGrdAfterEdit: function(e) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'feature_products_manager',
        action: 'update_sort_order',
        products_id: e.record.get("products_id"),
        sort_value: e.value
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
               
        if (result.success == true) {
          e.record.commit();
          
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
          this.getStore().reload();
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
  },
  
  onRefresh: function () {
    this.getStore().reload();
  },
  
  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
    }
  },
  
  onSearch: function(){
    var categoriesId = this.cboCategories.getValue() || null;
    var store = this.getStore();
          
    store.baseParams['categories_id'] = categoriesId;
    store.reload();
  }  
});