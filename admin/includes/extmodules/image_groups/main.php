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

  echo 'Ext.namespace("Toc.image_groups");';
  
  include('image_groups_dialog.php');
  include('image_groups_grid.php');
?>

Ext.override(TocDesktop.ImageGroupsWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('image_groups-win');
     
    if (!win) {
      grd = new Toc.image_groups.ImageGroupsGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'image_groups-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-image_groups-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createImageGroupsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('image_groups-dialog-win');
    
    if(!dlg){
      dlg = desktop.createWindow({}, Toc.image_groups.ImageGroupsDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});