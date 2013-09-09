<?php
/*
  $Id: currencies_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.currencies.CurrenciesDialog = function(config) {
  
  config = config || {};
  
  config.id = 'currencies-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_currency'); ?>';
  config.width = 450;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-currencies-win';
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
      handler: function() {
        this.close();
      },
      scope:this
    }
  ];

  this.addEvents({'saveSuccess' : true});  
  
  Toc.currencies.CurrenciesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.currencies.CurrenciesDialog, Ext.Window, {
  
  show: function(id) {
    var currenciesId = id || null;
    
    this.frmCurrency.form.reset();
    this.frmCurrency.form.baseParams['currencies_id'] = currenciesId;

    if (currenciesId > 0) {
      this.frmCurrency.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_currency',
          currencies_id: currenciesId
        },
        success: function(form, action) {
          if(action.result.data.is_default == '1') {
            Ext.getCmp('is_default').disable();
          }
          
          Toc.currencies.CurrenciesDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.currencies.CurrenciesDialog.superclass.show.call(this);
    }
  },
    
  buildForm: function() {
    this.frmCurrency = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'currencies',
        action: 'save_currency'
      }, 
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
      	{
      		xtype: 'panel',
      		border: false,
      		html: '<?php echo $osC_Language->get('introduction_set_default_currency'); ?>'
      	},
        {xtype: 'hidden', name: 'id'},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_title'); ?>', name: 'title', allowBlank: false},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_code'); ?>', name: 'code', allowBlank: false},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_symbol_left'); ?>', name: 'symbol_left'},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_symbol_right'); ?>', name: 'symbol_right'},
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get('field_decimal_places'); ?>', name: 'decimal_places', allowDecimals: false},
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get('field_currency_value'); ?>', name: 'value', decimalPrecision: 10},
        {xtype: 'checkbox', fieldLabel: '<?php echo $osC_Language->get('field_set_default'); ?>', id: 'is_default', name: 'is_default', anchor: ''}
      ]
    });
    
    return this.frmCurrency;
  },

  submitForm : function() {
    this.frmCurrency.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
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