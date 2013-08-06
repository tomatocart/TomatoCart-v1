<?php
/*
  $Id: orders_returns_store_credit_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.orders_returns.OrdersReturnsStoreCreditDialog = function (config) {
  config = config || {};
  
  config.id = 'return_orders_store_credit-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_store_credit"); ?>';
  config.layout = 'fit';
  config.width = 400;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-orders_returns-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function () {
        this.submitForm();
      },
      scope: this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
    
  ];
  this.addEvents({'saveSuccess': true});
  
  Toc.orders_returns.OrdersReturnsStoreCreditDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.orders_returns.OrdersReturnsStoreCreditDialog, Ext.Window, {

  show: function (record) {
    this.frmReturnOrdersStoreCredit.form.baseParams['orders_id'] = record.get('orders_id');
    this.frmReturnOrdersStoreCredit.form.baseParams['orders_returns_id'] = record.get('orders_returns_id');
    this.frmReturnOrdersStoreCredit.form.baseParams['return_quantity'] = record.get('return_quantity');

    this.txtSubTotal.setValue(record.get('total'));
    
    Toc.orders_returns.OrdersReturnsStoreCreditDialog.superclass.show.call(this);
  },
  
  buildForm: function () {
    this.frmReturnOrdersStoreCredit = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'orders_returns',
        action: 'create_store_credit'
      },
      autoHeight: true,
      layoutConfig: {labelSeparator: ''},
      defaults: {anchor: '96%'},
      labelWidth: 180,
      items:[
        {border: false, html: '<p class="form-info"><?php echo $osC_Language->get("field_store_credit_title"); ?></p>'},
        this.txtSubTotal = new Ext.form.NumberField({xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get("field_store_credit"); ?>', name: 'sub_total', allowNegative: false, allowBlank: false, allowDecimals: true, decimalPrecision: 3, value: 0}),
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get("field_shipping_fee"); ?>', name: 'shipping_fee', allowNegative: false, allowBlank: false, allowDecimals: true, value: 0},
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get("field_handling"); ?>', name: 'handling', allowNegative: false, allowBlank: false, allowDecimals: true, value: 0},
        {xtype: 'checkbox', fieldLabel: '<?php echo $osC_Language->get("field_restock_product_quantity"); ?>', name: 'restock_quantity', anchor: ''},
        {xtype: 'textarea',fieldLabel: '<?php echo $osC_Language->get("field_comment"); ?>', name: 'admin_comment'}
      ]
    });
    
    return this.frmReturnOrdersStoreCredit;
  },
  
  submitForm: function () {
    this.frmReturnOrdersStoreCredit.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function (form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });
  }
}
);