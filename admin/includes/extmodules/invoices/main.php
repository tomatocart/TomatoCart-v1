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

  echo 'Ext.namespace("Toc.invoices");';

  include('returns_grid.php');
  include('refunds_grid.php');
  include('invoices_credit_slips_dialog.php');
  include('invoices_store_credits_dialog.php');
  include('invoices_products_grid.php');
  include('invoices_transaction_grid.php');
  include('invoices_status_panel.php');
  include('invoices_dialog.php');
  include('invoices_grid.php');
?>

Ext.override(TocDesktop.InvoicesWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('invoices-win');
     
    if (!win) {
      grd = new Toc.invoices.InvoicesGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'invoices-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 850,
        height: 400,
        iconCls: 'icon-invoices-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createInvoicesDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('invoices-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(config, Toc.invoices.InvoicesDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  createInvoicesCreditSlipsDialog: function(record) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('invoices_credit_slips-dialog-win');
    
    if(!dlg) {
      dlg = desktop.createWindow({owner: this, record: record}, Toc.invoices.InvoicesCreditSlipsDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  },

  createInvoicesStoreCreditsDialog: function(record) {
    var desktop =  this.app.getDesktop();
    var dlg = desktop.getWindow('invoices_store_credits-dialog-win');
    
    if(!dlg) {
      dlg = desktop.createWindow({owner: this, record: record}, Toc.invoices.InvoicesStoreCreditsDialog);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }
});