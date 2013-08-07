<?php
/*
  $Id: coupons_send_emails_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.coupons.SendEmailsDialog = function(config) {

  config = config || null;
  
  config.id = 'coupons-send-emails-dialog-win';
  config.width = 600;
  config.height = 300;
  config.layout = 'fit';
  config.modal = true;
  config.iconCls = 'icon-coupons-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: '<?php echo $osC_Language->get('button_send'); ?>',
      handler: this.onSendEmail,
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
  
  Toc.coupons.SendEmailsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.coupons.SendEmailsDialog, Ext.Window, {
  show: function(couponsId, couponsName) {
    this.couponsId = couponsId || null;
    this.couponsName = couponsName || null;
    
    this.stxCouponName.setValue(couponsName);
    
    Toc.coupons.SendEmailsDialog.superclass.show.call(this);
  },
  
  
  buildForm: function() { 
    var dsCustomers = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'coupons',
        action: 'get_customers'        
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'coupons_id'
      }, [
          'id',
          'text'   
      ]),
      autoLoad: true
    });      
    
    this.cboCustomers = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_customer'); ?>',
      store: dsCustomers,
      displayField: 'text',
      valueField: 'id',
      name: 'customers',
      hiddenName: 'customers_id',
      readOnly: true,
      forceSelection: true,
      mode: 'remote',
      emptyText: '<?php echo $osC_Language->get('none'); ?>',
      triggerAction: 'all'
    });   
      
    this.pnlSendEmails = new Ext.Panel({
      border: false,
      layout: 'form',
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ' '
      },
      items: [
        this.stxCouponName = new Ext.ux.form.StaticTextField({name: 'coupons_name', fieldLabel: '<?php echo $osC_Language->get('field_coupons_name');?>'}),
        this.cboCustomers,
        this.txtMessage = new Ext.form.TextArea({fieldLabel: '<?php echo $osC_Language->get('field_message'); ?>', height: 150})
      ]
    });
    
    return this.pnlSendEmails;
  },
    
  onSendEmail: function() {
    var customersId = this.cboCustomers.getValue();
    var message = this.txtMessage.getValue();
    
    if ( Ext.isEmpty(customersId) ) {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
      return;
    }
  
    this.pnlSendEmails.el.mask('<?php echo $osC_Language->get('sending_please_wait') ?>', 'x-mask-loading');
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'coupons',
        action: 'send_email',
        coupons_id: this.couponsId,
        customers_id: customersId,
        message: message
      },
      callback: function(options, success, response) {
        result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.fireEvent('sendSuccess', result.feedback);
          this.close();        
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
        
        this.pnlSendEmails.el.unmask();
      },
      scope: this
    }); 
  }
});