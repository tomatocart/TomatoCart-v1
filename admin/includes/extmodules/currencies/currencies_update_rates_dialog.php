<?php
/*
  $Id: currencies_update_rates_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.currencies.CurrenciesUpdateRatesDialog = function(config) {
  
  config = config || {};

  config.id = 'currencies-update-rates-win';
  config.title = '<?php echo $osC_Language->get('action_heading_update_rates'); ?>';
  config.iconCls = 'icon-update-exchange-rates';
  config.layout = 'fit';
  config.width = 450;
  config.height = 240;
  config.modal = true;
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: '<?php echo $osC_Language->get('button_update'); ?>',
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
  
  this.addEvents({'updateSuccess' : true});  
  
  Toc.currencies.CurrenciesUpdateRatesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.currencies.CurrenciesUpdateRatesDialog, Ext.Window, {
      
  buildForm: function() {
    this.frmUpdateRates = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'currencies',
        action : 'update_currency_rates'
      }, 
      border: false,
      frame: false,
      layout: 'form',
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        {border: false, html: '<p class="form-info"><?php echo $osC_Language->get('introduction_update_exchange_rates'); ?></p>'},
        new Ext.form.Radio({name: 'service', boxLabel: 'Oanda (http://www.oanda.com)', inputValue: 'oanda', hideLabel: true, checked: true}),
        new Ext.form.Radio({name: 'service', boxLabel: 'XE (http://www.xe.com)', inputValue: 'xe', hideLabel: true}),
        {border: false, html: '<p class="form-info"><?php echo $osC_Language->get('service_terms_agreement'); ?></p>'}
      ]
    });
    
    return this.frmUpdateRates;
  },

  submitForm : function() {
    this.frmUpdateRates.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action){
        this.fireEvent('updateSuccess', action.result.feedback);
        Ext.MessageBox.alert(TocLanguage.msgSuccessTitle, action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
      },
      scope: this
    });   
  }
});