<?php
/*
  $Id: send_newsletters_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.newsletters.SendNewslettersDialog = function(config) {

  config = config || {};
  
  config.id = 'send-newsletters-dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.layout = 'fit';
  config.width = 600;
  config.height = 350;
  config.items = this.buildForm();  
  
  config.buttons = [
    {
      id: 'btn-send-newsletters',
      text: '<?php echo $osC_Language->get('button_send') ?>',
      handler: function() { 
        this.sendEmails();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];

  this.addEvents({'sendSuccess' : true});  
  
  Toc.newsletters.SendNewslettersDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.newsletters.SendNewslettersDialog, Ext.Window, {
  
  show: function (newslettersId) {
    this.newslettersId = newslettersId;
    
    this.frmNewsletter.el.mask(TocLanguage.formSubmitWaitMsg, 'x-mask-loading');
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'newsletters',
        action: 'get_newsletters_confirmation',
        newsletters_id: this.newslettersId
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.frmNewsletter.body.update(result.confirmation);
          
          if (result.execute == true) {
            Ext.getCmp('btn-send-newsletters').setText('<?php echo $osC_Language->get('button_send') ?>');
          } else {
            Ext.getCmp('btn-send-newsletters').hide();
          }
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
        
        this.frmNewsletter.el.unmask();
      },
      scope: this
    }); 
        
    Toc.newsletters.SendNewslettersDialog.superclass.show.call(this);
  },
  
  sendEmails: function() {
    this.frmNewsletter.el.mask('<?php echo $osC_Language->get('sending_please_wait') ?>', 'x-mask-loading');
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'newsletters',
        action: 'send_newsletters',
        newsletters_id: this.newslettersId
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
         this.fireEvent('sendSuccess', result.feedback);
         this.close();        
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
        
        this.frmNewsletter.el.unmask();
      },
      scope: this
    }); 
  },
      
  buildForm: function() {
    this.frmNewsletter = new Ext.Panel();
    
    return this.frmNewsletter;
  }
});