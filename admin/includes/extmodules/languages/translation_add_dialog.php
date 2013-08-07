<?php
/*
  $Id: translation_add_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

   Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.languages.TranslationAddDialog = function(config) {

  config = config || {};
  
  config.id = 'translation-add-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_add_definition'); ?>';
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
  
  Toc.languages.TranslationAddDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.languages.TranslationAddDialog, Ext.Window, {
  
  show: function (languagesId, group) {
    this.languagesId = languagesId || null;

    if (this.languagesId > 0) {
      this.frmLanguage.baseParams['languages_id'] = this.languagesId;
    
      this.dsGroups.baseParams['languages_id'] = this.languagesId;
      this.dsGroups.on('load', function(){
        this.cboGroups.setValue(group);
      }, this);
      
      this.dsGroups.load();
    }
    
    Toc.languages.TranslationAddDialog.superclass.show.call(this);
  },
  
  buildForm: function() {
    this.dsGroups = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'languages', 
        action: 'get_groups'
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
        action: 'add_translation'
      }, 
      border: false,
      style: 'padding: 8px',
      layoutConfig: {
        labelSeparator: ' '
      },
      labelWidth: 120,
      defaults: {
        anchor: '97%',
        allowBlank: false
      },
      items: [ 
        this.cboGroups = new Ext.form.ComboBox({
          fieldLabel: '<?php echo $osC_Language->get('field_group_selection'); ?>', 
          id: 'languages',
          name: 'languages',
          store: this.dsGroups,
          displayField: 'text',
          valueField: 'id',
          triggerAction: 'all',
          hiddenName: 'definition_group',
          readOnly: true
        }),
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_definition_key'); ?>', 
          name: 'definition_key'
        },
        {
          xtype: 'textarea',
          fieldLabel: '<?php echo $osC_Language->get("field_definition_value"); ?>',
          name: 'definition_value'
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