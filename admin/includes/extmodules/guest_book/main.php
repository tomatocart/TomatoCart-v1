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

  echo 'Ext.namespace("Toc.guest_book");';
  
  include('guest_book_dialog.php');
  include('guest_book_grid.php');
?>

Ext.override(TocDesktop.GuestBookWindow, {

  createWindow: function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('guest_book-win');
     
    if (!win) {
      grd = new Toc.guest_book.GuestBookGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'guest_book-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-guest_book-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createGuestBookDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('guest_book-dialog');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.guest_book.GuestBookDialog);
      
      return dlg;
    }
  }
});
