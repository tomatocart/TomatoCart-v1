<?php
/*
  $Id:languages_edit_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

   Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.languages.LanguagesEditDialog = function(config) {

  config = config || {};
  
  config.id = 'languages-edit-dialog-win';
  config.width = 640;
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
  
  Toc.languages.LanguagesEditDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.languages.LanguagesEditDialog, Ext.Window, {

  show: function (id) {
    var languagesId = id || null;
    
    this.frmEditLanguage.form.reset(); 
    this.frmEditLanguage.form.baseParams['languages_id'] = languagesId;
    this.dsParentLanguages.baseParams['languages_id'] = languagesId;
     
    if (languagesId > 0) {
      this.frmEditLanguage.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'languages',
          action: 'load_language'
        },
        success: function(form, action) {
        
          if ( !action.result.data['default'] ) {
            this.frmEditLanguage.add({
              xtype: 'checkbox', 
              fieldLabel: '<?php echo $osC_Language->get('field_set_default'); ?>', 
              name: 'default', 
              inputValue: 'on'
            });
          }
          Toc.languages.LanguagesEditDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.languages.LanguagesEditDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function() {
    this.dsTextDirections = new Ext.data.JsonStore({
      root: 'rows',
      data: {
        rows : [
          {id: 'ltr', text: 'ltr'},
          {id: 'rtl', 'text': 'rtl'}
        ]
      },
      fields: [
        {name: 'id', mapping: 'id'},
        {name: 'text', mapping: 'text'}
      ],
      autoLoad: true
    });
    
    this.dsCurrencies = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'languages', 
        action: 'get_currencies'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['currencies_id', 'text']
      }),
      autoLoad: true                                                                                    
    });
    
    this.dsParentLanguages = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'languages', 
        action: 'get_parent_language'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['parent_id', 'text']
      }),
      autoLoad: true                                                                                    
    });

    this.frmEditLanguage = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'languages',
        action: 'save_language'
      }, 
      layoutConfig: {
        labelSeparator: ''
      },
      defaults: {
        anchor: '97%'
      },
      labelWidth: 200,
      items: [ 
      	{
      		xtype: 'panel',
      		border: false,
      		html: '<?php echo $osC_Language->get('introduction_set_default_currency'); ?>'
      	},
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_name'); ?>', 
          name: 'name', 
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_code'); ?>', 
          name: 'code', 
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_locale'); ?>', 
          name: 'locale', 
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_character_set'); ?>', 
          name: 'charset', 
          allowBlank: false
        },
        {
          xtype: 'combo', 
          fieldLabel: '<?php echo $osC_Language->get('field_text_direction'); ?>',
          name: 'text_direction',
          id: 'text_direction', 
          mode: 'local', 
          store: this.dsTextDirections,
          displayField: 'text',
          valueField: 'id',
          triggerAction: 'all',
          hiddenName: 'text_id',
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_date_format_short'); ?>', 
          name: 'date_format_short', 
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_date_format_long'); ?>', 
          name: 'date_format_long', 
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_time_format'); ?>', 
          name: 'time_format', 
          allowBlank: false
        },
        {
          xtype: 'combo',
          fieldLabel: '<?php echo $osC_Language->get('field_currency'); ?>', 
          store: this.dsCurrencies,
          displayField: 'text',
          valueField: 'currencies_id',
          triggerAction: 'all',
          hiddenName: 'currencies_id',
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_currency_separator_decimal'); ?>', 
          name: 'numeric_separator_decimal', 
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_currency_separator_thousands'); ?>', 
          name: 'numeric_separator_thousands', 
          allowBlank: false
        },
        {
          xtype: 'combo',
          fieldLabel: '<?php echo $osC_Language->get('field_parent_language'); ?>', 
          mode: 'local', 
          store: this.dsParentLanguages,
          displayField: 'text',
          valueField: 'parent_id',
          triggerAction: 'all',
          hiddenName: 'parent_id',
          allowBlank: false
        },
        {
          xtype: 'numberfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_sort_order'); ?>', 
          name: 'sort_order'
        }
      ]
    });
    
    return this.frmEditLanguage;
  },

  submitForm : function() {
    this.frmEditLanguage.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action){
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