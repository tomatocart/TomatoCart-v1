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

  echo 'Ext.namespace("Toc.whos_online");';
  
  include('whos_online_grid.php');
?>

Ext.override(TocDesktop.WhosOnlineWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('whos_online-win');
     
    if(!win){
      var grd = new Toc.whos_online.WhosOnlineGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'whos_online-win',
        title: "<?php echo $osC_Language->get('heading_title'); ?>",
        width: 800,
        height: 400,
        iconCls: 'icon-whos_online-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  }
});
