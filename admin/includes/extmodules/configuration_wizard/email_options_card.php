<?php
/*
  $Id: stroe_inforamtion_card.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.configuration_wizard.EmailOptionsCard = function (config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('email_options_header_title'); ?>';
  config.description = '<?php echo $osC_Language->get('email_options_header_description'); ?>';
  config.labelWidth = 150;
  config.monitorValid = true;
  config.frame = true;
  config.defaults = {
    xtype: 'textfield',
    labelStyle: 'font-size: 11px',
    anchor: '97%'
  };
 
  config.cboEmailTansportMethod = new Ext.form.ComboBox({
    fieldLabel: '<?php echo $osC_Language->get('field_email_tansport_method'); ?>',
    name: 'EMAIL_TRANSPORT',
    allowBlank: false,
    triggerAction: 'all',
    editable: false,
    displayField: 'text',
    valueField: 'id',
    mode: 'local',
    store: new Ext.data.SimpleStore ({
      fields: ['id', 'text'],
      data: [['sendmail', 'sendmail'],
             ['smtp', 'smtp']]
    }),
    listeners: {
      select: this.onEamilTransportMethodSelect,
      scope: this
    }
  });
  
  config.cboSendEmails = new Ext.form.ComboBox({
    fieldLabel: '<?php echo $osC_Language->get('field_email_send_emails'); ?>',
    name: 'SEND_EMAILS',
    triggerAction: 'all',
    editable: false,
    allowBlank: false,
    displayField: 'text',
    valueField: 'id',
    mode: 'local',
    store: new Ext.data.SimpleStore({
      fields: ['id', 'text'],
      data: [['0', '<?php echo $osC_Language->get('parameter_false'); ?>'],
             ['1', '<?php echo $osC_Language->get('parameter_true'); ?>']]
    })
  });
  
  config.items = [
    {name: 'STORE_OWNER_EMAIL_ADDRESS', fieldLabel: '<?php echo $osC_Language->get('field_email_adress'); ?>', regex: /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/, allowBlank: false},
    {name: 'EMAIL_FROM', fieldLabel: '<?php echo $osC_Language->get('field_email_from'); ?>', allowBlank: false},
    {name: 'SEND_EXTRA_ORDER_EMAILS_TO', fieldLabel: '<?php echo $osC_Language->get('field_order_email_to'); ?>', allowBlank: true},
    config.cboEmailTansportMethod,
    this.txtSmtpHost = new Ext.form.TextField({name: 'SMTP_HOST', fieldLabel: '<?php echo $osC_Language->get('field_smtp_host'); ?>'}),
    this.txtSmtpPort = new Ext.form.TextField({name: 'SMTP_PORT', fieldLabel: '<?php echo $osC_Language->get('field_smtp_port'); ?>'}),
    this.txtSmtpUsername = new Ext.form.TextField({name: 'SMTP_USERNAME', fieldLabel: '<?php echo $osC_Language->get('field_smtp_username'); ?>'}),
    this.txtSmtpPassword = new Ext.form.TextField({name: 'SMTP_PASSWORD', fieldLabel: '<?php echo $osC_Language->get('field_smtp_password'); ?>', inputType: 'password'}),
    config.cboSendEmails
  ];
  
  Toc.configuration_wizard.EmailOptionsCard.superclass.constructor.call(this, config);
};
 
Ext.extend(Toc.configuration_wizard.EmailOptionsCard, Ext.ux.Wiz.Card,{
  show: function() {
    this.onCardShow();
     
    Toc.configuration_wizard.EmailOptionsCard.superclass.show.call(this);
  },
  
  onEamilTransportMethodSelect: function() {
    if (this.cboEmailTansportMethod.getValue() === 'sendmail') {
      this.txtSmtpHost.allowBlank = true;
      this.txtSmtpPort.allowBlank = true;
      this.txtSmtpUsername.allowBlank = true;
      this.txtSmtpPassword.allowBlank = true;
      
      this.txtSmtpHost.setValue('');
      this.txtSmtpPort.setValue('');
      this.txtSmtpUsername.setValue('');
      this.txtSmtpPassword.setValue('');
      
      this.txtSmtpHost.disable();
      this.txtSmtpPort.disable();
      this.txtSmtpUsername.disable();
      this.txtSmtpPassword.disable();
    } else {
      this.txtSmtpHost.allowBlank = false;
      this.txtSmtpPort.allowBlank = false;
      this.txtSmtpUsername.allowBlank = false;
      this.txtSmtpPassword.allowBlank = false;
        
      this.txtSmtpHost.enable();
      this.txtSmtpPort.enable();
      this.txtSmtpPassword.enable();
      this.txtSmtpUsername.enable();
    }
  }
});
