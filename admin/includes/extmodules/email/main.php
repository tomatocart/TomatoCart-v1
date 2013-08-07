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

  echo 'Ext.namespace("Toc.email");';
  
  include('email_main_panel.php');
  include('accounts_tree.php');
  include('message_panel.php');
  include('account_list_dialog.php');
  include('account_dialog.php');
  include('email_composer_dialog.php');
  include('attachments_dialog.php');
  include('file_upload_dialog.php');
  include('message_detail_dialog.php');
?>

Ext.override(TocDesktop.EmailWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('email-win');
     
    if (!win) {
      pnl = new Toc.email.EmailMainPanel({owner: this});

      win = desktop.createWindow({
        id: 'email-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 1000,
        height: 480,
        iconCls: 'icon-email-win',
        layout: 'fit',
        items: pnl
      });
    }
       
    win.show();
  },
  
  createAccountListDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('account_list_dialog-win');
  
    if (!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.email.AccountListDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  createAccountDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('account_dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.email.AccountDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  createFilterDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('filter_dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.email.FiltersDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  createEmailComposerDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('composer_dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.email.EmailComposerDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;    
  },
  
  createAttachmentsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('attachments_dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.email.AttachmentsDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;  
  },

  createUploadDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('upload_dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.email.FileUploadDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;  
  },
  
  createMessageDetailDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('message_detail_dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.email.MessageDetailDialog);
    }
    
    return dlg;  
  }
});