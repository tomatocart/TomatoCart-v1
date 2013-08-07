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

  echo 'Ext.namespace("Toc.faqs");';
  
  include('faqs_dialog.php');
  include('faqs_grid.php');
?>

Ext.override(TocDesktop.FaqsWindow, {

  createWindow : function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('faqs-win');
     
    if (!win) {
      grd = new Toc.faqs.FaqsGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'faqs-win',
        title: '<?php echo $osC_Language->get('heading_faqs_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-faqs-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createFaqsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('faqs-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.faqs.FaqsDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }

    return dlg;
  }

});
