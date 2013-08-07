<?php
/*
  $Id: variants_groups_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products.VariantsGroupsDialog = function(config) {
  config = config || {};
  
  config.width = 450;
  config.height = 300;
  config.layout = 'fit';
  config.modal = true;
  config.id = 'variants_group-dialog-win';
  config.iconCls = 'icon-product_variants-win';
  config.title = '<?php echo $osC_Language->get('dialog_variants_groups_heading_title'); ?>';
  config.items = this.buildGrid();

  config.buttons = [{
    text: TocLanguage.btnAdd,
    handler: function() {
      this.submitForm();
    },
    scope: this
  }, {
    text: TocLanguage.btnClose,
    handler: function() { 
      this.close();
    },
    scope: this
  }];
  
  this.addEvents({'groupChange' :true});
  
  Toc.products.VariantsGroupsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.products.VariantsGroupsDialog, Ext.Window, {
  buildGrid: function() {
    var dsVariantGroups = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'load_variants_groups'     
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'groups_id'
      },  [
        'groups_id',
        'groups_name'
      ]),
      autoLoad: true,
      listeners: {
        load: this.onDsVariantGroupsLoad,
        scope: this
      }
    });

    var sm = new Ext.grid.CheckboxSelectionModel();
    this.grdProductsVariants = new Ext.grid.GridPanel({
      viewConfig: {
        emptyText: TocLanguage.gridNoRecords
      },
      border: false,
      ds: dsVariantGroups,
      sm: sm,
      cm: new Ext.grid.ColumnModel([
        sm,
        {id: 'products_variants_groups_name', header: '<?php echo $osC_Language->get('table_heading_attachments_name'); ?>', dataIndex: 'groups_name'}
      ]),
      autoExpandColumn: 'products_variants_groups_name',
      tbar: [{
        text: TocLanguage.btnRefresh,
        iconCls: 'refresh',
        handler: this.onRefresh,
        scope: this
      }]
    });    
    
    return this.grdProductsVariants;
  },
  
  onDsVariantGroupsLoad: function() {
    var rows = [];
    
    Ext.each(this.group_ids, function(id){
      var row =  this.grdProductsVariants.store.findExact('groups_id', id);
      rows.push(row);
    }, this);
    
    this.grdProductsVariants.selModel.selectRows(rows);
  },
  
  onRefresh: function() {
    this.grdProductsVariants.getSotre().reload();
  },
  
  submitForm: function() {
    var groups = [];
    var records = this.grdProductsVariants.getSelectionModel().getSelections();
    
    Ext.each(records, function(record) {
      var group = {id: record.get('groups_id'), name: record.get('groups_name')};
      groups.push(group); 
    });
    
    if (groups.length > 0) {
      this.fireEvent('groupChange', groups);
    }

    this.close();
  }
});