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

  echo 'Ext.namespace("Toc.products_expected");';
  
  include('products_expected_grid.php');
  include('products_expected_dialog.php');
?>

Ext.override(TocDesktop.ProductsExpectedWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('products_expected-win');
     
    if(!win){
      grd = new Toc.products_expected.ProductsExpectedGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'products_expected-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-products_expected-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
    
  createProductsExpectedDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('products_expected-dialog-win');
    
    if(!dlg){
      dlg = desktop.createWindow({}, Toc.products_expected.ProductsExpectedDialog);
    }
    
    return dlg;
  }
});
