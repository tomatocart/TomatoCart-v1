<?php
/*
  $Id: abandoned_cart_send_emails_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.abandoned_cart.SendEmailsDialog = function(config) {
  
  config = config || {};
  
  config.id = 'abandoned-cart-send-emails-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_abandoned_cart_send_emails'); ?>';
  config.width = 600;
  config.fit = true;
  config.modal = true;
  config.iconCls = 'icon-abandoned_cart-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: '<?php echo $osC_Language->get('button_send'); ?>',
      handler: function(){
        this.submitForm();
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
  
  this.addEvents({'sendSuccess': true});
  
  Toc.abandoned_cart.SendEmailsDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.abandoned_cart.SendEmailsDialog, Ext.Window, {
  show: function(customersId, customersName, cartContents, cartTotal) {
    customersId = customersId || null;
    
    this.frmSendEmails.form.reset();
    this.frmSendEmails.baseParams['customers_id'] = customersId;
    
    this.stxCustomersName.setValue(customersName);
    this.stxCartContents.setValue(cartContents);
    this.stxCartTotal.setValue(cartTotal);
        
    Toc.abandoned_cart.SendEmailsDialog.superclass.show.call(this);
  },
  
  buildForm: function() {
    this.frmSendEmails = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'abandoned_cart',
        action: 'send_email'
      },
      layout: 'form',
      defaults: {
        anchor: '97%'       
      },
      layoutConfig: {
        labelSeparator: ' '
      },
      labelWidth: 150,
      items: [
        this.stxCustomersName = new Ext.ux.form.StaticTextField({fieldLabel: '<?php echo $osC_Language->get('field_customer'); ?>'}),
        this.stxCartContents = new Ext.ux.form.StaticTextField({fieldLabel: '<?php echo $osC_Language->get('field_cart_contents'); ?>'}),
        this.stxCartTotal = new Ext.ux.form.StaticTextField({fieldLabel: '<?php echo $osC_Language->get('field_cart_total'); ?>'}),
        {
          xtype: 'textarea',
          name: 'message',
          fieldLabel: '<?php echo $osC_Language->get('field_comment'); ?>',
          height: 150,
          allowBlank: false
        }
      ]
    }); 
    
    return this.frmSendEmails;
  },
  
  submitForm: function() {
    this.frmSendEmails.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
         this.fireEvent('sendSuccess', action.result.feedback);
         this.close();  
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });   
  }
});