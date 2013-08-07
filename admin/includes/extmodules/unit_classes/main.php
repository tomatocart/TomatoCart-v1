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
  echo 'Ext.namespace("Toc.unit_classes");';

  include('unit_classes_dialog.php');
  include('unit_classes_grid.php');
?>

Ext.override(TocDesktop.UnitClassesWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('unit_classes-win');
     
    if (!win) {
      var grd = new Toc.unit_classes.UnitClassesGrid({owner: this});
      
      win = desktop.createWindow ({
        id: 'unit_classes-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 600,
        height: 400,
        iconCls: 'icon-unit_classes-win',
        layout: 'fit',
        items: grd
      });
    }
     win.show();
  },
  
  createUnitClassesDialog: function(title) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('unit_classes-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({title:title}, Toc.unit_classes.UnitClassesDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});