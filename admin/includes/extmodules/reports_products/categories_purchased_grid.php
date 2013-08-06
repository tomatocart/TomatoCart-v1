<?php
/*
  $Id: categories_purchased_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

  Toc.reports_products.CategoriesPurchasedGrid = function(config) {
    
    config = config || {};
    
    config.border = false;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
    
    config.ds = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'reports_products',
        action: 'list_categories_purchased'
      },
      autoLoad: true,
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
      }, [
        'categories_id',
        'total',
        'quantity',
        'categories_name',
        'path'
        ]
      )
    }); 
    
    config.cm = new Ext.grid.ColumnModel([
      { id: 'categories_name', header: '<?php echo $osC_Language->get('table_heading_categories'); ?>',dataIndex: 'categories_name'},
      { header: '<?php echo $osC_Language->get('table_heading_quantity'); ?>',dataIndex: 'quantity', align: 'right', sortable: true},
      { header: '<?php echo $osC_Language->get('table_heading_total'); ?>',dataIndex: 'total', sortable: true, width: 150, align: 'right', renderer: tocCurrenciesFormatter}
    ]);
    config.autoExpandColumn = 'categories_name';
    
    dsCategories = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'reports_products',
        action: 'get_categories'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
      }, [
        'id',
        'text'
      ]),
      autoLoad: true
    });
    
    config.cboCategories = new Toc.CategoriesComboBox({
      store: dsCategories,
      valueField: 'id',
      displayField: 'text',
      readOnly: true,
      emptyText: '<?php echo $osC_Language->get("top_category"); ?>',
      triggerAction: 'all',
      listeners: {
        select: this.onSearch,
        scope: this
      }
    });
    
    config.datStartDate = new Ext.form.DateField({
      width: 150, 
      format: 'Y-m-d', 
      emptyText: '<?php echo $osC_Language->get("field_start_date"); ?>'
    });
    
    config.datEndDate = new Ext.form.DateField({
      width: 150, 
      format: 'Y-m-d', 
      emptyText: '<?php echo $osC_Language->get("field_end_date"); ?>'
    });
    
    config.tbar = [
      {
        text: TocLanguage.btnBack,
        iconCls: 'back',
        handler: this.onUp,
        scope: this
      },
      {
        text: TocLanguage.btnRefresh,
        iconCls: 'refresh',
        handler: this.onRefresh,
        scope: this
      },
      '->',
      config.datStartDate,
      ' ',
      config.datEndDate,
      ' ',
      config.cboCategories,
      ' ',
      { 
        iconCls: 'search',
        handler: this.onSearch,
        scope: this
      }
    ];
    
    config.listeners = {rowdblclick: this.onRowDblClick};
    
    Toc.reports_products.CategoriesPurchasedGrid.superclass.constructor.call(this, config);
  };
  
  Ext.extend(Toc.reports_products.CategoriesPurchasedGrid, Ext.grid.GridPanel, {
    onRowDblClick: function() {
      var record = this.getSelectionModel().getSelected();
      var store = this.getStore();
      
      this.cboCategories.setValue(record.get('path'));
      store.baseParams['categories_id'] = record.get('categories_id');
      this.getStore().reload();
    },
    
    onRefresh: function() {
      this.getStore().reload();
    },
    
    onUp: function() {
      var cPath = this.cboCategories.getValue() || null;
      var categories = cPath.toString().split('_');
      var store = this.getStore();
      
      if (categories.length > 1) {
        store.baseParams['categories_id'] = categories[categories.length-2];
  
        categories.pop();
        cPath = categories.join('_');
        this.cboCategories.setValue(cPath);
      } else {
        this.cboCategories.setValue(0);
        store.baseParams['categories_id'] = 0;
      }
  
      store.reload();
    },
    
    onSearch: function(){
      var startDate = this.datStartDate.getValue() || null;
      var endDate = this.datEndDate.getValue() || null;
      var categoriesId = this.cboCategories.getValue() || null;
      var store = this.getStore();
        
      store.baseParams['start_date'] = startDate;
      store.baseParams['end_date'] = endDate;
      store.baseParams['categories_id'] = categoriesId;
      store.reload();
    }
  });