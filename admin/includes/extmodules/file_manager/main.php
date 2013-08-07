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

  echo 'Ext.namespace("Toc.file_manager");';
  
  include('file_edit_dialog.php');
  include('new_directory_dialog.php');
  include('rename_directory_dialog.php');
  include('file_upload_dialog.php');
  include('directory_list_grid.php');
  include('directory_tree_panel.php');
  include('file_manager_main_panel.php');
?>

Ext.override(TocDesktop.FileManagerWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('file_manager-win');
       
    if (!win) {
      var pnl = new Toc.file_manager.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'file_manager-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 980,
        height: 400,
        iconCls: 'icon-file_manager-win',
        layout: 'fit',
        items: pnl
      });
    }
    win.show();
  },
  
  createFileEditDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('file_edit_dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(null, Toc.file_manager.FileEditDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  createFileUploadDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('file_upload_dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(null, Toc.file_manager.FileUploadDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },

  createNewDirectoryDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('new_directory_dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(null, Toc.file_manager.NewDirectoryDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  createRenameDirectoryDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('rename_directory_dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(null, Toc.file_manager.RenameDirectoryDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
  
});