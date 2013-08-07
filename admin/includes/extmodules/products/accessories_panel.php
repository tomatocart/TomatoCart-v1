<?php
/*
  $Id: accessories_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.products.AccessoriesPanel = function(config) {
  config = config || {};
  
  config.border = false;
  config.title = '<?php echo $osC_Language->get('section_accessories'); ?>';
  
  config.productsId = config.productsId || null;
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'products',
      action: 'get_accessories',
      products_id: config.productsId
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'accessories_id'
    },  [
      'accessories_id',
      'products_name'
    ]),
    autoLoad: true
  }); 
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
      widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.RowSelectionModel({ singleSelect: true });
  config.cm = new Ext.grid.ColumnModel([
    new Ext.grid.RowNumberer(),
    {id: 'accessories_products_name', header: '<?php echo $osC_Language->get('table_heading_products'); ?>', dataIndex: 'products_name'},
    config.rowActions 
  ]);
  config.autoExpandColumn = 'accessories_products_name';
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
    
  config.cboProducts = new Ext.form.ComboBox({
    name: 'accessories_name',
    store: new Ext.data.Store({
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        fields: ['id', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      }),
      url:Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'get_products',
        products_id: config.productsId
      }
    }),
    displayField: 'text',
    valueField: 'id',
    triggerAction: 'all',
    selectOnFocus: true,
    editable: false,
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    emptyText: '<?php echo $osC_Language->get('section_accessories'); ?>',
    width: 400
  });
  
  config.tbar = [
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }, 
    '->', 
    config.cboProducts,
    {
      text: '<?php echo $osC_Language->get('button_insert'); ?>',
      iconCls : 'add',
      handler: this.addProduct,
      scope: this
    }
  ];
  
  Toc.products.AccessoriesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.AccessoriesPanel, Ext.grid.GridPanel, {
  addProduct: function() {
    var accessoriesId = this.cboProducts.getValue();
    var productName = this.cboProducts.getRawValue().toString();

    if (!Ext.isEmpty(accessoriesId)) {
      store = this.getStore();
      
      if (store.findExact('accessories_id', accessoriesId) == -1) {
        var record = Ext.data.Record.create([
          {name: 'accessories_id', type: 'string'},
          {name: 'products_name', type: 'string'}
        ]);
        
        var v = new record({
          accessories_id: accessoriesId,
          products_name: productName
        });
        
        store.add(v);
      }
    }
  },

  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.getStore().removeAt(row);
        break;
    }
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  getAccessoriesIds: function() {
    var batch = [];
    
    this.getStore().each(function(record) {
      batch.push(record.get('accessories_id'));
    });
    
    return batch.join(';');
  }
});
