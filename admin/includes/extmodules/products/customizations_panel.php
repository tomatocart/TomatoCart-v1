<?php
/*
  $Id: customizations_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.products.CustomizationsPanel = function(config) {
  config = config || {};
  
  config.border = false;
  config.title = '<?php echo $osC_Language->get('section_customizations'); ?>';
  config.viewConfig = {
    emptyText: TocLanguage.gridNoRecords
  };
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'products',
      products_id: config.productsId,
      action: 'list_customization_fields'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'customization_fields_id'
    },  [
      'customization_fields_id',
      'customization_fields_name',
      'products_id',
      'customization_type',
      'is_required',
      'name_data'
    ]),
    autoLoad: true
  }); 
  
  renderRequired = function(isRequired) {
    if(isRequired == 1) {
      return '<?php echo $osC_Language->get('parameter_yes'); ?>';
    }else {
      return '<?php echo $osC_Language->get('parameter_no'); ?>';
    }
  };
  
  renderType = function(customizationType) {
    if(customizationType == 1) {
      return '<?php echo $osC_Language->get('field_customization_type_text'); ?>';
    }else {
      return '<?php echo $osC_Language->get('field_customization_type_file'); ?>';
    }
  };
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
      widthIntercept: Ext.isSafari ? 4 : 2
  });
  
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'customization_fields_name', header: '<?php echo $osC_Language->get('table_heading_customizations_name'); ?>', dataIndex: 'customization_fields_name'},
    {header: '<?php echo $osC_Language->get('table_heading_customizations_type'); ?>', dataIndex: 'customization_type', renderer: renderType, width: 250},
    {header: '<?php echo $osC_Language->get('table_heading_customizations_is_required'); ?>', dataIndex: 'is_required', renderer: renderRequired, width: 250},
    config.rowActions 
  ]);
  config.autoExpandColumn = 'customization_fields_name';
  
  config.tbar = [{
    text: TocLanguage.btnAdd,
    iconCls: 'add',
    handler: this.onAdd,
    scope: this
  },
  '-', 
  {
    text: TocLanguage.btnDelete,
    iconCls: 'remove',
    handler: this.onBatchDelete,
    scope: this
  }];
  
  Toc.products.CustomizationsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.CustomizationsPanel, Ext.grid.GridPanel, {
  onChange: function(row, type, required, name, lanData) {
    var store = this.getStore();
    required = (required == true) ? 1 : 0;
    
    if (row == -1) {
      var record = Ext.data.Record.create([
        {name: 'customization_fields_id', type: 'int'},
        {name: 'customization_fields_name', type: 'string'},
        {name: 'products_id', type: 'string'},
        {name: 'customization_type', type: 'int'},
        {name: 'is_required', type: 'int'},
        {name: 'name_data'}
      ]);
      
      var v = new record({
        customization_fields_id: -1, 
        customization_fields_name: name, 
        products_id: this.productsId, 
        customization_type: type, 
        is_required: required, 
        name_data: Ext.encode(lanData)
      });
      
      store.add(v);
    } else {
      var v = store.getAt(row);

      v.set('customization_fields_name', name);
      v.set('products_id', this.productsId);
      v.set('customization_type', type);
      v.set('is_required', required);
      v.set('name_data', Ext.encode(lanData));
      
      store.commitChanges();
    }
  },
  
  onAdd: function() {
    var dlg = this.owner.createCustomizationsDialog({owner: this});
    
    dlg.show();
  },
  
  onEdit: function(row, record) {
    var dlg = this.owner.createCustomizationsDialog({owner: this});
    dlg.setTitle(record.get('customization_fields_name'));

    dlg.show(row, record);
  },
  
  getCustomizations: function() {
    var data = [];

    this.getStore().each(function(record) {
      data.push(record.get('customization_fields_id') + '::' + record.get('customization_type') + '::' + record.get('is_required') + '::' + record.get('name_data'));
    });

    return data.join(';;');
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-edit-record':
        this.onEdit(row, record);
        break;
      case 'icon-delete-record':
        this.onDelete(record);
        break;
    }
  },
  
  onDelete: function(record) {
    this.getStore().remove(record);
  },
  
  onBatchDelete: function() {
    var customizations = this.getSelectionModel().getSelections();

    if (customizations.length > 0) {
      Ext.each(customizations, function(customization) {
        this.getStore().remove(customization);
      }, this);
    }else{
       Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  }
});
