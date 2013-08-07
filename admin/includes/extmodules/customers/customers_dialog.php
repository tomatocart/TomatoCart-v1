<?php
/*
  $Id: customers_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.customers.CustomersDialog = function(config) {
  config = config || {};
  
  config.id = 'customers-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_customer'); ?>';
  config.modal = true;
  config.width = 500;
  config.iconCls = 'icon-customers-win';
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
  
  Toc.customers.CustomersDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.customers.CustomersDialog, Ext.Window, {
  
  show: function (id) {
    var customersId = id || null;
    
    this.frmCustomers.form.reset();
    this.frmCustomers.form.baseParams['customers_id'] = customersId;
    
    if (customersId > 0) {
    
      this.frmCustomers.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'customers',
          action: 'load_customer'
        },
        success: function(form, action) {
          Toc.customers.CustomersDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.customers.CustomersDialog.superclass.show.call(this);
    }
  },
      
  buildForm: function() {
    dsCustomersGroups = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'customers', 
        action: 'get_customers_groups'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['id', 'text']
      }),
      autoLoad: true                                                                                   
    });
    
    this.cboCustomersGroups = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_customer_group'); ?>', 
      store: dsCustomersGroups, 
      displayField: 'text', 
      valueField: 'id', 
      name: 'customers_groups',
      hiddenName: 'customers_groups_id', 
      readOnly: true, 
      forceSelection: true,
      mode: 'local',
      emptyText: '<?php echo $osC_Language->get('none'); ?>',
      triggerAction: 'all'
    });
    
    this.frmCustomers = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'customers',
        action: 'save_customer'
      }, 
      defaults: {
          anchor: '98%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 145,
      items: [
        {
          layout: 'column',
          border: false,
          items: [
            { 
              width: 220,
              layout: 'form',
              labelSeparator: ' ',
              border: false,
              items:[
                {fieldLabel: '<?php echo $osC_Language->get('field_gender'); ?>', boxLabel: '<?php echo $osC_Language->get('gender_male'); ?>' , name: 'customers_gender', xtype:'radio', inputValue: 'm', checked: true}
              ]
            },
            { 
              layout: 'form',
              border: false,
              items:[
                { hideLabel: true, boxLabel: '<?php echo $osC_Language->get('gender_female'); ?>' , name: 'customers_gender', xtype:'radio', inputValue: 'f'}
              ]
            }
          ]  
        }, 
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_first_name'); ?>', name: 'customers_firstname', allowBlank: false},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_last_name'); ?>', name: 'customers_lastname', allowBlank: false},
        {xtype: 'datefield', fieldLabel: '<?php echo $osC_Language->get('field_date_of_birth'); ?>', editable: false, name: 'customers_dob', format: 'Y-m-d'},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_email_address'); ?>', name: 'customers_email_address', allowBlank: false},
        {xtype: 'checkbox', anchor: '', fieldLabel: '<?php echo $osC_Language->get('field_newsletter_subscription'); ?>', name: 'customers_newsletter'},
        {xtype: 'textfield', inputType: 'password', fieldLabel: '<?php echo $osC_Language->get('field_password'); ?>', name: 'customers_password'},
        {xtype: 'textfield', inputType: 'password', fieldLabel: '<?php echo $osC_Language->get('field_password_confirmation'); ?>', name: 'confirm_password'},
        {xtype: 'checkbox',anchor: '', fieldLabel: '<?php echo $osC_Language->get('field_status'); ?>', name: 'customers_status'},
        this.cboCustomersGroups
      ]
    });
    
    return this.frmCustomers;
  },

  submitForm : function() {
    this.frmCustomers.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
         this.fireEvent('saveSuccess', action.result.feedback);
         this.close();  
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });   
  }
});