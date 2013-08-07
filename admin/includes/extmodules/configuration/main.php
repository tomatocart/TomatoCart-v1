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

  echo 'Ext.namespace("Toc.configuration");';
  
  include('configuration_grid.php');
?>

Ext.override(TocDesktop.ConfigurationWindow, {

  createWindow : function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow(this.id);
      
    if(!win){
      var grid = new Toc.configuration.ConfigurationGrid({gID: this.params.gID, owner: this});
      
      win = desktop.createWindow({
        id: this.id,
        title: this.title,
        width: 800,
        height: 450,
        iconCls: 'icon-configuration-win',
        layout: 'fit',
        items: grid
      });
    }
    
    win.show();
  }
});