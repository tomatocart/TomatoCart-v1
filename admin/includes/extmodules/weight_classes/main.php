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
  echo 'Ext.namespace("Toc.weight_classes");';
  
  include('weight_classes_grid.php');
  include('weight_classes_dialog.php');
?>

Ext.override(TocDesktop.WeightClassesWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('weight_classes-win');
     
    if(!win){
      grd = new Toc.weight_classes.WeightClassesGrid({owner: this});
      win = desktop.createWindow({
        id: 'weight_classes-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-weight_classes-win',
        layout: 'fit',
        items: grd
      });
    }
    win.show();
  },
    
  createWeightClassesDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('weight_classes-dialog-win');
    
    if(!dlg){
      dlg = desktop.createWindow({}, Toc.weight_classes.WeightClassesDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }
    
    return dlg;
  }
});