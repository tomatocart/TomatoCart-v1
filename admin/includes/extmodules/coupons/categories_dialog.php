<?php
/*
  $Id: coupons_categories_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.coupons.CategoriesDialog = function(config) {

  config = config || null;
  
  config.id = 'coupons-categories-dialog-win';
  config.title = '<?php echo $osC_Language->get('dialog_heading_search_categories'); ?>';
  config.layout = 'fit';
  config.width = 600;
  config.height = 400;
  config.modal = true;
  config.iconCls = 'icon-categories-win';
  config.items = this.buildCategoriesGrid();
  
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
  
  this.current_category_id = null; 
  
  this.addEvents({'save': true});
  
  Toc.coupons.CategoriesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.coupons.CategoriesDialog, Ext.Window, {

  buildCategoriesGrid: function() {
    var dsParentCategories = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'coupons',
        action: 'get_categories'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'parent_category_id',
        fields: [
          'id', 
          'text'
        ]
      }),
      listeners: {
        load: this.onDsParentCategoriesLoad,
        scope: this
      },
      autoLoad: true
    });
    
    this.cboParentCategories = new Ext.form.ComboBox({
      store: dsParentCategories,
      valueField: 'id',
      displayField: 'text',
      readOnly: true,
      mode: 'local',
      emptyText: '<?php echo $osC_Language->get("top_category"); ?>',
      triggerAction: 'all',
      listeners: {
        select: this.onCboParentCategoriesSelect,
        scope: this
      }
    });  
  
    this.txtSearch = new Ext.form.TextField({name: 'search', enableKeyEvents: true});
    this.txtSearch.on('keydown', function(fldSearch, e) {
      if (e.getKey() == e.ENTER) {
        this.onSearch();
      }
    }, this);
        
    var dsCategories = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: { 
        module: 'coupons',
        action: 'list_categories'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'categories_id'
      }, [
        'categories_id', 
        'categories_name',
        'path'
      ]),
      autoLoad: true
    });
      
    var sm = new Ext.grid.CheckboxSelectionModel();
    this.grdCategories = new Ext.grid.GridPanel({
      viewConfig: {emptyText: TocLanguage.gridNoRecords},
      border: false,
      sm: sm,
      ds: dsCategories,
      cm: new Ext.grid.ColumnModel([
        sm,
        {id: 'categories_name', header: '<?php echo $osC_Language->get("table_heading_categories"); ?>', dataIndex: 'categories_name'} 
      ]),
      autoExpandColumn: 'categories_name',
      listeners: {
        rowdblclick: this.onGrdRowDbClick,
        scope: this
      },
      tbar: [
        {
          text: TocLanguage.btnBack,
          iconCls: 'back',
          handler: this.onUp,
          scope: this
        },
        '->', 
        this.txtSearch,
        ' ', 
        this.cboParentCategories, 
        ' ', 
        {
          iconCls: 'search',
          handler: this.onSearch,
          scope: this
        }
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: dsCategories,
        iconCls: 'icon-grid',
        displayInfo: true,
        displayMsg: TocLanguage.displayMsg,
        emptyMsg: TocLanguage.emptyMsg
      })
    });
    
    return this.grdCategories;
  },
  
  onSearch: function () {
    this.current_category_id = this.cboParentCategories.getValue() || null;
    var store = this.grdCategories.getStore();
    
    store.baseParams['search'] = this.txtSearch.getValue() || null;
    store.baseParams['categories_id'] = this.current_category_id;
    store.reload();
  },
      
  onUp: function () {
    this.grdCategories.getStore().baseParams['search'] = null;
    this.txtSearch.setValue(null);
    
    var cPath = this.cboParentCategories.getValue() || '';
    var categories = cPath.toString().split('_');
    
    if (categories.length > 1) {
      this.grdCategories.getStore().baseParams['categories_id'] = categories[categories.length - 2];
  
      categories.pop();
      cPath = categories.join('_');
      
      this.cboParentCategories.setValue(cPath);
      this.current_category_id = cPath;
    } else {
      this.grdCategories.getStore().baseParams['categories_id'] = 0;
      this.cboParentCategories.setValue(0);
      
      this.current_category_id = null;
    }
  
    this.grdCategories.getStore().reload();
  },    
      
  onGrdRowDbClick: function () {
    var store = this.grdCategories.getStore();
    var categoriesId = this.grdCategories.getSelectionModel().getSelected().get('categories_id');
    var path = this.grdCategories.getSelectionModel().getSelected().get('path'); 
    
    store.baseParams['search'] = null;
    this.txtSearch.setValue(null);
              
    this.cboParentCategories.setValue(path);
    store.baseParams['categories_id'] = categoriesId;
    store.reload();
  },
      
  onDsParentCategoriesLoad: function() {
    this.cboParentCategories.setValue(this.current_category_id);
  },
      
  onCboParentCategoriesSelect: function() {
    this.grdCategories.getStore().baseParams['search'] = null;
    this.txtSearch.setValue(null);
    
    this.current_category_id = this.cboParentCategories.getValue();
    
    this.grdCategories.getStore().baseParams['categories_id'] = this.current_category_id;
    this.grdCategories.getStore().reload();
  },
        
  onSave: function() {
    var categories = this.grdCategories.getSelectionModel().getSelections();
    
    this.fireEvent('save', categories);
    this.close();
  }
});