<?php
/*
  $Id: products_move_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.products.CategoriesMoveDialog = function (config) {
  config = config || {};
  
  config.id = 'products-move-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_batch_move_categories"); ?>';
  config.layout = 'fit';
  config.width = 400;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-categories-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function () {
        this.submitForm();
        this.disable();
      }, 
      scope: this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      }, 
      scope: this
    }
  ];
  
  this.addEvents({'saveSuccess': true});
  
  Toc.products.CategoriesMoveDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.products.CategoriesMoveDialog, Ext.Window, {

  show: function (batch) {
    this.frmCategories.form.reset();
    this.frmCategories.form.baseParams['batch'] = batch;

    Toc.products.CategoriesMoveDialog.superclass.show.call(this);
  },
  
  buildForm: function () {
    dsParentCategories = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'get_categories',
        top: 1
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        fields: [
          'id', 
          'text'
        ]
      }),
      autoLoad: true
    });
    
    this.cboCategories = new Toc.CategoriesComboBox({
      store: dsParentCategories,
      displayField: 'text',
      mode: 'remote',
      fieldLabel: '<?php echo $osC_Language->get("field_parent_category"); ?>',
      valueField: 'id',
      hiddenName: 'parent_category_id',
      triggerAction: 'all',
      allowBlank: true,
      editable: false
    });
    
    this.frmCategories = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'products',
        action: 'move_categories'
      },
      border: false,
      frame: false,
      autoHeight: true,      
      labelAlign: 'top',
      defaults: {anchor: '97%'},
      layoutConfig: { labelSeparator: '' },
      labelWidth: 160,
      items: this.cboCategories
    });
    
    return this.frmCategories;
  },
    
  submitForm: function () {
    this.frmCategories.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function (form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });
  }
});