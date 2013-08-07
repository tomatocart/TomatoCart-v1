<?php
/*
  $Id: message_panel_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.email.MessageDetailDialog = function(config) {
  config = config || {};
  
  config.id = 'message_detail_dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_message_detail'); ?>';
  config.width = 650;
  config.height = 360;
  config.modal = true;
  config.layout = 'border';
  /*config.autoScroll = true;*/
  
  config.items = [this.pnlMessage = new Toc.email.MessagePanel({'owner': config.owner, 'type': 'dialog', region: 'center'})];
    
  Toc.email.MessageDetailDialog.superclass.constructor.call(this, config);  
}

Ext.extend(Toc.email.MessageDetailDialog, Ext.Window, {
  show: function(){
    Toc.email.MessageDetailDialog.superclass.show.call(this);
  }
});