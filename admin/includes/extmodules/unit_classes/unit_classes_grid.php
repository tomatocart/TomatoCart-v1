 <?php
/*
  $Id: unit_classes_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.unit_classes.UnitClassesGrid = function(config) {
  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'unit_classes',
      action: 'list_unit_classes'        
      },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id:'unit_class_id'
      }, 
      [ 
        'unit_class_id',
        'unit_class_title',
        'language_id'
      ]),
      autoLoad: true
  });  
  
  config.rowActions = new Ext.ux.grid.RowActions({
    header: '<?php echo $osC_Language->get('table_heading_action'); ?>',
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipedit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  
  config.rowActions.on('action', this.onRowAction, this);    
  
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { 
      id: 'unit_class_title',
      header: '<?php echo $osC_Language->get('table_heading_unit_name'); ?>',
      dataIndex: 'unit_class_title'
    },
    config.rowActions
  ]);
  config.autoExpandColumn = 'unit_class_title';
  config.tbar = [ 
    {
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
    },
    '-',
   	{ 
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }];
 
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
      }],
    beforePageText : TocLanguage.beforePageText,
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
       
  Toc.unit_classes.UnitClassesGrid.superclass.constructor.call(this, config);
};
 
Ext.extend(Toc.unit_classes.UnitClassesGrid, Ext.grid.GridPanel, {
   		 
  onAdd: function() {
    var dlg = this.owner.createUnitClassesDialog('<?php echo $osC_Language->get("action_heading_new_quantity_unit_class"); ?>');
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onEdit: function (record) {
    var dlg = this.owner.createUnitClassesDialog(record.get('unit_class_title'));
    
    dlg.on('saveSuccess', function() {
    this.onRefresh();
    }, this);
    
    dlg.show(record.get('unit_class_id'));
  },
  
  onDelete: function(record) {
    var unit_class_id = record.get('unit_class_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if ( btn == 'yes' ) {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'unit_classes',
              action: 'delete_unit_class',
              unit_class_id: unit_class_id
            },
            callback: function(options, success, response) {
              result = Ext.decode(response.responseText);
              
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
      }, 
        this
    );
  },
  
  onBatchDelete: function() {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {    
      var batch = keys.join(',');

      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'unit_classes',
                action: 'delete_unit_classes',
                batch: batch
              },
              callback: function(options, success, response){
                result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.onRefresh();
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
   
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction:function(grid, record, action, row, col) {
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