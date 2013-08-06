<?php
/*
  $Id: attachments_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.products.AttachmentsPanel = function(config) {
  config = config || {};
  
  config.border = false;
  config.title = '<?php echo $osC_Language->get('section_attachments'); ?>';
  config.viewConfig = {
    emptyText: TocLanguage.gridNoRecords
  };
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'products',
      products_id: config.productsId,
      action: 'load_products_attachments'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'attachments_id'
    },  [
      'attachments_id',
      'attachments_name',
      'attachments_file_name',
      'attachments_cache_filename',
      'attachments_description'
    ]),
    autoLoad: true
  }); 
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
      widthIntercept: Ext.isSafari ? 4 : 2
  });
    widthIntercept: Ext.isSafari ? 4 : 2
  
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'attachments_name', header: '<?php echo $osC_Language->get('table_heading_attachments_name'); ?>', dataIndex: 'attachments_name'},
    {header: '<?php echo $osC_Language->get('table_heading_attachments_file'); ?>', dataIndex: 'attachments_file_name', width: 250},
    {header: '<?php echo $osC_Language->get('table_heading_attachments_description'); ?>', dataIndex: 'attachments_description', width: 250},
    config.rowActions 
  ]);
  config.autoExpandColumn = 'attachments_name';
  
  config.tbar = [{
    text: TocLanguage.btnAdd,
    iconCls: 'add',
    handler: this.onAdd,
    scope: this
  }, '-', {
    text: TocLanguage.btnDelete,
    iconCls: 'remove',
    handler: this.onBatchDelete,
    scope: this
  }];
  
  Toc.products.AttachmentsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.AttachmentsPanel, Ext.grid.GridPanel, {
  onAdd: function() {
    var dlg = this.owner.createAttachmentsListDialog(this.productsId);
    
    dlg.on('saveSuccess', function(records) {
      Ext.each(records, function(record) {
        var attachments_id = record.get('attachments_id');
        var attachments_name = record.get('attachments_name');
        var attachments_file_name = record.get('attachments_filename');
        var attachments_description = record.get('attachments_description');
 
          
        var store = this.getStore();
        if (store.find('attachments_id', attachments_id) == -1) {
          var record = Ext.data.Record.create([
            {name: 'attachments_id', type: 'int'},
            {name: 'attachments_name', type: 'string'},
            {name: 'attachments_file_name', type: 'string'},
            {name: 'attachments_description', type: 'string'}
          ]);
          
          var v = new record({attachments_id: attachments_id, attachments_name: attachments_name, attachments_file_name: attachments_file_name, attachments_description: attachments_description});
          store.add(v);
        }
      }, this);
    }, this);

    dlg.show();
  },
  
  getAttachmentsIDs: function() {
    var ids = [];

    this.getStore().each(function(record) {
      ids.push(record.get('attachments_id'));
    });

    return ids;
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
    }
  },
  
  onDelete: function(record) {
    this.getStore().remove(record);
  },
  
  onBatchDelete: function() {
    var attachments = this.getSelectionModel().getSelections();

    if (attachments.length > 0) {
      Ext.each(attachments, function(attachment) {
        this.getStore().remove(attachment);
      }, this);
    }else{
       Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  }
});
