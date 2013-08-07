<?php
/*
  $Id: products_expected_gird.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.products_expected.ProductsExpectedGrid = function (config) {
  config = config || {};
  
  config.border = false;
  config.selModel = new Ext.grid.RowSelectionModel({ singleSelect: true });
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'products_expected',
      action: 'list_products_expected'
    },
    autoLoad: true,
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'products_id'
    }, [
      'products_id', 
      'products_name', 
      'products_date_available'
      ]
    )
  });
  
  var rowActions = new Ext.ux.grid.RowActions({
    actions: [
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}
    ],
    widthIntercept: Ext.isSafari ? 4: 2
  });
  rowActions.on('action', this.onRowAction, this);
  config.plugins = rowActions;
  
  config.cm =  new Ext.grid.ColumnModel([
    {
       id: 'products_name',
       header: '<?php echo $osC_Language->get("table_heading_products"); ?>',
       dataIndex: 'products_name'
     }, {
       header: '<?php echo $osC_Language->get("table_heading_date_expected"); ?>',
       dataIndex: 'products_date_available'
     }, 
     rowActions
  ]);
  config.autoExpandColumn = 'products_name';
  
  config.tbar = [
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];
  
  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    beforePageText: TocLanguage.beforePageText,
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
                
  Toc.products_expected.ProductsExpectedGrid.superclass.constructor.call(this, config);
};
Ext.extend(Toc.products_expected.ProductsExpectedGrid, Ext.grid.GridPanel, {
  onEdit: function (record) {
    var productsId = record.get('products_id');
    var dlg = this.owner.createProductsExpectedDialog();
    dlg.setTitle(record.get('products_name'));
    
    dlg.on('saveSuccess', this.onRefresh, this);
    
    dlg.show(productsId);
  },
  
  onRefresh: function () {
    this.getStore().reload();
  },
  
  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  }
}
);