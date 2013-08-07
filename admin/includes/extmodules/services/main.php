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

  echo 'Ext.namespace("Toc.services");';
  
  include('service_config_dialog.php');
  include('services_grid.php');
?>

Ext.override(TocDesktop.ServicesWindow, {

  createWindow : function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('services-win');
     
    if(!win){
      var grid = new Toc.services.ServicesGrid({owner: this});

      win = desktop.createWindow({
        id: 'services-win',
        title:'<?php echo $osC_Language->get('heading_title'); ?>',
        width:800,
        height:400,
        iconCls: 'icon-services-win',
        layout: 'fit',
        items: grid
      });
    }
    
    win.show();
  },
  
  createConfigurationDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('services-dialog-win');
    
    if(!dlg){
      dlg = desktop.createWindow(config, Toc.services.ServiceConfigDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});
