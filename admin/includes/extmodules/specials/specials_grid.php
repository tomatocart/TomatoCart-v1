<?php
/*
  $Id: specials_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.specials.SpecialsGrid = function (config) {
  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    baseParams: {
      module: 'specials',
      action: 'list_specials'
    },
    url: Toc.CONF.CONN_URL,
    autoLoad: true,
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'specials_id'
    }, [
      'products_id', 
      'products_name', 
      'products_price',
      'specials_id', 
      'specials_new_products_price'
    ])
  });
  
  var rowActions = new Ext.ux.grid.RowActions({
    header: '<?php echo $osC_Language->get("table_heading_action"); ?>',
    actions: [
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4: 2
  });
  rowActions.on('action', this.onRowAction, this);
  config.plugins = rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'specials_products_name', header: '<?php echo $osC_Language->get("table_heading_products"); ?>', dataIndex: 'products_name'}, 
    {header: '<?php echo $osC_Language->get("table_heading_price"); ?>', dataIndex: 'specials_new_products_price', width: 180}, 
    rowActions
  ]);  
  config.autoExpandColumn = 'specials_products_name';
  
  var dsProductsType = new Ext.data.SimpleStore({
    fields: ['id', 'text'],
    data: 
      [
        ['<?php echo PRODUCTS_TYPE_GENERAL; ?>','<?php echo $osC_Language->get('products_type_general'); ?>'],
        ['<?php echo PRODUCTS_TYPE_VARIANTS; ?>','<?php echo $osC_Language->get('products_type_variants'); ?>'],
      ]
  });
  
  this.productsType = '<?php echo PRODUCTS_TYPE_GENERAL; ?>';
  
  this.cboProductsType = new Ext.form.ComboBox({
    fieldLabel: '<?php echo $osC_Language->get('field_products_type'); ?>',
    xtype: 'combo', 
    store: dsProductsType, 
    name: 'products_type', 
    mode: 'local',
    width: 150,
    hiddenName: 'products_type', 
    displayField: 'text', 
    valueField: 'id', 
    triggerAction: 'all', 
    editable: false,
    forceSelection: true,      
    value: '<?php echo PRODUCTS_TYPE_GENERAL; ?>',
    listeners: {
      select: this.onProductsTypeSelect,
      scope: this
    }
  });
  
  dsManufacturers = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'specials',
      action: 'list_manufacturers'
    },
    autoLoad: true,
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'manufacturers_id',
      fields: [
        'manufacturers_id', 
        'manufacturers_name'
      ]
    })
  });
  
  config.cboManufacturers = new Ext.form.ComboBox({
    width: 150,
    store: dsManufacturers,
    displayField: 'manufacturers_name',
    mode: 'remote',
    emptyText: '<?php echo $osC_Language->get("top_manufacturers"); ?>',
    valueField: 'manufacturers_id',
    editable: false,
    triggerAction: 'all',
    listeners: {
      select: this.onSearch,
      scope: this
    }
  });

  dsCategories = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'specials',
      action: 'list_categories'
    },
    autoLoad: true,
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'id',
      fields: [
        'id', 
        'text'
      ]
    })
  });
    
  config.cboCategories = new Toc.CategoriesComboBox({
    width: 150,
    store: dsCategories,
    mode: 'remote',
    emptyText: '<?php echo $osC_Language->get("top_category"); ?>',
    valueField: 'id',
    displayField: 'text',
    triggerAction: 'all',
    editable: false,
    listeners: {
      select: this.onSearch,
      scope: this
    }
  });
  
  config.txtSearch = new Ext.form.TextField({
    width: 150,
    hideLabel: true
  });
  
  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls: 'add',
      handler: this.onAdd,
      scope: this
    }, 
    '-',
    {
      text: TocLanguage.btnBatchAdd,
      iconCls: 'add',
      handler: this.onBatchAdd,
      scope: this
    },
    '-',
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    },
    '->',
    this.cboProductsType, 
    ' ', 
    config.cboManufacturers, 
    ' ', 
    config.cboCategories, 
     ' ',
    config.txtSearch, 
    ' ', 
    {
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }
  ];
  
  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnAdd,
        iconCls:'add',
        handler: function() {
          thisObj.onAdd();
        }
      }, 
      {
        text: TocLanguage.btnDelete,
        iconCls:'remove',
        handler: function() {
          thisObj.onBatchDelete();
        }
      }
    ],
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
  
  Toc.specials.SpecialsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.specials.SpecialsGrid, Ext.grid.GridPanel, {
  
  onAdd: function () {
    var dlg = this.owner.createSpecialsDialog({'productsType': this.productsType});
    dlg.setTitle('<?php echo $osC_Language->get("action_heading_new_special"); ?>');
    
    //attach event for supporting variants specials
    this.attachAddVariantsEvent(dlg);
    
    dlg.on('saveSuccess', function() {
      this.getStore().reload();
    }, this);  
    
    dlg.show();
  },
  
  onBatchAdd: function() {
    var dlg = this.owner.createBatchSpecialsDialog();
    dlg.setTitle('<?php echo $osC_Language->get("action_heading_new_specials"); ?>');
    
    //attach event for supporting variants specials
    this.attachAddVariantsEvent(dlg);
    
    dlg.on('saveSuccess', function() {
      this.getStore().reload();
    }, this);  
    
    dlg.show();
  },
  
  onEdit: function (record) {
    var specialsId = record.get('specials_id');
    
    //support the variants speicals
    var dlg = this.owner.createSpecialsDialog({'productsType' : this.productsType});
    
    dlg.setTitle(record.get('products_name'));
    
    dlg.on('saveSuccess', function() {
      this.getStore().reload();
    }, this);        
    
    dlg.show(specialsId);
  },
  
  onDelete: function (record) {
    var specialsId = record.get('specials_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle,
      TocLanguage.msgDeleteConfirm, 
      function (btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'specials',
              action: 'delete_special',
              specials_id: specialsId,
              products_type: this.productsType
            },
            callback: function (options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({
                  title: TocLanguage.msgSuccessTitle,
                  html: result.feedback
                });
                
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
                module: 'specials',
                action: 'delete_specials',
                batch: batch,
                products_type: this.productsType
              },
              callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({
                    title: TocLanguage.msgSuccessTitle,
                    html: result.feedback
                  });
                  
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
  
  onSearch: function () {
    var store = this.getStore();
    
    store.baseParams['search'] = this.txtSearch.getValue();
    store.baseParams['manufacturers_id'] = this.cboManufacturers.getValue();
    store.baseParams['category_id'] = this.cboCategories.getValue();
    store.reload();
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
  },
  
  onProductsTypeSelect: function(combo, record) {
    var type = record.get('id');
    
    if (this.productsType != type) {
      this.productsType = type;
      
      this.resetGrid();
    }
  },
  
  resetGrid: function() {
    //reset the manufactuers, category and search field
    this.cboManufacturers.reset();
    this.cboCategories.reset();
    this.txtSearch.setValue('');
    
    var store = this.getStore();
    
    delete store.baseParams['search'];
    delete store.baseParams['manufacturers_id'];
    delete store.baseParams['category_id'];
    
    switch(this.productsType) {
      case '<?php echo PRODUCTS_TYPE_GENERAL;?>':
        store.baseParams['action'] = 'list_specials';
        break;
        
      case '<?php echo PRODUCTS_TYPE_VARIANTS; ?>':
        store.baseParams['action'] = 'list_variants_specials';
        break;
      default:
        store.baseParams['action'] = 'list_specials';
    }
    
    store.reload();
  },
  
  attachAddVariantsEvent: function(dlg) {
    dlg.on('addVariants', function(variantsChk) {
      if (variantsChk == 1) {
        this.cboProductsType.setValue('<?php echo PRODUCTS_TYPE_VARIANTS; ?>');
        this.productsType = '<?php echo PRODUCTS_TYPE_VARIANTS; ?>';
      }else {
        this.cboProductsType.setValue('<?php echo PRODUCTS_TYPE_GENERAL; ?>');
        this.productsType = '<?php echo PRODUCTS_TYPE_GENERAL; ?>';
      }
      
      this.resetGrid();
    }, this);
  }
});