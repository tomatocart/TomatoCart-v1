<?php
/*
  $Id: tax_classes_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.tax_classes.TaxClassesGrid = function(config) {

  config = config || {};
  
  config.region = 'center';
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'tax_classes',
      action: 'list_tax_classes'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'tax_class_id'
    },[
      'tax_class_id',
      'tax_class_title',
      'tax_total_rates'
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
    {id: 'tax_class_title', header: '<?php echo $osC_Language->get('table_heading_tax_classes');?>', dataIndex: 'tax_class_title'},
    {header: '<?php echo $osC_Language->get('table_heading_total_rates');?>', dataIndex: 'tax_total_rates', width: 150, align: 'right'},
    config.rowActions
  ]);
  config.selModel = new Ext.grid.RowSelectionModel({singleSelect: false});
  config.autoExpandColumn = 'tax_class_title';
  
  config.listeners = {
    'rowclick' : this.onGrdRowClick
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
      iconCls:'refresh',
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
  
  this.addEvents({'selectchange' : true}); 
  
  Toc.tax_classes.TaxClassesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tax_classes.TaxClassesGrid,Ext.grid.GridPanel, {

  onAdd: function() {
    var dlg = this.owner.createTaxClassesDialog();
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createTaxClassesDialog();
    dlg.setTitle(record.get('tax_class_title'));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get('tax_class_id'));   
  },
  
  onDelete: function(record) {
    var taxClassesId = record.get('tax_class_id');
                  
    Ext.Msg.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function(btn) {
        if(btn == 'yes') {                                                                                                                                                                 
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: { 
              module: 'tax_classes',
              action: 'delete_tax_class',
              tax_class_id: taxClassesId                                        
            },
            callback: function(options, success, response) {
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
      }, this);                                                               
  },
  
  onGrdRowClick: function(grid, rowIndex, e) {
    var record = grid.getStore().getAt(rowIndex);
    this.fireEvent('selectchange', record);
  },
  
  onRefresh: function() {
    this.getStore().load();
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  } 
});