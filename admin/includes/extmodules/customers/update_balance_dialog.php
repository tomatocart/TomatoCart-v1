<?php
/*
  $Id: update_balance_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.UpdateBalanceDialog = function(config) {
  config = config || {};
  
  config.id = 'update_balance-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_update_blance'); ?>';
  config.modal = true;
  config.width = 400;
  config.autoHeight = true;
  config.items = this.buildForm();

  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
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

  this.addEvents({'saveSuccess' : true});  
  
  Toc.customers.UpdateBalanceDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.customers.UpdateBalanceDialog, Ext.Window, {
  
  show: function (customersId) {
    this.customersId = customersId;

    Toc.customers.UpdateBalanceDialog.superclass.show.call(this);
  },
      
  buildForm: function() {
    this.frmCustomers = new Ext.form.FormPanel({ 
      defaults: {
        anchor: '98%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 145,
      items: [
        {xtype: 'textfield', name: 'amount', fieldLabel: '<?php echo $osC_Language->get('field_amount'); ?>'},
        {xtype: 'textarea', name: 'comments', fieldLabel: '<?php echo $osC_Language->get('field_comments'); ?>'},
        {xtype: 'checkbox', name: 'notify', fieldLabel: '<?php echo $osC_Language->get('field_notify_customer_by_email'); ?>'}
      ]
    });
    
    return this.frmCustomers;
  },
  
  submitForm : function() {
    this.frmCustomers.form.submit({
      url: Toc.CONF.CONN_URL,
      params: {
        'module' : 'customers',
        'action' : 'save_blance',
        'customers_id': this.customersId
      },
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.fireEvent('saveSuccess', action.result.customers_credits, action.result.feedback);
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