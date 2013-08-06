<?php
/*
  $Id: tax_classes_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.tax_classes.TaxClassesDialog = function(config) {

  config = config || {};
  
  config.id = 'tax-class-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_tax_class'); ?>';
  config.width = 500;
  config.modal = true;
  config.iconCls = 'icon-tax_classes-win';
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
  
  Toc.tax_classes.TaxClassesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.tax_classes.TaxClassesDialog, Ext.Window, {
  
  show: function (id) {
    var taxClassesId = id || null;
    
    this.frmTaxClass.form.reset(); 
    this.frmTaxClass.form.baseParams['tax_class_id'] = taxClassesId;

    if (taxClassesId > 0) {
      this.frmTaxClass.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'tax_classes',
          action: 'load_tax_class'
        },
        success: function() {
          Toc.tax_classes.TaxClassesDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.tax_classes.TaxClassesDialog.superclass.show.call(this);
    }
  },
      
  buildForm: function() {
    this.frmTaxClass = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'tax_classes',
        action: 'save_tax_class'
      }, 
      layoutConfig: {
        labelSeparator: ''
      },
      defaults: {
        anchor: '97%'
      },
      items: [                           
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_title'); ?>', 
          name: 'tax_class_title', 
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_description'); ?>', 
          name: 'tax_class_description'
        }
      ]
    });
    
    return this.frmTaxClass;
  },

  submitForm: function() {
    this.frmTaxClass.form.submit({
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