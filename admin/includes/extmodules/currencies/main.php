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

  echo 'Ext.namespace("Toc.currencies");';
  
  include('currencies_dialog.php');
  include('currencies_update_rates_dialog.php');  
  include('currencies_grid.php');
?>

Ext.override(TocDesktop.CurrenciesWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('currencies-win');
     
    if(!win){
      grd = new Toc.currencies.CurrenciesGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'currencies-win',
        title: '<?php echo $osC_Language->get("heading_title"); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-currencies-win',
        layout: 'fit',
        items: grd
      });
    }

    win.show();
  },
  
  createCurrenciesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('currencies-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.currencies.CurrenciesDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  createCurrenciesUpdateRatesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('currencies-update-rates-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.currencies.CurrenciesUpdateRatesDialog);
    }
    
    return dlg;
  }
});
