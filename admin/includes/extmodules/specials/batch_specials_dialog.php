<?php
/*
  $Id: batch_specials_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.specials.StatusCheckColumn = function(config){
  Ext.apply(this, config);
  if(!this.id){
    this.id = Ext.id();
  }
  this.renderer = this.renderer.createDelegate(this);
};

Toc.specials.StatusCheckColumn.prototype = {
  init : function(grid){
    this.grid = grid;
    this.grid.on('render', function(){
      var view = this.grid.getView();
      view.mainBody.on('mousedown', this.onMouseDown, this);
    }, this);
  },

  onMouseDown : function(e, t){
    if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
      e.stopEvent();
      var index = this.grid.getView().findRowIndex(t);
      var record = this.grid.store.getAt(index);
      record.set(this.dataIndex, !record.data[this.dataIndex]);
      this.grid.store.commitChanges();
    }
  },

  renderer : function(v, p, record) {
    p.css += ' x-grid3-check-col-td'; 
    return '<div class="x-grid3-check-col'+(v?'-on':'')+' x-grid3-cc-'+this.id+'">&#160;</div>';
  }
};

Toc.specials.BatchSpecialsDialog = function (config) {
  config = config || {};
  
  config.id = 'batch-specials-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_specials'); ?>';
  config.width = 800;
  config.autoHeight = true;
  config.layout = 'fit';
  config.modal = true;
  config.iconCls = 'icon-specials-win';
  config.items = [this.buildSearchForm(), this.buildProductEditorGrid()];
  
  this.addEvents({'saveSuccess': true, 'addVariants': true});
  
  Toc.specials.BatchSpecialsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.specials.BatchSpecialsDialog, Ext.Window, {

  buildSearchForm: function() {
    this.cboManufacturers = new Ext.form.ComboBox({
      name: 'cManufacturer',
      hiddenName: 'cManufacturer',
      fieldLabel: '<?php echo $osC_Language->get("field_manufacturer"); ?>',
      emptyText: '<?php echo $osC_Language->get("top_manufacturers"); ?>',
      valueField: 'manufacturers_id',
      displayField: 'manufacturers_name',
      triggerAction: 'all',
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'specials',
          action: 'list_manufacturers'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'manufacturers_id',
          fields: ['manufacturers_id', 'manufacturers_name']
        })
      })
    });
 
    this.cboCategories = new Toc.CategoriesComboBox({
      name: 'categories',
      hiddenName: 'categories_id',
      fieldLabel: '<?php echo $osC_Language->get("field_categories"); ?>',
      emptyText: '<?php echo $osC_Language->get("top_category"); ?>',
      valueField: 'id',
      displayField: 'text',
      triggerAction: 'all',
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
	      baseParams: {
	        module: 'specials',
	        action: 'list_categories'
	      },
	      reader: new Ext.data.JsonReader({
	        root: Toc.CONF.JSON_READER_ROOT,
	        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
	        id: 'id',
	        fields: ['id', 'text']
	      })
	    })
    });

    this.txtProductName = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_products_name'); ?>',
      name: 'products_name'
    });
    
    this.txtProductSku = new Ext.form.TextField({
      fieldLabel: '<?php echo $osC_Language->get('field_products_sku'); ?>',
      name: 'products_sku'
    });
    
    this.btnSearch = new Ext.Button({
      text: '<?php echo $osC_Language->get('button_search'); ?>',
      handler: this.searchProducts,
      scope: this
    });
    
    this.frmSearch = new Ext.form.FormPanel({
      style: 'padding: 8px',
      border: false,
      autoHeight: true,
      buttonAlign: 'right',
      labelSeparator: ' ',
      items: 
      [
        this.chkVariants = new Ext.form.Checkbox({
          fieldLabel: '<?php echo $osC_Language->get('field_variants'); ?>',
          name: 'variants',
          checked: false,
          listeners: {
            check: this.onChkVariantsChecked,
            scope: this
          }
        }),
        
        {
          layout: 'column',
          border: false,
          items: 
          [
            {
              columnWidth: .49,
              layout: 'form',
              border: false,
              defaults: {
                anchor: '97%'
              },
              layoutConfig: {
                labelSeparator: ' '
              },
              items: [this.cboManufacturers, this.txtProductName]
            },
            {
              columnWidth: .50,
              layout: 'form',
              border: false,
              defaults: {
                anchor: '97%'
              },
              layoutConfig: {
                labelSeparator: ' '
              },
              items: [this.cboCategories, this.txtProductSku]
            }
          ]
        }
      ],
      buttons: [this.btnSearch]
    });
    
    return this.frmSearch;
  },
  
  buildProductEditorGrid: function() {
    var rowActions = new Ext.ux.grid.RowActions({
      actions: [
        {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
      ],
      widthIntercept: Ext.isSafari ? 4 : 2
    });
    rowActions.on('action', this.onProductGridRowAction, this);    
    
    var sm = new Ext.grid.CheckboxSelectionModel();
    var store = new Ext.data.GroupingStore({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'specials',
        action: 'load_products'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
      }, [
        'products_id',
        'products_name',
        'products_price',
        'special_price',
        'start_date',
        'expires_date',
        'status'
      ]),
      autoLoad: false
    });
     
    var checkColumn = new Toc.specials.StatusCheckColumn({
      header: '<?php echo $osC_Language->get("table_heading_status"); ?>',
      width: 50,
      textAlign: 'center',
      dataIndex: 'status'
    });
    
    var specialRender = function (v) {
      if (v == 0) {
        return '';
      } else {
        return tocCurrenciesFormatter(v);
      }
    }

    this.grdProducts = new Ext.grid.EditorGridPanel({
      clicksToEdit: 1,
      viewConfig: {
        emptyText: TocLanguage.gridNoRecords
      },
      autoScroll: true,
      style: 'padding: 10px',
      border: true,
      height: 300,
      store: store,
      plugins: [rowActions, checkColumn],
      sm: sm,
      cm: new Ext.grid.ColumnModel([
        sm,
        {id: 'product_name', header: '<?php echo $osC_Language->get("table_heading_products"); ?>', dataIndex: 'products_name'},
        {header: '<?php echo $osC_Language->get("table_heading_price"); ?>', dataIndex: 'products_price', width: 80, renderer: tocCurrenciesFormatter, align: 'right'},
        {header: '<?php echo $osC_Language->get("table_heading_products_price_net"); ?>', dataIndex: 'special_price', width: 100, renderer: specialRender, editor: new Ext.form.NumberField({allowBlank: false, allowNegative: true, minValue: 0}), align: 'right'},
        {header: '<?php echo $osC_Language->get("table_heading_products_date_start"); ?>', dataIndex: 'start_date', width: 100, renderer: Ext.util.Format.dateRenderer('Y-m-d'), editor: new Ext.form.DateField({allowBlank: false}), align: 'center'},
        {header: '<?php echo $osC_Language->get("table_heading_products_date_expires"); ?>', dataIndex: 'expires_date', width: 100, renderer: Ext.util.Format.dateRenderer('Y-m-d'), editor: new Ext.form.DateField({allowBlank: false}), align: 'center'},
        checkColumn,
        rowActions 
      ]),
      autoExpandColumn: 'product_name',
      buttons: [
      {
        text:TocLanguage.btnSave,
        handler: this.submitSpecials,
        scope:this
      },
      {
        text: TocLanguage.btnClose,
        handler: function(){
          this.close();
        },
        scope:this
      }]
    });

    return this.grdProducts; 
  },
  
  onProductGridRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.removeRecord(record);
        break;
    }
  },
  
  removeRecord: function(record) {
    this.grdProducts.getStore().remove(record);  
  },
  
  searchProducts: function() {
    var store = this.grdProducts.store; 
    store.baseParams['cManufacturer'] = this.cboManufacturers.getValue();
    store.baseParams['categories_id'] = this.cboCategories.getValue();
    store.baseParams['products_sku'] = this.txtProductSku.getValue();
    store.baseParams['products_name'] = this.txtProductName.getValue();
    
    store.reload();
  },
  
  submitSpecials: function() {
    var store = this.grdProducts.getStore();
    var products = [];
    var errors = [];
    
    store.each(function (record){
      var products_name = record.get('products_name');
      var special_price = record.get('special_price');
      var start_date = record.get('start_date');
      var expires_date = record.get('expires_date');
      var error = false;
      
      if (special_price <= 0) {
        error = true;
        
        errors.push('<?php echo $osC_Language->get('ms_error_special_price_empty_for'); ?>' + products_name);
      }
      
      if (Ext.isEmpty(start_date)) {
        error = true;
        
        errors.push('<?php echo $osC_Language->get('ms_error_start_date_empty_for'); ?>' + products_name);
      }
      
      if (Ext.isEmpty(expires_date)) {
        error = true;
        
        errors.push('<?php echo $osC_Language->get('ms_error_expires_date_empty_for'); ?>' + products_name);
      }
      
      if (error == false) {
        products.push({
          products_id: record.get('products_id'),
          products_name: products_name,
          special_price: special_price,
          start_date: start_date,
          expires_date: expires_date,
          status: record.get('status')
        });
      } 
    });

    if (errors.length > 0) {
      alert(errors.join('\n'));
      
      return;
    }
    
    if (products.length == 0) {
      alert('<?php echo $osC_Language->get('ms_error_specials_products'); ?>');
      
      return;
    }
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
		    module: 'specials',
        action: 'save_batch_specials',
        products: Ext.encode(products),
        variants: this.productsType 				
	    },
	    callback: function (options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.fireEvent('saveSuccess', result.feedback);
	        this.close();
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
  },
  
  onChkVariantsChecked: function(checkbox, checked) {
    var store = this.grdProducts.getStore();
    
    if (checked) {
      store.baseParams['variants'] = 1;
      store.baseParams['action'] = 'load_variants_products';
      
      this.productsType = '<?php echo PRODUCTS_TYPE_VARIANTS; ?>';
      
      this.fireEvent('addVariants', 1);
    } else {
      store.baseParams['variants'] = 0;
      store.baseParams['action'] = 'load_products';
      
      this.productsType = '<?php echo PRODUCTS_TYPE_GENERAL; ?>';
      
      this.fireEvent('addVariants', 0);
    }
  }
});