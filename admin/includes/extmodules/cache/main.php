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

  echo 'Ext.namespace("Toc.cache");';
  
  include('cache_grid.php');
?>

Ext.override(TocDesktop.CacheWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('cache-win');
     
    if(!win){
      grd = new Toc.cache.CacheGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'cache-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-cache-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  }
});