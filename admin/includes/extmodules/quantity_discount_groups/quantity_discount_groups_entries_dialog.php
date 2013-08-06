<?php
/*
  $Id: quantity_discount_groups_entries_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.quantity_discount_groups.QuantityDiscountEntriesDialog = function(config) {

  config = config || {}; 
  
  config.id = 'quantity_discount_groups_entries-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_quantity_discount_group_entry'); ?>';
  config.width = 500;
  config.iconCls = 'icon-quantity_discount_groups-win';
  config.modal = true;
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope:this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function() {
        this.close();
      },
      scope:this
    }
  ];
  
  this.addEvents({'saveSuccess' : true});  
  
  Toc.quantity_discount_groups.QuantityDiscountEntriesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.quantity_discount_groups.QuantityDiscountEntriesDialog, Ext.Window, { 
 
  show: function (groupsId, valuesId) {
    var quantityDiscountGroupsId = groupsId;
    var quantityDiscountGroupsValuesId = valuesId || null; 
    
    this.frmQuantityDiscountEntry.form.reset();
    this.frmQuantityDiscountEntry.form.baseParams['quantity_discount_groups_id'] = quantityDiscountGroupsId;
    this.frmQuantityDiscountEntry.form.baseParams['quantity_discount_groups_values_id'] = quantityDiscountGroupsValuesId;
    
    if (quantityDiscountGroupsValuesId > 0) {
      this.frmQuantityDiscountEntry.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'quantity_discount_groups',
          action: 'load_quantity_discount_groups_value',
          quantity_discount_groups_values_id: quantityDiscountGroupsValuesId
        },
        success: function(form, action) {
          Toc.quantity_discount_groups.QuantityDiscountEntriesDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData)
        },
        scope: this       
      });
    } else {   
      Toc.quantity_discount_groups.QuantityDiscountEntriesDialog.superclass.show.call(this);
    }
  },
    
  buildForm: function() {
    this.cboCustomerGroups = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_quantity_discount_customer_group'); ?>', 
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'quantity_discount_groups', 
          action: 'get_customer_groups'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          fields: ['id', 'text']
        }),
        autoLoad: true                                                                                 
      }), 
      displayField: 'text', 
      valueField: 'id', 
      name: 'customers_groups_name',
      hiddenName: 'customers_groups_id', 
      triggerAction: 'all', 
      readOnly: true,
      allowBlank: false
    });
    
    this.frmQuantityDiscountEntry = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        'module' : 'quantity_discount_groups',
        'action' : 'save_quantity_discount_groups_value'
      }, 
      border: false,
      layout: 'form',
      labelWidth: 150,
      defaults: {
          anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        this.cboCustomerGroups,
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get('field_quantity_discount_product_quantity'); ?>', name: 'quantity', allowNegative: false, allowDecimals: false},
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get('field_quantity_discount_product_discount'); ?>', name: 'discount', allowNegative: false, allowDecimals: true}
      ]
    });
    
    return this.frmQuantityDiscountEntry;
  },
  
  submitForm : function() {
    this.frmQuantityDiscountEntry.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success:function(form, action) {
        this.fireEvent('saveSuccess', action.result.feedback); 
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