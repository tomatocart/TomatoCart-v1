<?php
/*
  $Id: product_variants_groups_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.product_variants.ProductVariantsGroupsGrid = function (config) {

  config = config || {};
  
  config.region = 'center';
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'product_variants',
      action: 'list_product_variants'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'products_variants_groups_id'
    }, [
      'products_variants_groups_id', 
      'products_variants_groups_name', 
      'total_entries',
      'sort_order'
    ]),
    autoLoad: true
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions: [
     {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
     {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
    widthIntercept: Ext.isSafari ? 4: 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.cm = new Ext.grid.ColumnModel([
    {id: 'product_variants_groups', header: '<?php echo $osC_Language->get("table_heading_variant_groups"); ?>', dataIndex: 'products_variants_groups_name'},
    {header: '<?php echo $osC_Language->get("table_heading_order"); ?>', dataIndex: 'sort_order', align: 'center'},
    {header: '<?php echo $osC_Language->get("table_heading_total_entries"); ?>', dataIndex: 'total_entries', align: 'center'}, 
    config.rowActions
  ]);
  config.selModel = new Ext.grid.RowSelectionModel({singleSelect: true});
  config.autoExpandColumn = 'product_variants_groups';
  
  config.listeners = {
   'rowclick': this.onGrdRowClick
  };   
  
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
    }
  ];
      
  config.bbar = new Ext.PagingToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    iconCls: 'icon-grid',
    displayInfo: true,
    displayMsg: TocLanguage.displayMsg,
    emptyMsg: TocLanguage.emptyMsg
  });

  Toc.product_variants.ProductVariantsGroupsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.product_variants.ProductVariantsGroupsGrid, Ext.grid.GridPanel, {

  onAdd: function () {
    var dlg = this.owner.createProductVariantsGroupsDialog();
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onEdit: function (record) {
    var dlg = this.owner.createProductVariantsGroupsDialog();
    dlg.setTitle('<?php echo $osC_Language->get("action_heading_new_variant_group"); ?>');

    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get('products_variants_groups_id'));
  },

  onDelete: function (record) {
    var groupsId = record.get('products_variants_groups_id');
    
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
              action: 'delete_product_variant',
              products_variants_groups_id: groupsId
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

  onGrdRowClick: function(grid, rowIndex, e) {
    var record = grid.getStore().getAt(rowIndex);
    this.fireEvent('selectchange', record);
  },
    
  onRefresh: function () {
    this.getStore().reload();
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
  }
});