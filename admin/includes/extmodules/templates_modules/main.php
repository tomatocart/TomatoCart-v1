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

  echo 'Ext.namespace("Toc.templates_modules");';
  
  include('templates_modules_config_dialog.php');
  include('templates_modules_grid.php');
?>

Ext.override(TocDesktop.TemplatesModulesWindow, {

  createWindow : function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow(this.id);
     
    if(!win){
      var grid = new Toc.templates_modules.TemplatesModulesGrid({owner: this, set: this.params.set});

      if (this.params.set == 'boxes') {
        var title = '<?php echo $osC_Language->get('heading_title_boxes'); ?>';
      } else {
        var title = '<?php echo $osC_Language->get('heading_title_content'); ?>';
      }
      
      win = desktop.createWindow({
        id: this.id,
        title: title,
        width: 800,
        height: 400,
        iconCls: this.iconCls,
        layout: 'fit',
        items: grid
      });
    }
    
    win.show();
  },
  
  createConfigDialog: function(config) {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('templates_modules-dialog-win');
    
    if(!win){
      Ext.applyIf(config, {iconCls: this.iconCls});
      win = desktop.createWindow(config, Toc.templates_modules.TemplatesModulesConfigDialog);
      
      win.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return win;
  }
});
