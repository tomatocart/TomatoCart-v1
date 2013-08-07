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

  echo 'Ext.namespace("Toc.purchased_downloadables");';
  
  include('purchased_downloadables_grid.php');
?>

Ext.override(TocDesktop.PurchasedDownloadablesWindow, {

  createWindow : function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('purchased_downloadables-win');
     
    if (!win) {
      grd = new Toc.purchased_downloadables.PurchasedDownloadablesGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'purchased_downloadables-win',
        title: '<?php echo $osC_Language->get('heading_purchased_downloadables_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-purchased_downloadables-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createPurchasedDownloadablesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('purchased_downloadables-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.purchased_downloadables.PurchasedDownloadablesDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }

});
