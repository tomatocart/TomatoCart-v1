<?php
/*
  $Id: backup_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>

Toc.backup.BackupGrid = function (config) {

  config = config || {};
    
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'backup',
      action: 'list_backup'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'file'
    }, [
      'file', 
      'date', 
      'size'
    ]),
    autoLoad: true
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions: [
      {iconCls: 'icon-restore-record', qtip: TocLanguage.tipRestore},
      {iconCls: 'icon-download-record', qtip: TocLanguage.tipDownload}, 
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4: 2
  });
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'db_backup_file', header: '<?php echo $osC_Language->get("table_heading_backups"); ?>', dataIndex: 'file'},
    { header: '<?php echo $osC_Language->get("table_heading_date"); ?>', dataIndex: 'date'},
    { header: '<?php echo $osC_Language->get("table_heading_file_size"); ?>', dataIndex: 'size'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'db_backup_file';
  
  config.tbar = [
    {
      text: TocLanguage.btnBackup,
      iconCls: 'add',
      handler: this.onBackup,
      scope: this
    },
    '-', 
    {
      text: TocLanguage.btnDelete,
      iconCls: 'remove',
      handler: this.onBathDelete,
      scope: this
    },
    '-', 
    {
      text: TocLanguage.tipRestore,
      iconCls: 'icon-restore-record',
      handler: this.onRestoreFromFile,
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
      
  Toc.backup.BackupGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.backup.BackupGrid, Ext.grid.GridPanel, {

  onBackup: function () {
    var dlg = this.owner.createBackupDialog();
    
    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onRestoreFromFile: function () {
    var dlg = this.owner.createRestoreDialog();
    
    dlg.on('saveSuccess', function(){
      this.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  onDelete: function (record) {
    var file = record.get('file');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle,
      TocLanguage.msgDeleteConfirm,
      function (btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'backup',
              action: 'delete_backup',
              file: file
            },
            callback: function (options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({
                  title: TocLanguage.msgSuccessTitle,
                  html: result.feedback
                });
                
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
  },
  
  onBathDelete: function () {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {
      var batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle,
        TocLanguage.msgDeleteConfirm,
        function (btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              waitMsg: TocLanguage.formSubmitWaitMsg,
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'backup',
                action: 'delete_backups',
                batch: batch
              },
              callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({
                    title: TocLanguage.msgSuccessTitle,
                    html: result.feedback
                  });
                  
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
  
  onRefresh: function () {
    this.getStore().reload();
  },
  
  onRestore: function(record) {
    var file = record.get('file');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle,
      '<?php echo $osC_Language->get("introduction_restore_file"); ?><br />' + file,
      function (btn) {
        if (btn == 'yes') {
          Ext.MessageBox.wait(TocLanguage.formSubmitWaitMsg);
          Ext.Ajax.timeout = 600000;
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'backup',
              action: 'restore_backup',
              file: file
            },
            callback: function (options, success, response) {
              result = Ext.decode(response.responseText);

              if (result.success == true) {
                alert(result.feedback);
                window.location = "<?php echo osc_href_link_admin(FILENAME_DEFAULT, 'login&action=logoff'); ?>";
              } else {
                Ext.MessageBox.hide();
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
  
  onDownload: function (record) {
    file = record.get('file');
    params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
    
    url = '<?php echo osc_href_link_admin(FILENAME_JSON); ?>' + '?module=backup&action=download_backup&file=' + file + '&token=' + token;

    window.open(url, "", params); 
  },
  
  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      case 'icon-restore-record':
        this.onRestore(record);
        break;
      case 'icon-download-record':
        this.onDownload(record);
        break;
    }
  }
}
);