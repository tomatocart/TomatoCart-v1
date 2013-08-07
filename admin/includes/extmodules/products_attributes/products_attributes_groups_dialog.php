<?php
/*
  $Id: products_attributes_groups_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products_attributes.AttributeGroupsDialog = function(config) {
  
  config = config || {};
  
  config.id = 'products_attributes-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_attributes_group'); ?>';
  config.width = 400;
  config.modal = true;
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
  
  Toc.products_attributes.AttributeGroupsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.products_attributes.AttributeGroupsDialog, Ext.Window, {
  
  show: function(id) {
    var groupsId = id || null;
    
    this.frmAttributeGroup.form.reset();  
    this.frmAttributeGroup.form.baseParams['products_attributes_groups_id'] = groupsId;
    
    if (groupsId > 0) {
      this.frmAttributeGroup.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'products_attributes',
          action: 'load_products_attributes'
        },
        success: function() {
          Toc.products_attributes.AttributeGroupsDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.products_attributes.AttributeGroupsDialog.superclass.show.call(this);
    }
  },
      
  buildForm: function() {
    this.frmAttributeGroup = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'products_attributes',
        action: 'save_products_attributes'
      }, 
      labelAlign: 'top',
      items: [
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_group_name'); ?>', name: 'products_attributes_groups_name', allowBlank: false, labelSeparator: '', anchor: '98%'}
      ]
    });
    
    return this.frmAttributeGroup;
  },
  
  submitForm : function() {
    this.frmAttributeGroup.form.submit({
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