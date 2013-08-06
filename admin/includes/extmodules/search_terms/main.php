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

  echo 'Ext.namespace("Toc.search_terms");';
  
  include('search_terms_grid.php');
  include('search_terms_edit_dialog.php');
?>


Ext.override(TocDesktop.SearchTermsWindow, {

  createWindow: function() {
    desktop = this.app.getDesktop();
    win = desktop.getWindow('search_terms-win');
     
    if(!win){
      grd = new Toc.search_terms.SearchTermsGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'search_terms',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-search_terms-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createSearchTermsEditDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('search_terms_edit_dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.search_terms.SearchTermEditDialog);             
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }   
    
    return dlg;
  }
});
