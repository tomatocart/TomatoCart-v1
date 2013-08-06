<?php
/*
  $Id: orders_returns_credit_slip_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.orders_returns.OrdersReturnsCreditSlipDialog = function (config) {
  config = config || {};
  
  config.id = 'return_orders_credit_slip-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_credit_slip"); ?>';
  config.layout = 'fit';
  config.width = 400;
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
  
  Toc.orders_returns.OrdersReturnsCreditSlipDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.orders_returns.OrdersReturnsCreditSlipDialog, Ext.Window, {

  show: function (record) {
    this.frmCreditSlip.form.baseParams['orders_id'] = record.get('orders_id');
    this.frmCreditSlip.form.baseParams['orders_returns_id'] = record.get('orders_returns_id');
    this.frmCreditSlip.form.baseParams['return_quantity'] = record.get('return_quantity');

    this.txtSubTotal.setValue(record.get('total'));
    
    Toc.orders_returns.OrdersReturnsCreditSlipDialog.superclass.show.call(this);
  },
  
  buildForm: function () {
    this.frmCreditSlip = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'orders_returns',
        action: 'create_credit_slip'
      },
      autoHeight: true,
      layoutConfig: {labelSeparator: ''},
      defaults: {anchor: '96%'},
      labelWidth: 180,
      items: [
        {border: false, html: '<p class="form-info"><?php echo $osC_Language->get("field_credit_slip_title"); ?></p>'},
        this.txtSubTotal = new Ext.form.NumberField({xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get("field_sub_total"); ?>', name: 'sub_total', allowNegative: false, allowBlank: false, allowDecimals: true, value: 0}),
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get("field_shipping_fee"); ?>', name: 'shipping_fee', allowNegative: false, allowBlank: false, allowDecimals: true, value: 0},
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get("field_handling"); ?>', name: 'handling', allowNegative: false, allowBlank: false, allowDecimals: true, value: 0},
        {xtype: 'checkbox', fieldLabel: '<?php echo $osC_Language->get("field_restock_product_quantity"); ?>', name: 'restock_quantity', anchor: ''},
        {xtype: 'textarea',fieldLabel: '<?php echo $osC_Language->get("field_comment"); ?>', name: 'admin_comment'}
      ]
    });
    
    return this.frmCreditSlip;
  },
  
  submitForm: function () {
    this.frmCreditSlip.form.submit({
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