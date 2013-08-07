<?php
/*
  $Id: main.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  echo 'Ext.namespace("Toc.polls");';  
  
  include('polls_answers_dialog.php');
  include('polls_answers_grid.php');
  include('polls_dialog.php');
  include('polls_grid.php');
  include('polls_main_panel.php');  
?>

Ext.override(TocDesktop.PollsWindow, {
  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('polls-win');

    if (!win) {                               
      pnl = new Toc.polls.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'polls-win',
        title: '<?php echo $osC_Language->get('heading_title'); ?>',
        width: 850,
        height: 400,
        iconCls: 'icon-polls-win',
        layout: 'fit',
        items: pnl
      });
    }   
    
    win.show();
  },
  
  createPollsDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('polls-dialog-win');    

    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.polls.PollsDialog);             
      
      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }    
    
    return dlg;
  },
  
  createPollsAnswersDialog : function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('polls-answers-dialog-win');

    if (!dlg) {
      dlg = desktop.createWindow({},Toc.polls.PollsAnswersDialog);

      dlg.on('saveSuccess', function(feedback) {
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  }
});