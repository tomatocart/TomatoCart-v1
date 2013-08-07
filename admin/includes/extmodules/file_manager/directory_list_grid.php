<?php
/*
  $Id: directory_list_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.file_manager.DirectoryListGrid = function(config) {
  
  config = config || {};
  config.layout = 'fit';
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  config.region = 'center';
  config.split = true;
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'file_manager',
      action: 'list_directory'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'file_name'
    }, 
    [
      'icon',
      'file_name',
      'is_directory',
      'size',
      'permission',
      'file_owner',
      'group_owner',
      'writeable',
      'last_modified_date',
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
    actions:['','',''],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.selModel = new Ext.grid.RowSelectionModel({singleSelect: true});
  
  config.cm = new Ext.grid.ColumnModel([
    {header: '', dataIndex: 'icon', width : 24},
    {id : 'file_name', header: '<?php echo $osC_Language->get('table_heading_files'); ?>', dataIndex: 'file_name'},
    {header: '<?php echo $osC_Language->get('table_heading_size'); ?>', dataIndex: 'size', align: 'center', width: 80},
    {header: '<?php echo $osC_Language->get('table_heading_permissions'); ?>', dataIndex: 'permission', align: 'center', width: 100},
    {header: '<?php echo $osC_Language->get('table_heading_user'); ?>', dataIndex: 'file_owner', align: 'center', width: 80},
    {header: '<?php echo $osC_Language->get('table_heading_group'); ?>', dataIndex: 'group_owner', align: 'center', width: 80},
    {header: '<?php echo $osC_Language->get('table_heading_writable'); ?>', dataIndex: 'writeable', align: 'center', width: 80},
    {header: '<?php echo $osC_Language->get('table_heading_date_last_modified'); ?>', dataIndex: 'last_modified_date', align: 'center', width: 140},
    config.rowActions
  ]);
  config.autoExpandColumn = 'file_name';

  config.tbar = [
    {
      text: TocLanguage.btnUpload,
      iconCls: 'icon-upload',
      handler: this.onUpload,
      scope: this
    },
    '-', 
    {
      text: '<?php echo $osC_Language->get('button_new_file'); ?>',
      iconCls: 'add',
      handler: this.onNewFile,
      scope: this
    },
    '-',
    { 
      text: '<?php echo $osC_Language->get('button_new_directory'); ?>',
      iconCls: 'add',
      handler: this.onNewDirectory,
      scope: this
    },
    '-',
    { 
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    },
     '-',
    { 
      text: TocLanguage.btnDelete,
      iconCls: 'remove',
      handler: this.onBatchDelete,
      scope: this
    }
  ];
  
  config.listeners = {'rowdblclick': this.onGrdDbClick};
  
  Toc.file_manager.DirectoryListGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.file_manager.DirectoryListGrid,Ext.grid.GridPanel, {
  
  changeDirectory: function(directory) {
    if(directory){
      this.getStore().baseParams['directory'] = directory;
    }
    else {
      this.getStore().baseParams['directory'] = this.mainPanel.getDirectoryTreePanel().getCurrentPath();
    }
    
    this.getStore().reload();
  },
  
  onUpload: function() {
    var dlg = this.owner.createFileUploadDialog();
    
    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show(this.mainPanel.getDirectoryTreePanel().getCurrentPath());
  },
    
  onNewFile: function() {
    var dlg = this.owner.createFileEditDialog();
    
    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show(this.mainPanel.getDirectoryTreePanel().getCurrentPath(), null);
  },
  
  onEdit: function(fileName) {
    var dlg = this.owner.createFileEditDialog();

    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show(this.mainPanel.getDirectoryTreePanel().getCurrentPath(), fileName);
  },
  
  onNewDirectory: function() {
    var dlg = this.owner.createNewDirectoryDialog();
    
    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show(this.mainPanel.getDirectoryTreePanel().getCurrentPath());
  },
  
  onDownload: function (record) {
    fileName = record.get('file_name');
    params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
    
    url = '<?php echo osc_href_link_admin(FILENAME_JSON); ?>' + '?module=file_manager&directory=' + this.mainPanel.getDirectoryTreePanel().getCurrentPath() + '&action=download&file_name=' + fileName + '&token=' + token;
    window.open(url, "", params); 
  },
  
  onGrdDbClick: function(grid, row) {
    var record = grid.getStore().getAt(row);
    var isDirectory = record.get('is_directory');
    var fileName = record.get('file_name');
    
    if (isDirectory == true) {
      var directory = this.mainPanel.getDirectoryTreePanel().getCurrentPath() + '/' + fileName;
      this.mainPanel.getDirectoryTreePanel().setCurrentPath(directory);
    } else {
      this.onEdit(fileName);
    }
  },
  
  onDelete: function(record) {
    var fileName = record.get('file_name');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
            module: 'file_manager',
            action: 'delete',
            file_name: fileName,
            directory: this.mainPanel.getDirectoryTreePanel().getCurrentPath()
        },
        callback: function(options, success, response) {
          var result = Ext.decode(response.responseText);
                
          if (result.success == true) {
            this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            this.onRefresh();
            }else{
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
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'file_manager',
                action: 'batch_delete',
                batch: batch,
                directory: this.mainPanel.getDirectoryTreePanel().getCurrentPath()
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                
                  this.onRefresh();
                }else{
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });   
          }
        }, this);
    }else{
       Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRefresh: function() {
    this.mainPanel.getDirectoryTreePanel().reloadCurrentPath();
    
    this.getStore().reload();
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
      this.onDelete(record);
      break;
      
      case 'icon-edit-record':
        this.onEdit(record.get('file_name'));
        break;
        
      case 'icon-download-record':
        this.onDownload(record);
        break;
    }
  }
});