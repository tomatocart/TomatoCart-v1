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
 echo 'Ext.namespace("Toc.abandoned_cart");';
 
 include('abandoned_cart_grid.php');
 include('abandoned_cart_send_emails_dialog.php');
?>

Ext.override(TocDesktop.AbandonedCartWindow, {
  
  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('abandoned_cart-win');
     
    if (!win) {
      grd = new Toc.abandoned_cart.AbandonedCartGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'abandoned_cart-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-abandoned_cart-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createSendEmailsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('abandoned-cart-send-emails-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.abandoned_cart.SendEmailsDialog);
      
      dlg.on('sendSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      },this);
    }
    
    return dlg;
  }
});