<?php
/*
  $Id: translations_edit_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.languages.TranslationsEditGrid = function(config) {

  config = config || {};

  config.layout = 'fit';
  config.border = false;
  config.region = 'center';
  config.clicksToEdit = 1;
  config.loadMask = true;
  this.owner = config.owner;
  
  config.group = 'general';
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'languages',
      action: 'list_translations',
      languages_id: config.languagesId,
      group: 'general'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'languages_definitions_id'
    }, [
      'languages_definitions_id',
      'content_group',
      'definition_key',
      'definition_value'
    ]),
    autoLoad: true
  });
  
  rowActions = new Ext.ux.grid.RowActions({
    header: '',
    widthSlope: 30,
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  rowActions.on('action', this.onRowAction, this);
  config.plugins = rowActions;
  
  config.cm = new Ext.grid.ColumnModel([
    {header: '<?php echo $osC_Language->get('table_heading_definition_key'); ?>', dataIndex: 'definition_key', sortable: true, width: 200},
    {id: 'language_translation', header: '<?php echo $osC_Language->get('table_heading_definition_value'); ?>', dataIndex: 'definition_value', editor: new Ext.form.TextArea({height: 200})},
    rowActions
  ]);
  config.autoExpandColumn = 'language_translation';
  config.search = new Ext.form.TextField({name: 'search', width: 250});
  
  config.tbar = [
    {
      text: '<?php echo $osC_Language->get('button_add_definition'); ?>',
      iconCls: 'add',
      handler: function() {this.onAddDefinition(config.languagesId)},
      scope: this
    },
    '->',
    config.search,
    '',
    {
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }];
  
  config.listeners = {
    afteredit: this.onAfterEdit,
    scope: this
  }

  Toc.languages.TranslationsEditGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.languages.TranslationsEditGrid, Ext.grid.EditorGridPanel, {

	setTranslationGroup: function(group) {
	  this.group = group;
	  
    this.getStore().baseParams['group'] = group;
    this.getStore().reload();
  },
  
  onSearch: function () {
    var store = this.getStore();

    store.baseParams['search'] = this.search.getValue() || null;
    store.reload();
  },
  
  onAddDefinition: function(languagesId) {
    var dlg = this.owner.createTranslationAddDialog();
    
    dlg.on('saveSuccess', function(){
      this.getStore().reload();
    }, this);
    
    dlg.show(languagesId, this.group);
  },

  onAfterEdit: function(e) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'languages',
        action: 'update_translation',
        languages_id: this.languagesId,
        group: this.group,
        definition_key: e.record.get('definition_key'),
        definition_value: e.record.get('definition_value')
      },
      callback: function (options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          e.record.commit();
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);          
        }
      },
      scope: this
    }); 
  },

  onEdit: function(record) {
    var dlg = this.owner.createTranslationEditDialog({
      languagesId: this.languagesId,
      group: this.group,
      definitionKey: record.get('definition_key'),
      definitionValue: record.get('definition_value')
    }); 
    
    dlg.on('saveSuccess', function(feedback, value) {
      record.set('definition_value', value);
      record.commit();
    }, this);
    
    dlg.show();
  },
  
  onDelete: function(record) {
    var languagesDefinitionsId = record.get('languages_definitions_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if ( btn == 'yes' ) {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'languages',
              action: 'delete_translation',
              languages_definitions_id: languagesDefinitionsId
            },
            callback: function(options, success, response) {
              result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                this.getStore().reload();
              } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });   
        }
      }, 
      this
    );
  },

  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  }
});