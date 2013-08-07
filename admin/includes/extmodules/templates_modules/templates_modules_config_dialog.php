<?php
/*
  $Id: templates_modules_config_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.templates_modules.TemplatesModulesConfigDialog = function(config) {
  
  config = config || {};

  config.id = 'templates_modules-dialog-win';
  config.layout = 'border';
  config.width = 400;
  config.height = 300;
  config.modal = true;
  config.items = this.buildForm(config.code, config.set);

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

  this.addEvents({'saveSuccess' : true});  
  
  Toc.templates_modules.TemplatesModulesConfigDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.templates_modules.TemplatesModulesConfigDialog, Ext.Window, {

  buildForm: function(code, set) {
    this.moduleForm = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'templates_modules',
        action: 'save'
      }, 
      region: 'center',
      layoutConfig: {
        labelSeparator: ''
      },
      labelAlign: 'top',
      autoScroll: true,
      defaults: {
        anchor: '90%'
      }
    });  
    
    this.requestForm(code, set);
    
    return this.moduleForm;
  },

  requestForm: function(code, set) {  
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'templates_modules',
        action: 'get_configuration_options',
        code: code,
        set: set
      },
      callback: function(options, success, response) {
        var fields = Ext.decode(response.responseText);

        Ext.each(fields, function(field, i) {
          if(field.type == 'textfield'){
            this.moduleForm.add(
              new Ext.form.TextField({
                fieldLabel: '<b>' + field.title + '</b><br/>' + field.description,
                name: field.name,
                value: field.value
              })
            );
          } else if(field.type == 'combobox'){
            combo = new Ext.form.ComboBox({
              fieldLabel: '<b>' + field.title + '</b><br/>' + field.description,
              name: field.name,
              hiddenName: field.name,
              store: new Ext.data.SimpleStore({
                fields: [{name: 'id', mapping: 'id'}, {name: 'text', mapping: 'text'}],
                data : field.values
              }),
              displayField: 'text',
              valueField: 'id',
              mode: 'local',
              triggerAction: 'all',
              readOnly: false,
              allowblank: false,
              value: field.value
            });
                   
            this.moduleForm.add(combo);
          }
        },this);
        
        this.doLayout();
      },
      scope: this
    });

  },

  submitForm : function() {
    this.moduleForm.form.submit({
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