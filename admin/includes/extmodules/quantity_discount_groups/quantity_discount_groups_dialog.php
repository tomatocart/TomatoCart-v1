<?php
/*
  $Id: quantity_discount_groups_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.quantity_discount_groups.QuantityDiscountGroupsDialog = function(config) {

  config = config || {};
  
  config.id = 'quantity_discount_groups-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_quantity_discount_group'); ?>';
  config.width = 400;
  config.modal = true;
  config.iconCls = 'icon-quantity_discount_groups-win';
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
  
  Toc.quantity_discount_groups.QuantityDiscountGroupsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.quantity_discount_groups.QuantityDiscountGroupsDialog, Ext.Window, {
  
  show: function(id) {
    var quantityDiscountGroupsId = id || null;
    
    this.frmQuantityDiscountGroup.form.reset();  
    this.frmQuantityDiscountGroup.form.baseParams['quantity_discount_groups_id'] = quantityDiscountGroupsId;
    
    if (quantityDiscountGroupsId > 0) {
      this.frmQuantityDiscountGroup.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'quantity_discount_groups',
          action: 'load_quantity_discount_group'
        },
        success: function() {
          Toc.quantity_discount_groups.QuantityDiscountGroupsDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        }, 
        scope: this       
      });
    } else {   
      Toc.quantity_discount_groups.QuantityDiscountGroupsDialog.superclass.show.call(this);
    }
  },
      
  buildForm: function() {
    this.frmQuantityDiscountGroup = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'quantity_discount_groups',
        action: 'save_quantity_discount_group'
      }, 
      border: false,
      layout: 'form',
      labelAlign: 'top',
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },    
      items: [                           
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_quantity_discount_group_name'); ?>', name: 'quantity_discount_groups_name', allowBlank: false, anchor: '98%', labelSeparator: ''}
      ]
    });
    
    return this.frmQuantityDiscountGroup;
  },

  submitForm : function() {
    this.frmQuantityDiscountGroup.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
         this.fireEvent('saveSuccess', action.result.feedback);
         this.close();  
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      }, scope: this
    });   
  }
});