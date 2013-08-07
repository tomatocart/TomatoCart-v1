<?php
/*
  $Id: configuration_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.configuration.ConfigurationGrid = function(config) {
  
  config = config || {};

  this.buildGrid(config.gID);
  
  config.source = {};
  config.clicksToEdit = 1;
  config.listeners = {
    'afteredit': this.onGrdAfterEdit,
    scope: this
  };

  config.tbar = [
    { 
      text: TocLanguage.btnRefresh,
      iconCls:'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];  
  
  Toc.configuration.ConfigurationGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.configuration.ConfigurationGrid, Ext.grid.PropertyGrid, {
  onRefresh: function(){
    this.buildGrid(this.gID);
  },
  
  buildGrid: function(gID){
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'configuration',
        action: 'list_configurations',
        gID: gID
      },
      callback: function(options, success, response) {
        fields = Ext.decode(response.responseText);
        this.fields = fields;
        customEditors = {};
        this.getStore().removeAll();

        Ext.each(fields, function(field, i) {
          this.getStore().add(new Ext.grid.PropertyRecord({
            name: field.title,
            value: field.value
          }));
          
          if(field.type == 'combobox') {
            var gridEditor = null;
            var store = null;
            
            if(field.mode == 'local') {
              this['ds' + field.title] = new Ext.data.SimpleStore({
                fields: [{name: 'id', mapping: 'id'},{name: 'text', mapping: 'text'}],
                data : field.values
              });            
            }else if (field.mode == 'remote') {
              this['ds' + field.title] = new Ext.data.Store({
                reader: new Ext.data.JsonReader({
                    fields: ['id', 'text'],
                    root: Toc.CONF.JSON_READER_ROOT
                }),
                url:Toc.CONF.CONN_URL,
                baseParams: {
                  module: field.module,
                  action: field.action
                },
                pageSize: 10,
                triggerAction: 'all'
              });
            }
            
            gridEditor = new Ext.grid.GridEditor(
              new Ext.form.ComboBox({
                fieldLabel: field.title,
                name: field.name,
                store: this['ds' + field.title],
                displayField: 'text',
                valueField: 'id',
                typeAhead: true,
                mode: field.mode,
                triggerAction: 'all',
                editable: false
              })
            );
            
            customEditors[field.title] = gridEditor;
            
          } else if(field.type == 'textarea') {
            customEditors[field.title] = new Ext.grid.GridEditor(
              new Ext.form.TextArea({
                fieldLabel: field.title,
                name: field.name
              })            
            );
          } else if(field.type == 'password') {
            customEditors[field.title] = new Ext.grid.GridEditor(
              new Ext.form.TextField({
                inputType: 'password',
                fieldLabel: field.title,
                name: field.name
              })            
            );
          }
        }, this);

        this.customEditors = customEditors;
      },
      scope: this
    });
  },

  onGrdAfterEdit: function(o){
    Ext.each(this.fields, function(field, i) {
      if(field.title == o.record.get('name')) {
        var value = o.record.get('value');
        
        if(field.type == 'combobox'){
          o.record.set('value', o.grid.customEditors[field.title].field.getRawValue());
        }
        
        if(field.type == 'password') {
          o.record.set('value', o.value.replace(/[\s\S]/g, '*'));
        }
        
        Ext.Ajax.request({
          url: Toc.CONF.CONN_URL,
          params: {
            module: 'configuration',
            action: 'save_configurations',
            cID: field.id,
            configuration_value: o.value
          },
          callback: function(options, success, response) {
            result = Ext.decode(response.responseText);
            
            if (result.success == true) {
              if (o.record.get('name') == 'Country') {
                this.dsZone.reload();
              }
              this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
            } else {
              Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
            }
          },
          scope: this
        });
      }
    }, this);
  }
});