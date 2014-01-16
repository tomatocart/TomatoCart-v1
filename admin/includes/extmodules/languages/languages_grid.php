<?php
/*
  $Id: languages_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.languages.LanguagesGrid = function(config) {

  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'languages',
      action: 'list_languages'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'languages_id'
    },[
      'languages_id',
      'code',
      'total_definitions',
      'languages_name',
      'languages_flag',
      'action'
    ]),
    autoLoad: true
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    tpl: new Ext.XTemplate(
      '<div class="ux-row-action">'
      +'<tpl for="action">'
      +'<div class="ux-row-action-item {class}" qtip="{qtip}"></div>'
      +'</tpl>'
      +'</div>'
    ),
    actions:['','','',''],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'languages', header: '<?php echo $osC_Language->get('table_heading_languages');?>', dataIndex: 'languages_name'},
    {header: '', dataIndex: 'languages_flag', width: 30},
    {header: '<?php echo $osC_Language->get('table_heading_total_definitions');?>', dataIndex: 'total_definitions', width: 130},
    {header: '<?php echo $osC_Language->get('table_heading_code');?>', dataIndex: 'code', width: 130},
    config.rowActions
  ]);
  config.autoExpandColumn = 'languages';
  
  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls: 'add',
      handler: this.onAdd,
      scope: this
    },
    '-',
    {
      text: TocLanguage.btnUpload,
      iconCls: 'icon-upload',
      handler: this.onUpload,
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
        iconCls: 'add',
        handler: function() {
          thisObj.onAdd();
        }
      },
      {
        text: TocLanguage.btnDelete,
        iconCls: 'remove',
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
  
  Toc.languages.LanguagesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.languages.LanguagesGrid, Ext.grid.GridPanel, {
  
  onAdd: function() {
    var dlg = this.owner.createLanguagesAddDialog();
    
    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onUpload: function() {
    var dlg = this.owner.createLanguagesUploadDialog();
    
    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createLanguagesEditDialog();
    dlg.setTitle(record.get('languages_name'));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get('languages_id'));   
  },
  
  onTranslations: function(record) {
    var dlg = this.owner.createTranslationsDialog({
      languagesId: record.get('languages_id'), 
      languagesName: record.get('languages_name'),
      owner: this.owner
    });
    dlg.setTitle(record.get('languages_name'));
    
    dlg.show(); 
  },
  
  onExport: function(record) {
    var dlg = this.owner.createLanguagesExportDialog();
    dlg.setTitle(record.get('languages_name'));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get('languages_id'));   
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onDelete: function(record) {
    var languagesId = record.get('languages_id');
    var code = record.get('code');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteLanguageFiles,
      
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'languages',
              action: 'delete_language',
              languages_id: languagesId,
              code: code,
              delFiles: 1
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                window.location.reload();
              } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });
        }else {
        	Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'languages',
              action: 'delete_language',
              languages_id: languagesId,
              code: code
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                window.location.reload();
              } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });   
        }
      }, this);
  },
  
  onBatchDelete: function() {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {    
      var batch = keys.join(',');

      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteLanguageFiles,
        
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'languages',
                action: 'delete_languages',
                batch: batch,
                delFiles: 1
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  window.location.reload();
                } else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }else {
          	Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'languages',
                action: 'delete_languages',
                batch: batch
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  window.location.reload();
                } else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });
          }
        }, this);
    } else {
        Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-edit-record':
        this.onEdit(record);
        break;
        
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-export-record':
        this.onExport(record);
        break;  
        
      case 'icon-languages':
        this.onTranslations(record);
        break;
    }
  }
});