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

  echo 'Ext.namespace("Toc.information");';
  
  include('information_grid.php');
  include('information_dialog.php');
  include('information_general_panel.php');
  include('information_meta_info_panel.php');
?>

Ext.override(TocDesktop.InformationWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('information-win');
     
    if (!win) {
      grd = new Toc.information.InformationGrid({owner: this});

      win = desktop.createWindow({
        id: 'information-win',
        title: '<?php echo $osC_Language->get('heading_information_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-information-win',
        layout: 'fit',
        items: grd
      });
    }
           
    win.show();
  },
  
  createInformationDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('information-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.information.InformationDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
      
    return dlg;
  }
});
