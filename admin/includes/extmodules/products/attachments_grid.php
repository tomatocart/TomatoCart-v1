<?php
/*
  $Id: attachments_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.products.AttachmentsGrid = function(config) {
  config = config || {};
  
  config.border = false;
  config.viewConfig = {
    emptyText: TocLanguage.gridNoRecords
  };
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'products',
      action: 'list_product_attachments'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'attachments_id'
    },  [
      'attachments_id',
      'attachments_name',
      'attachments_filename',
      'attachments_cache_filename',
      'attachments_description'
    ]),
    autoLoad: true
  }); 
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.plugins = config.rowActions;
  
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'attachments_name', header: '<?php echo $osC_Language->get('table_heading_attachments_name'); ?>', dataIndex: 'attachments_name'},
    {header: '<?php echo $osC_Language->get('table_heading_attachments_file'); ?>', dataIndex: 'attachments_filename', width: 250},
    {header: '<?php echo $osC_Language->get('table_heading_attachments_description'); ?>', dataIndex: 'attachments_description', width: 250},
    config.rowActions 
  ]);
  config.autoExpandColumn = 'attachments_name';
  config.rowActions.on('action', this.onRowAction, this);
  
  config.txtSearch = new Ext.form.TextField({
    emptyText: '<?php echo $osC_Language->get('empty_attachements_name'); ?>'
  });
  
  config.tbar = [{
    text: TocLanguage.btnAdd,
    iconCls: 'add',
    handler: function() {
      this.onAdd();
    },
      scope: this
  },'-', {
    text: TocLanguage.btnDelete,
    iconCls: 'remove',
    handler: this.onBatchDelete,
    scope: this
  }, '-', { 
    text: TocLanguage.btnRefresh,
    iconCls: 'refresh',
    handler: this.onRefresh,
    scope: this
  }, '->', config.txtSearch, ' ',
  {
    iconCls : 'search',
    handler : this.onSearch,
    scope : this
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
      }
    ],
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
  
  Toc.products.AttachmentsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.AttachmentsGrid, Ext.grid.GridPanel, {
  onAdd: function(productsId, inTabAttachments) {
    var dlg = this.owner.createAttachmentsDialog();
      
    dlg.on('saveSuccess', function(){
     this.onRefresh();
    }, this);
    
    dlg.setTitle('<?php echo $osC_Language->get('heading_title_new_attachment'); ?>');
    dlg.show();
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createAttachmentsDialog();
    dlg.setTitle(record.get('attachments_name'));

    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show(record.get('attachments_id'));
  },
  
  onDelete: function(record) {
    var attachmentsId = record.get('attachments_id');
    var attachmentsName = record.get('attachments_cache_filename');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if ( btn == 'yes' ) {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'products',
              action: 'delete_attachment',
              attachments_id: attachmentsId,
              attachments_name: attachmentsName
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
    var selection = this.getSelectionModel().selections,
    keys = selection.keys,
    result = [];
      
    Ext.each(keys, function(key, index) {
      result = result.concat(key + ':' + selection.map[key].get('attachments_cache_filename'));
    });
  
    if (result.length > 0) {    
      var batch = result.join(',');
    
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'products',
                action: 'delete_attachments',
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
  
  onSearch: function() {
    var attachments_name = this.txtSearch.getValue();
    var store = this.getStore(); 
    
    store.baseParams['attachments_name'] = attachments_name;
    store.reload();
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
  }
});
