<?php
/*
  $Id: products_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.coupons.ProductsDialog = function(config) {

  config = config || null;
  
  config.id = 'coupons-products-dialog-win';
  config.title = '<?php echo $osC_Language->get('dialog_heading_search_products'); ?>';
  config.layout = 'fit';
  config.width = 600;
  config.height = 400;
  config.modal = true;
  config.iconCls = 'icon-products-win'; 
  config.items = this.buildProductsGrid();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.onSave();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() {
        this.close();
      },
      scope: this
    }
  ];
  
  this.addEvents({'save': true});
  
  Toc.coupons.ProductsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.coupons.ProductsDialog, Ext.Window, {

  buildProductsGrid: function() {  
    var dsCategories = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'coupons',
        action: 'get_categories',
        top: 1 
      },
      reader: new Ext.data.JsonReader({
        fields:['id','text'],
        root: Toc.CONF.JSON_READER_ROOT
      }),
      autoLoad: true
    });
    
    this.cboCategories = new Ext.form.ComboBox({
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
      
    var dsProducts = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'coupons',
        action: 'list_products'        
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'products_id'
      }, [
        {name: 'products_id'},
        {name: 'products_name'}
      ]),
      autoLoad: true
    });
    
    this.txtSearch = new Ext.form.TextField({
      width: 160,
      paramName: 'search'
    });
        
    var sm = new Ext.grid.CheckboxSelectionModel();
    this.grdProducts = new Ext.grid.GridPanel({
      viewConfig: {
        emptyText: TocLanguage.gridNoRecords
      },
      border: false,
      ds: dsProducts,
      sm: sm,
      cm: new Ext.grid.ColumnModel([
        sm,
        {
          id:'coupons_products_name', 
          header: "<?php echo $osC_Language->get('table_heading_products'); ?>", 
          sortable: true, 
          dataIndex: 'products_name'
        }
      ]),
      autoExpandColumn: 'coupons_products_name',
      tbar: [
        '->',
        this.txtSearch,
        ' ',
        this.cboCategories,
        ' ',
        {
          iconCls : 'search',
          handler : this.onSearch,
          scope : this
        } 
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: dsProducts,
        iconCls: 'icon-grid',
        displayInfo: true,
        displayMsg: TocLanguage.displayMsg,
        emptyMsg: TocLanguage.emptyMsg
      })
    });    
    
    return this.grdProducts;
  },
  
  onSearch: function() {
    var store = this.grdProducts.getStore();
    
    store.baseParams['search'] = this.txtSearch.getValue() || null;
    store.baseParams['categories_id'] = this.cboCategories.getValue() || null;
    store.reload();
  },
  
  onSave: function() {
    var products = this.grdProducts.getSelectionModel().getSelections();
    
    this.fireEvent('save', products);
    
    this.close();
  }
});