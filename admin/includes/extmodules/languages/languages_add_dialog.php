<?php
/*
  $Id: languages_add_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

   Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.languages.LanguagesAddDialog = function(config) {

  config = config || {};
  
  config.id = 'languages-add-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_import_language'); ?>';
  config.width = 500;
  config.modal = true;
  config.iconCls = 'icon-languages-win';
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
  
  Toc.languages.LanguagesAddDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.languages.LanguagesAddDialog, Ext.Window, {
  
  buildForm: function() {
    this.dsLanguages = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'languages', 
        action: 'get_languages'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['id', 'text']
      })                                                                                    
    });
    
    this.frmLanguage = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'languages',
        action: 'import_language'
      }, 
      border: false,
      layout: 'form',
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 130,
      items: [ 
        {
          xtype: 'combo', 
          fieldLabel: '<?php echo $osC_Language->get('field_language_selection'); ?>', 
          id: 'languages',
          width: 300,
          name: 'languages',
          mode: 'remote', 
          store: this.dsLanguages,
          displayField: 'text',
          valueField: 'id',
          triggerAction: 'all',
          hiddenName: 'languages_id',
          readOnly: true,
          allowBlank: false
        },
        {
          xtype: 'radio', 
          name: 'import_type',
          inputValue: 'add',
          checked: true,
          boxLabel: '<?php echo $osC_Language->get('only_add_new_records'); ?>'
        },
        {
          xtype: 'radio',
          name: 'import_type',
          inputValue: 'update',
          boxLabel: '<?php echo $osC_Language->get('only_update_existing_records'); ?>',
          fieldLabel: '<?php echo $osC_Language->get('field_import_type'); ?>'
        },
        {
          xtype: 'radio',
          name: 'import_type',
          inputValue: 'replace',
          boxLabel: '<?php echo $osC_Language->get('replace_all'); ?>'
        }
      ]
    });
    
    return this.frmLanguage;
  },

  submitForm : function() {
    this.frmLanguage.form.submit({
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