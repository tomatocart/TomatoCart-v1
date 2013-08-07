<?php
/*
  $Id: templates_modules_layout_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.templates_modules_layout.TemplatesModulesLayoutDialog = function(config) {
  
  config = config || {};
  
  this.filter = null;
  this.set = null;

  config.id = 'templates_modules_layout-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_order_status'); ?>';
  config.layout = 'fit';
  config.width = 450;
  config.modal = true;
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text:TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope:this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function() {
        this.close();
      },
      scope:this
    }
  ];
  
  Toc.templates_modules_layout.TemplatesModulesLayoutDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.templates_modules_layout.TemplatesModulesLayoutDialog, Ext.Window, {
  
  show: function(boxPageId, filter, set) {
    boxPageId = boxPageId || null;
    
    this.setFilter(filter, set);
    this.frmLayout.form.reset(); 
    this.frmLayout.form.baseParams['box_page_id'] = boxPageId;
    
    if (boxPageId > 0) {
      this.frmLayout.load({
        url: Toc.CONF.CONN_URL,
        params: {
          action: 'load_box_layout',
          filter: this.filter,
          set: this.set
        },
        success: function(form, action) {
          this.cboModules.disable();
          
          Toc.templates_modules_layout.TemplatesModulesLayoutDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.templates_modules_layout.TemplatesModulesLayoutDialog.superclass.show.call(this);
    }
  },
  
  setFilter: function(filter, set) {
    this.set = set;
    this.filter = filter;
      
    this.cboModules.getStore().baseParams['set'] = set;
    
    this.cboPages.getStore().baseParams['filter'] = filter;
    this.cboPages.getStore().baseParams['set'] = set;
    
    this.cboGroups.getStore().baseParams['filter'] = filter;
    this.cboGroups.getStore().baseParams['set'] = set;
    
    this.frmLayout.baseParams['filter'] = filter;
    this.frmLayout.baseParams['set'] = set;
  },
  
  buildForm: function() {
    this.cboModules = new Ext.form.ComboBox({
      allowBlank: false,
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'templates_modules_layout',
          action: 'get_modules'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT
        }, [
          'id', 
          'text'
        ]),
        autoLoad: true
      }),  
      fieldLabel: '<?php echo $osC_Language->get('field_module'); ?>', 
      triggerAction: 'all',
      readOnly: true,
      name: 'module',
      hiddenName: 'box',
      valueField: 'id',
      displayField: 'text'
    });    
    
    this.cboPages = new Ext.form.ComboBox({
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'templates_modules_layout',
          action: 'get_pages'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT
        }, [
          'id',
          'text'
        ]),
        autoLoad: true
      }),  
      allowBlank: false,
      fieldLabel: '<?php echo $osC_Language->get('field_pages'); ?>', 
      name: 'page',
      triggerAction: 'all',
      readOnly: true,
      hiddenName: 'content_page',
      valueField: 'id',
      displayField: 'text'
    });
    
    this.cboGroups = new Ext.form.ComboBox({
      store: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'templates_modules_layout',
          action: 'get_groups'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT
        }, [
          'id',
          'text'
        ]),
        autoLoad: true
      }), 
      fieldLabel: '<?php echo $osC_Language->get('field_group'); ?>', 
      name: 'group', 
      triggerAction: 'all',
      readOnly: true,
      valueField: 'id',
      displayField: 'text'
    });
    
    this.frmLayout = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        set: this.set,
        module: 'templates_modules_layout',
        action : 'save_box_layout'
      }, 
      border:false,
      layout: 'form',
      autoHeight: true,
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        this.cboModules,
        this.cboPages,
        {
          layout: 'column',
          border: false,
          items: [{
            layout: 'form',
            border: false,
            width: 200,
            items: [{
              xtype: 'checkbox', 
              fieldLabel: '<?php echo $osC_Language->get('field_page_specific'); ?>', 
              name: 'page_specific', 
              width: 50
            }]
          }]
        },
        this.cboGroups,
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_group_new'); ?>', name: 'group_new'},
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_sort_order'); ?>', name: 'sort_order'}
      ]
    });
    
    return this.frmLayout;
  },

  submitForm : function() {
    this.frmLayout.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      }, scope: this
    });   
  }
});