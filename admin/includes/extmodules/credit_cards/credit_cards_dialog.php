<?php
/*
  $Id: credit_cards_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.credit_cards.CreditCardsDialog = function(config) {

  config = config || {};
  
  config.id = 'credit_cards_dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_card'); ?>';
  config.width = 460;
  config.modal = true;
  config.iconCls = 'icon-credit_cards-win';
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

  this.addEvents({'saveSuccess': true});  
  
  Toc.credit_cards.CreditCardsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.credit_cards.CreditCardsDialog, Ext.Window, {
  
  show: function (id) {
    var creditCardsId = id || null;
    
    this.frmCreditCard.form.reset();  
    this.frmCreditCard.form.baseParams['credit_cards_id'] = creditCardsId;

    if (creditCardsId > 0) {
      this.frmCreditCard.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'credit_cards',
          action: 'load_credit_card'
        },
        success: function() {
          Toc.credit_cards.CreditCardsDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.credit_cards.CreditCardsDialog.superclass.show.call(this);
    }
  },
      
  buildForm: function() {
    this.frmCreditCard = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'credit_cards',
        action: 'save_credit_card'
      }, 
      layout: 'form',
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      items: [                           
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_name'); ?>', 
          name: 'credit_card_name', 
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_pattern'); ?>', 
          name: 'pattern', 
          allowBlank: false
        },
        {
          xtype: 'numberfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_sort_order'); ?>', 
          name: 'sort_order', 
          allowBlank: false
        },
        {
          xtype: 'checkbox', 
          fieldLabel: '<?php echo $osC_Language->get('field_status'); ?>', 
          name: 'credit_card_status',
          inputValue: 'on',
          anchor: '', 
          allowBlank: false
        }     
      ]
    });
    
    return this.frmCreditCard;
  },

  submitForm: function() {
    this.frmCreditCard.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
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