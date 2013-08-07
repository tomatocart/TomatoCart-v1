<?php
/*
  $Id: products_duplicate_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.products.ProductsDuplicateDialog = function(config) {
  
  config = config || {};
  
  config.id = 'products_duplicate-dialog-win';
  config.layout = 'fit';
  config.width = 450;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-products-win';

  config.items = this.buildForm(config.productsId);
  
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
  
  Toc.products.ProductsDuplicateDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.products.ProductsDuplicateDialog, Ext.Window, {
  
  buildForm: function(productsId) {
    this.frmProduct = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'products',
        action: 'copy_product',
        products_id: productsId
      }, 
      autoHeight: true,
      style: 'padding: 8px',
      border: false,
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 200,
      items: [
        {xtype: 'statictextfield', hideLabel: true, value: '<?php echo $osC_Language->get('introduction_duplicate_product'); ?>'},
        {xtype: 'checkbox', name: 'copy_images', fieldLabel: '<?php echo $osC_Language->get('field_copy_images'); ?>', checked: true, inputValue: 1},
        {xtype: 'checkbox', name: 'copy_variants', fieldLabel: '<?php echo $osC_Language->get('field_copy_variants'); ?>', checked: true, inputValue: 1},
        {xtype: 'checkbox', name: 'copy_attributes', fieldLabel: '<?php echo $osC_Language->get('field_copy_attributes'); ?>', checked: true, inputValue: 1},
        {xtype: 'checkbox', name: 'copy_customization_fields', fieldLabel: '<?php echo $osC_Language->get('field_copy_customization_fields'); ?>', checked: true, inputValue: 1},
        {xtype: 'checkbox', name: 'copy_attachments', fieldLabel: '<?php echo $osC_Language->get('field_copy_attachments'); ?>', checked: true, inputValue: 1},
        {xtype: 'checkbox', name: 'copy_accessories', fieldLabel: '<?php echo $osC_Language->get('field_copy_accessories'); ?>', checked: true, inputValue: 1},
        {xtype: 'checkbox', name: 'copy_xsell', fieldLabel: '<?php echo $osC_Language->get('field_copy_xsell'); ?>', checked: true, inputValue: 1}
      ]
    });
    
    return this.frmProduct;
  },

  submitForm: function() {
    this.frmProduct.form.submit({
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