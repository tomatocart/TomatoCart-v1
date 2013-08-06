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
  echo 'Ext.namespace("Toc.product_variants");';
 
  include('product_variants_groups_grid.php');
  include('product_variants_groups_dialog.php');
  include('product_variants_entries_grid.php');
  include('product_variants_entries_dialog.php');
  include('product_variants_main_panel.php');
?>

Ext.override(TocDesktop.ProductVariantsWindow, {
  createWindow: function () {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('product_variants-win');
    
    if (!win) {
      pnl = new Toc.product_variants.MainPanel({ owner: this });
      
      win = desktop.createWindow({
        id: 'product_variants-win',
        title: '<?php echo $osC_Language->get("heading_title"); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-product_variants-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
  },
  
  createProductVariantsGroupsDialog: function () {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('product_variants_groups-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.product_variants.ProductVariantsGroupsDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  createProductVariantsEntriesDialog: function () {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('product_variants_entries-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.product_variants.ProductVariantsEntriesDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});