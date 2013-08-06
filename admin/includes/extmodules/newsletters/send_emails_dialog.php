<?php
/*
  $Id: send_emails_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.newsletters.SendEmailsDialog = function(config) {

  config = config || {};
  
  config.id = 'send-emails-dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.width = 600;
  config.height = 350;
  config.layout = 'fit';
  config.modal = true;
  config.items = this.buildForm();  
  
  config.buttons = [
    {
      text: '<?php echo $osC_Language->get('button_ok') ?>',
      id: 'btn-send-emails',
      handler: this.onAction,
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
  
  Toc.newsletters.SendEmailsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.newsletters.SendEmailsDialog, Ext.Window, {
  
  show: function (newslettersId) {
    this.newslettersId = newslettersId || null;
    
    Toc.newsletters.SendEmailsDialog.superclass.show.call(this);
  },
  
  onAction: function() {
    text = Ext.getCmp('btn-send-emails').getText();
    
    if (text == '<?php echo $osC_Language->get('button_ok') ?>') {
      this.showConfirmation();
    } else {
      this.sendEmails();
    }
  },
  
  sendEmails: function() {
    var batch = this.selAudience.getValue();
  
    this.pnlSendEmail.el.mask('<?php echo $osC_Language->get('sending_please_wait') ?>', 'x-mask-loading');
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'newsletters',
        action: 'send_emails',
        newsletters_id: this.newslettersId,
        batch: batch
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
         this.fireEvent('sendSuccess', result.feedback);
         this.close();        
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
        
        this.pnlSendEmail.el.unmask();
      },
      scope: this
    }); 
  },
    
  showConfirmation: function() {
    var batch = this.selAudience.getValue();
    
    if ( Ext.isEmpty(batch) ) {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
      return;
    }  
  
    this.pnlSendEmail.el.mask(TocLanguage.formSubmitWaitMsg, 'x-mask-loading');
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'newsletters',
        action: 'get_emails_confirmation',
        newsletters_id: this.newslettersId,
        batch: batch
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.pnlSendEmail.removeAll();
          
          this.pnlSendEmail.body.update(result.confirmation);
          Ext.getCmp('btn-send-emails').setText('<?php echo $osC_Language->get('button_send') ?>');
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
        
        this.pnlSendEmail.el.unmask();
      },
      scope: this
    }); 
  },
  
  getAudienceSelectionForm: function() {
    var selAudience = new Ext.ux.Multiselect({
      name: 'customers',
      style: 'padding: 10px 10px 10px 15px',
      width: 550,
      height: 250,
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'newsletters', 
          action: 'get_emails_audience'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          fields: ['id', 'text']
        }),                                                                        
        autoLoad: true
      }),
      legend: '<?php echo $osC_Language->get('newsletter_customer'); ?>',
      displayField: 'text',
      valueField: 'id',
      isFormField: true
    });
        
    return selAudience;
  },
      
  buildForm: function() {
    this.selAudience = this.getAudienceSelectionForm();
    
    this.pnlSendEmail = new Ext.Panel({
      border: false,
      items: this.selAudience
    });    
    
    return this.pnlSendEmail;
  }
});