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

  echo 'Ext.namespace("Toc.credit_cards");';
  
  include('credit_cards_dialog.php');
  include('credit_cards_grid.php');
?>

Ext.override(TocDesktop.CreditCardsWindow, {

  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('credit_cards-win');
     
    if (!win) {
      grd = new Toc.credit_cards.CreditCardsGrid({owner: this});
      
      win = desktop.createWindow({
        id: 'credit_cards-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-credit_cards-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  createCreditCardsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('credit_cards-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.credit_cards.CreditCardsDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});