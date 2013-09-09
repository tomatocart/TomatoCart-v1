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

  define('PRODUCTS_TYPE_GENERAL', 1);
  define('PRODUCTS_TYPE_VARIANTS', 2);

  echo 'Ext.namespace("Toc.specials");';
  
  include('specials_grid.php');
  include('specials_dialog.php');
  include('batch_specials_dialog.php');
?>

Ext.override(TocDesktop.SpecialsWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('specials-win');
     
    if(!win){
      var grd = new Toc.specials.SpecialsGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'specials-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 900,
        height: 400,
        iconCls: 'icon-specials-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
    
  createSpecialsDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('specials-dialog-win');
    
    if(!dlg){
      dlg = desktop.createWindow(config, Toc.specials.SpecialsDialog);
    }
    
    return dlg;
  },
  
  createBatchSpecialsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('batch-specials-dialog-win');
    
    if(!dlg){
      dlg = desktop.createWindow({}, Toc.specials.BatchSpecialsDialog);
    }
    
    return dlg;
  
  }
});
