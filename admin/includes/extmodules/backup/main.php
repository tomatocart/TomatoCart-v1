<?php
/*
  $Id: main.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  echo 'Ext.namespace("Toc.backup");';

  include('backup_dialog.php');
  include('restore_dialog.php');
  include('backup_grid.php');
?>

Ext.override(TocDesktop.BackupWindow, {
 
  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('backup-win');
     
    if(!win){
      grd = new Toc.backup.BackupGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'backup-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-backup-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
    
  createBackupDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('backup-dialog-win');
    
    if(!dlg){
      dlg = desktop.createWindow({}, Toc.backup.BackupDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
   createRestoreDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('backup-restore-dialog-win');
    
    if(!dlg){
      dlg = desktop.createWindow({}, Toc.backup.RestoreDialog);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});
