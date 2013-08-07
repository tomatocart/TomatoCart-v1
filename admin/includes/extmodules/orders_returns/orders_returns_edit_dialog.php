<?php
/*
  $Id: orders_returns_edit_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.orders_returns.OrdersReturnsEditDialog = function(config) {
  config = config || {};
  
  config.id = 'orders_returns_edit-dialog-win';

  config.layout = 'fit';
  config.width = 500;
  config.modal = true;
  config.autoHeight = true;
  config.iconCls = 'icon-orders_returns-win';
  config.items = this.buildForm();
    
  config.buttons = [
    {
      text:TocLanguage.btnSave,
      handler: function(){
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnClose,
      handler: function(){
        this.close();
      },
      scope:this
    }
  ]; 
  
  this.addEvents({'saveSuccess' : true});
  
  Toc.orders_returns.OrdersReturnsEditDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.orders_returns.OrdersReturnsEditDialog, Ext.Window, {
	
	show: function(record) {
	  this.record = record;

    this.frmOrdersReturnsEdit.form.reset();
    this.frmOrdersReturnsEdit.form.baseParams['orders_returns_id'] = record.get('orders_returns_id');

    this.stxProducts.setRawValue(record.get('products'));
    this.stxCustomer.setValue(record.get('orders_returns_customer'));
    this.stxDateAdded.setValue(record.get('date_added'));
    this.stxComments.setValue(record.get('customers_comments'));
    this.txtComment.setValue(record.get('admin_comments'));
    
  	this.dsStatus.on('load', function() {
  	  this.cboStatus.setValue(record.get('status_id'));
  	}, this);

    Toc.orders_returns.OrdersReturnsEditDialog.superclass.show.call(this);
  },

  buildForm: function() {
    this.dsStatus = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'orders_returns',
        action: 'list_return_status'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: [
          'status_id', 
          'status_name'
        ]
      }),
      autoLoad: true
    });

    this.cboStatus = new Ext.form.ComboBox({
      store: this.dsStatus,
      hiddenName: 'orders_returns_status_id',
      valueField: 'status_id',
      displayField: 'status_name',
      editable: false,
      fieldLabel: '<?php echo $osC_Language->get("field_status"); ?>',
      readOnly: true,
      triggerAction: 'all',
      listeners: {
        select: this.onSelected,
        scope: this
      }
    });

    this.frmOrdersReturnsEdit = new Ext.form.FormPanel({
    	url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'orders_returns',
        action: 'save_order_return'
      }, 
    	border: false,
    	autoHeight: true,
      labelWidth: 120,
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        this.stxProducts = new Ext.ux.form.StaticTextField({fieldLabel: '<?php echo $osC_Language->get("field_products"); ?>', name: 'products'}),
        this.stxCustomer = new Ext.ux.form.StaticTextField({fieldLabel: '<?php echo $osC_Language->get("field_customer"); ?>', name: 'orders_returns_customer'}),
        this.stxDateAdded = new Ext.ux.form.StaticTextField({fieldLabel: '<?php echo $osC_Language->get("field_date"); ?>', name: 'date_added'}),
        this.stxComments = new Ext.ux.form.StaticTextField({fieldLabel: '<?php echo $osC_Language->get("field_customer_comment"); ?>', name: 'comments'}),
        this.cboStatus,
        this.txtComment = new Ext.form.TextArea({
          xtype: 'textarea',
          fieldLabel: '<?php echo $osC_Language->get("field_comment"); ?>',
          name: 'admin_comment',
          anchor: '97%'
        })
      ]    	
    });
    
    return this.frmOrdersReturnsEdit;
  },
  
  onSelected: function() {
    if (this.cboStatus.getValue() == <?php echo ORDERS_RETURNS_STATUS_REFUNDED_CREDIT_MEMO; ?>) {
      var dlg = this.owner.createOrdersReturnsCreditSlipDialog();
      dlg.show(this.record);
      
      dlg.on('saveSuccess', function(feedback){
        this.fireEvent('saveSuccess', feedback);
        
        this.close();
      }, this);
    } else if (this.cboStatus.getValue() == <?php echo ORDERS_RETURNS_STATUS_REFUNDED_STORE_CREDIT; ?>) {
      var dlg = this.owner.createOrdersReturnsStoreCreditDialog();
      dlg.show(this.record);

      dlg.on('saveSuccess', function(feedback){
        this.fireEvent('saveSuccess', feedback);
        
        this.close();
      }, this);
    }
  },

  submitForm: function() {
    this.frmOrdersReturnsEdit.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
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