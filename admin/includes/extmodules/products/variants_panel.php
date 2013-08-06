<?php
/*
  $Id: variants_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.products.StatusCheckColumn = function(config){
    Ext.apply(this, config);
    if(!this.id){
        this.id = Ext.id();
    }
    this.renderer = this.renderer.createDelegate(this);
};

Toc.products.StatusCheckColumn.prototype = {
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
            var record = this.grid.getStore().getAt(index);
            
            if ( this.grid.getStore().getCount() > 1 ) {
              this.grid.getStore().each(function(item) {
                if (item.get(this.dataIndex) && item != record) {
                  item.set(this.dataIndex, !item.data[this.dataIndex]);
                  item.commit();
                }
              }, this);
            }
            record.set(this.dataIndex, !record.data[this.dataIndex]);
            this.grid.getStore().commitChanges();
        }
    },

    renderer : function(v, p, record){
        p.css += ' x-grid3-check-col-td'; 
        return '<div class="x-grid3-check-col'+(v?'-on':'')+' x-grid3-cc-'+this.id+'">&#160;</div>';
    }
};

Toc.products.VariantsPanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_variants'); ?>';
  config.layout = 'border';
  
  this.groupIds = [];
  this.variantsValues = [];
  this.productsId = config.productsId || null;
  this.downloadable = false;
  this.dlgProducts = config.dlgProducts;
  
  config.items = this.buildForm(config.productsId);

  this.addEvents({'variantschange' : true});    
  
  Toc.products.VariantsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.VariantsPanel, Ext.Panel, {

  buildForm: function(productsId) {
    this.pnlVariantGroups = new Ext.Panel({
      width: 190,
      split: true,
      layout: 'form',
      border: false,
      region: 'west',
      labelAlign: 'top',
      autoScroll: true,
      tbar: [{
        text: '<?php echo $osC_Language->get('button_manage_variants_groups'); ?>',
        iconCls : 'add',
        handler: this.onBtnManageVariantsGroupsClick,
        scope: this
      }]
    });
 
    this.grdVariants = this.buildGrdVariants(productsId);
    this.pnlVariantDataContainer = this.buildVariantDataPanel();
    
    return [this.pnlVariantGroups, this.grdVariants, this.pnlVariantDataContainer];
  },
  
  
  buildGrdVariants: function(productsId) {
    var rowActions = new Ext.ux.grid.RowActions({
      actions: [
        {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
      ],
      widthIntercept: Ext.isSafari ? 4 : 2
    });
    rowActions.on('action', this.onRowAction, this);
    
    var checkColumn = new Toc.products.StatusCheckColumn({
      header: '<?php echo $osC_Language->get("table_heading_default"); ?>',
      textAlign: 'center',
      width: 50,
      dataIndex: 'default'
    });
    
    var dsVariants = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        products_id: productsId,
        action: 'get_variants_products'        
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'products_variants_id'
      }, [
        'products_variants_id',
        'variants_values',
        'variants_groups',
        'variants_values_name',
        'data',
        'default'
      ]),
      listeners: {
        load: this.onDsVariantsLoad,
        scope: this
      }
    }); 
    
    var grdVariants = new Ext.grid.GridPanel({
      region: 'center',
      border: false,
      plugins: [rowActions, checkColumn],
      ds: dsVariants,
      cm:  new Ext.grid.ColumnModel([
        {id: 'variants_values_name', header: '<?php echo $osC_Language->get("table_heading_variants"); ?>', dataIndex: 'variants_values_name'},
        checkColumn,
        rowActions
      ]),
      autoExpandColumn: 'variants_values_name'
    });
    
    grdVariants.getSelectionModel().on('rowselect', this.onGrdVariantsRowSelect, this);
    
    return grdVariants;
  },

  onDsVariantsLoad: function() {
    if (this.grdVariants.getStore().getCount() > 0) {
      this.grdVariants.getStore().each(function(record) {
        this.pnlVariantDataContainer.add(this.buildVariantDataCard(record.get('variants_values'), record.get('data')));
      }, this);
      
      var record = this.grdVariants.getStore().getAt(0);
      this.generatePnlVariantsGroups(record.get('variants_groups'));
      var variantsValuesArray = record.get('variants_values').split('-');
      
      Ext.each(variantsValuesArray, function(value) {
        this.groupIds.push(value.split('_')[0]);
      }, this);
      
      this.grdVariants.getSelectionModel().selectFirstRow();

      var cardID = record.get('variants_values');
      this.pnlVariantDataContainer.getLayout().setActiveItem(cardID);
      this.setVariantsValues(record.get('variants_groups'));
        
      this.pnlVariantDataContainer.doLayout();
      this.dlgProducts.doLayout();
    }
  },
  
  onGrdVariantsRowSelect: function(sm, row, record) {
    var cardID = record.get('variants_values');
    this.pnlVariantDataContainer.getLayout().setActiveItem(cardID);
    this.setVariantsValues(record.get('variants_groups'));
  
    this.pnlVariantDataContainer.doLayout();
    this.dlgProducts.doLayout();
  },
  
  buildVariantDataPanel: function() {
    this.pnlVariantDataContainer = new Ext.Panel({
      layout:'card',
      region: 'east',
      width: 300,
      autoScroll: true,
      split: true,
      layoutOnCardChange: true,
      border:false
    });  
    
    return this.pnlVariantDataContainer;
  },

  onProductTypeChange: function (type) {
    this.enable();
    this.downloadable = false;
     
    if (type == '<?php echo PRODUCT_TYPE_DOWNLOADABLE; ?>') {
      this.enable();
      this.downloadable = true;  
    } else if (type == '<?php echo PRODUCT_TYPE_GIFT_CERTIFICATE; ?>') {
      this.disable();
      this.downloadable = false;
    } 
  },
  
  buildVariantDataCard: function(valuesId, data) {
    var card = new Toc.products.VariantDataPanel({
      valuesId: valuesId, 
      data: data, 
      downloadable: this.downloadable, 
      dlgProducts: this.dlgProducts
    }); 
    
    return card;   
  },
  
  onBtnManageVariantsGroupsClick: function() {
    var dlg = this.owner.createVariantsGroupDialog(this.groupIds);
    
    dlg.on('groupChange', function(groups) {
      if (this.groupIds.length === 0) {
        this.generatePnlVariantsGroups(groups);
      } else {
        var ids = [];
        Ext.each(groups, function(group) {
          ids.push(group.id);
        });
        
        if ( this.groupIds.sort().toString() != ids.sort().toString()) {
          Ext.MessageBox.confirm(
            TocLanguage.msgWarningTitle, 
            '<?php echo $osC_Language->get('msg_warning_variants_groups_changed'); ?>',
            function(btn) {
              if (btn == 'yes') {
                this.deleteVariants();
                this.generatePnlVariantsGroups(groups);
              }
            }, this);
        }
      }
    }, this);
    
    dlg.show();
  },
  
  setVariantsValues: function(variants_groups) {
    Ext.each(variants_groups, function(group){
      this.pnlVariantGroups.find('name', group.name + '_' + group.id)[0].setValue(group.value);
      this.pnlVariantGroups.find('name', group.name + '_' + group.id)[0].setRawValue(group.rawvalue);
    }, this);
  },
  
  generatePnlVariantsGroups: function(groups) {
    this.groupIds = [];
    this.deletePnlVariants();
    
    if (groups.length > 0) {
      for(var i = 0 ; i < groups.length; i ++) {
        var cboVariants = {
          xtype: 'combo',
          store: new Ext.data.Store({
            url: Toc.CONF.CONN_URL,
            baseParams: {
              module: 'products',
              action: 'get_variants_values',
              group_id: groups[i].id 
            },
            reader: new Ext.data.JsonReader ({
              fields: ['id', 'text'],
              root: Toc.CONF.JSON_READER_ROOT
            }),
            autoLoad: true
          }),
          fieldLabel: groups[i].name,
          valueField: 'id',
          displayField: 'text',
          name: groups[i].name + '_' + groups[i].id,
          triggerAction: 'all',
          editable: false
        };
        
        this.groupIds.push(groups[i].id);
        this.pnlVariantGroups.add(cboVariants);
      }
      
      this.pnlVariantGroups.add(new Ext.Button({
        text: TocLanguage.btnAdd,
        iconCls: 'add',
        handler: this.addProductVariant,
        style: 'padding-top: 5px; padding-left: 110px;',
        scope: this
      }));
    }
    
    this.pnlVariantGroups.doLayout();
  },
  
  addProductVariant: function() {
    var error = false;
    var values = [];
    var names = [];  
    var groups = [];

    //get variants values
    Ext.each(this.pnlVariantGroups.findByType('combo'), function(item) {
      if (Ext.isEmpty(item.getRawValue())) {
        error = true;  
      } else {
        var values_id = item.getValue();
        var groups_id = item.getName().split('_')[1];
        
        var values_name = item.getRawValue();
        var groups_name = item.getName().split('_')[0];
        
        values.push(groups_id + '_' + values_id);
        names.push(groups_name + ': ' + values_name);
        groups.push({id: groups_id, name: groups_name, rawvalue: values_name, value: values_id});
      }
    });
    
    if (error === true) {
      Ext.MessageBox.alert(TocLanguage.msgErrTitle, '<?php echo $osC_Language->get('msg_warning_must_choose_value_for_variant_group'); ?>');
      return;
    }
    
    //check whether variants combination exist
    //
    variants_values = values.sort().join('-');
    store = this.grdVariants.getStore();
    found = false;
    
    store.each(function(record, index) {
      var tmp = record.get('variants_values');
      
      if (tmp == variants_values) {
        found = true;
        this.grdVariants.getSelectionModel().selectRow(index);
      }
    } ,this);
    
    if (found == true) {
      Ext.MessageBox.alert(TocLanguage.msgErrTitle, '<?php echo $osC_Language->get('msg_warning_variant_values_exist'); ?>');
      return;
    }
    
    //add record
    var record = Ext.data.Record.create([{name: 'products_variants_id'},
                                         {name: 'variants_values'},
                                         {name: 'variants_groups'},
                                         {name: 'variants_values_name'},
                                         {name: 'data'},
                                         {name: 'default'}]);

    var data = {
      variants_quantity: 0,
      variants_net_price: 0,
      variants_sku: '',
      variants_model: '',
      variants_weight: 0,
      variants_status: 0,
      variants_image: null,
      variants_download_file: null,
      variants_download_filename: null
    };
    
    store.add(new record({
      products_variants_id: -1, 
      variants_values: variants_values,
      variants_groups: groups, 
      variants_values_name: names.join('; '),
      data: data, 
      'default': ((store.getCount() > 0) ? 0 : 1)
    }));
    
    this.pnlVariantDataContainer.add(this.buildVariantDataCard(variants_values, data));
    this.grdVariants.getSelectionModel().selectLastRow();
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
    }
  },
  
  onDelete: function(record) {
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function(btn) {
        if ( btn == 'yes' ) {
          this.grdVariants.getStore().remove(record);
          var cardID = record.get('variants_values');
          
          if (this.pnlVariantDataContainer.findById(cardID)) {
            this.pnlVariantDataContainer.remove(cardID);
          }

          this.grdVariants.getSelectionModel().selectFirstRow();
          this.pnlVariantDataContainer.doLayout(); 
          this.dlgProducts.doLayout(); 
        }
      }, this);
  },
  
  deletePnlVariants: function() {
    this.pnlVariantGroups.items.each(function(item) {
      var el = item.el.up('.x-form-item');
      
      if (el) {
        this.pnlVariantGroups.remove(item, true);
        el.remove();
      }
    }, this);
    
    this.pnlVariantGroups.removeAll();
  },
  
  deleteVariants: function() {
    this.deletePnlVariants();
    this.grdVariants.getStore().removeAll();
    this.pnlVariantDataContainer.removeAll();
  },
  
  getVariants: function() {
    var data = [];

    this.grdVariants.getStore().each(function(record) {
      var is_default = 0;
      if (record.get('default')) {
        is_default = 1;
      }
      data.push(record.get('variants_values') + ':' + record.get('products_variants_id') + ':' + is_default);
    });
    
    return data.join(';');
  },
  
  checkStatus: function() {
    var selected = false;
    var store = this.grdVariants.getStore();
    
    if (this.disabled == true) {
      selected = true;
    } else {
      if (store.getCount() > 0) {
        for (var i =0; i < store.getCount(); i++) {
          if (store.getAt(i).get('default') == '1') {
            selected = true;
            break;
          }
        }
      } else {
        selected = true;
      }
    }
    
    return selected;  
  }
});