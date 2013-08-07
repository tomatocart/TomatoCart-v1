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

  echo 'Ext.namespace("Toc.countries");';
  
  include('countries_grid.php');
  include('countries_dialog.php');
  include('countries_zones_dialog.php');
  include('countries_zones_grid.php');
  include('countries_main_panel.php');
?>
  
Ext.override(TocDesktop.CountriesWindow,{
  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('countries-win');
    
    if (!win) {
      pnl = new Toc.countries.MainPanel({owner:this});
      
      win = desktop.createWindow({
        id: 'countries-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-countries-win',
        layout: 'fit',
        items: pnl
      });
    }
  
    win.show();
  },

  createCountriesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('countries-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({},Toc.countries.CountriesDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },

  createZonesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('zones-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.countries.ZonesDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});