<?php
/*
  $Id: translation_edit_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.languages.TranslationEditDialog = function(config) {

  config = config || {};

  config.id = 'translation-edit-win';
  config.title = config.definitionKey;
  config.layout = 'fit';
  config.width = 400;
  config.height = 240;
  config.modal = true;
  config.iconCls = 'icon-languages-win';
  config.items = this.buildForm(config); 

  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function () {
        this.submitForm();
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

  Toc.languages.TranslationEditDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.languages.TranslationEditDialog, Ext.Window, {

  buildForm: function(config){
    this.frmTranslationEdit = new Ext.form.FormPanel({
      baseParams: {
        module: 'languages',
        languages_id: config.languagesId,
        group: config.group,
        definition_key: config.definitionKey
      },
      layout: 'border',
      border: false,
      style: 'padding: 8px',
      items: [
        this.txtTranslation = new Ext.form.TextArea({
        region: 'center',
        emptyText: TocLanguage.gridNoRecords,
        name: 'definition_value', 
        allowBlank: false,
        value: config.definitionValue
      })]
    });

    return this.frmTranslationEdit;
  },

  submitForm : function() {
    this.frmTranslationEdit.form.submit({
      url: Toc.CONF.CONN_URL,
      params: { 
        module: 'languages',
        action: 'update_translation'
      },
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.fireEvent('saveSuccess', action.result.feedback, this.txtTranslation.getValue());
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