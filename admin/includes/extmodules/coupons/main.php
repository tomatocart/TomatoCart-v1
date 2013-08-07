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

  echo 'Ext.namespace("Toc.coupons");';
  
  include('products_dialog.php');
  include('categories_dialog.php');
  include('coupons_grid.php');
  include('coupons_dialog.php');
  include('coupons_send_emails_dialog.php');
  include('coupons_redeem_history_dialog.php');      
?>

Ext.override(TocDesktop.CouponsWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('coupons-win');
     
    if (!win) {
      var grd = new Toc.coupons.CouponsGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'coupons-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-coupons-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createCouponsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('coupons-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.coupons.CouponsDialog);
     
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  createCategoriesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('coupons-categories-dialog-win');
     
    if (!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.coupons.CategoriesDialog);
    }
    
    return dlg;    
  },
  
  createProductsDialog: function() {
   
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('coupons-products-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({owner: this}, Toc.coupons.ProductsDialog);
    }
    
    return dlg;    
  },
  
  createSendEmailsDialog: function(title) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('coupons-send-emails-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({title:title,owner: this}, Toc.coupons.SendEmailsDialog);
      
      dlg.on('sendSuccess', function(feedback){
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      },this);
    }
    
    return dlg;
  },
  
  createRedeemHistoryDialog: function() {
    var desktop = this.app.getDesktop();
    dlg = desktop.getWindow('coupons-redeem-history-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.coupons.RedeemHistoryDialog);   
    }
    
    return dlg;
  }
});