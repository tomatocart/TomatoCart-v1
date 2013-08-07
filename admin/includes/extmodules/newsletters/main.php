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

  echo 'Ext.namespace("Toc.newsletters");';
  
  include('newsletters_dialog.php');
  include('send_emails_dialog.php');
  include('send_newsletters_dialog.php');
  include('send_product_notifications_dialog.php');
  include('newsletters_grid.php');
  include('log_dialog.php');
  
?>

Ext.override(TocDesktop.NewslettersWindow, {

  createWindow : function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('newsletters-win');
     
    if (!win) {
      this.grd = new Toc.newsletters.NewslettersGrid({owner: this});

      win = desktop.createWindow({
        id: 'newsletters-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-newsletters-win',
        layout: 'fit',
        items: this.grd
      });
    }
       
    win.show();
  },
  
  createNewslettersDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('newsletters-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.newsletters.NewslettersDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  },
  
  createSendNewslettersDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('send-newsletters-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.newsletters.SendNewslettersDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.grd.getStore().reload();
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  },
  
  createSendEmailsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('send-emails-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.newsletters.SendEmailsDialog);
      dlg.owner = this;
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  },
  
  createSendProductNotificationsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('send-product-notifications-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.newsletters.SendProductNotificationsDialog);
      dlg.owner = this;
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  },
  
  createLogDialog: function(nID) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('log-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.newsletters.LogDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  }
});
