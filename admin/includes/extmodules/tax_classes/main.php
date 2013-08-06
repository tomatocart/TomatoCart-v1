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
  echo 'Ext.namespace("Toc.tax_classes");';  

  include('tax_classes_dialog.php');
  include('tax_classes_grid.php');
  include('tax_rates_dialog.php');
  include('tax_rates_grid.php');
  include('tax_classes_main_panel.php');
?>

Ext.override(TocDesktop.TaxClassesWindow, {
  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('tax_classes-win');

    if (!win) {                               
      pnl = new Toc.tax_classes.TaxClassesMainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'tax_classes-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-tax_classes-win',
        layout: 'fit',
        items: pnl
      });
    }   
    
    win.show();
  },
  
  createTaxClassesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('tax-class-dialog-win');    

    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.tax_classes.TaxClassesDialog); 
                  
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }    
    
    return dlg;
  },
  
  createTaxRatesDialog : function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('tax-rate-dialog-win');

    if (!dlg) {
       dlg = desktop.createWindow({},Toc.tax_classes.TaxRatesDialog);
       
       dlg.on('saveSuccess', function(feedback) {
         this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
       }, this);
    }
    
    return dlg;
  }
});
