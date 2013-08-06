<?php
/*
  $Id: orders_delete_confirm_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.orders.OrdersDeleteComfirmDialog = function(config) {
  
  config = config || {};
  
  config.id = 'orders-delete-confirm-dialog-win';
  config.width = 450;
  config.modal = true;
  config.iconCls = 'icon-orders-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnDelete,
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

  this.addEvents({'deleteSuccess': true});  
      
  Toc.orders.OrdersDeleteComfirmDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.orders.OrdersDeleteComfirmDialog, Ext.Window, {

  show: function (action, ordersId, orders) {
    this.frmConfirm.baseParams['action'] = action;
    
    if (action == 'delete_order') {
      this.frmConfirm.baseParams['orders_id'] = ordersId; 
      this.setTitle('<?php echo $osC_Language->get('introduction_delete_order'); ?>');

      html = '<p class="form-info"><?php echo $osC_Language->get('introduction_delete_order'); ?></p><p class="form-info"><b>#' + orders + '</b></p>';
      this.pnlConfirmInfo.body.update(html);
    } else {
      this.frmConfirm.baseParams['batch'] = ordersId;
      this.setTitle('<?php echo $osC_Language->get('introduction_batch_delete_orders'); ?>');
       
      html = '<p class="form-info"><?php echo $osC_Language->get('introduction_batch_delete_orders'); ?></p><p class="form-info"><b>' + orders + '</b></p>';
      this.pnlConfirmInfo.body.update(html);
    }
    
    this.doLayout();
    
    Toc.orders.OrdersDeleteComfirmDialog.superclass.show.call(this);
  },
  
  buildForm: function() {
    this.pnlConfirmInfo = new Ext.Panel({border: false});
    this.pnlRestockCheckbox = new Ext.Panel({
      border: false,
      html: '<p class="form-info"><?php echo osc_draw_checkbox_field('restock', array(array('id' => '', 'text' => $osC_Language->get('field_restock_product_quantity')))); ?></p>'
    });
    
    this.frmConfirm = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'orders'
      }, 
      border:false,
      items: [this.pnlConfirmInfo, this.pnlRestockCheckbox]
    });
    
    return this.frmConfirm;    
  },

  submitForm : function() {
    this.frmConfirm.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action){
        this.fireEvent('deleteSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if(action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });   
  }  
});