<?php
/*
  $Id: administrators_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.administrators.AdministratorsDialog = function(config) {

  config = config || {};
  
  config.id = 'administrators_dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_administrator'); ?>';
  config.width = 400;
  config.height = 420;
  config.modal = true;
  config.iconCls = 'icon-administrators-win';
  config.layout = 'fit';
  config.items = this.buildForm();  
  
  config.treeLoaded = false;
  
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
  
  Toc.administrators.AdministratorsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.administrators.AdministratorsDialog, Ext.Window, {
  
  show: function (administratorsId) {
    var administratorsId = administratorsId || null;
    
    this.frmAdministrator.form.reset();  
    this.frmAdministrator.form.baseParams['aID'] = administratorsId;

    if (administratorsId > 0) {
      Ext.getCmp('user_password').allowBlank = true;
      
      this.frmAdministrator.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'administrators',
          action: 'load_administrator'
        },
        success: function(form, action) {
          if(action.result.data.access_globaladmin == true) {
            this.chkGlobal.setValue(true);
            this.checkAll();
          }else {
            if (this.treeLoaded == true) {
              this.pnlAccessTree.setValue(action.result.data.access_modules);
            } else {
              this.pnlAccessTree.loader.on('load', function(){
                this.pnlAccessTree.setValue(action.result.data.access_modules);
              }, this);
            }
          }
          
          Toc.administrators.AdministratorsDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.administrators.AdministratorsDialog.superclass.show.call(this);
    }
  },
  
  onCheckChange: function(node, checked) {
    if (node.hasChildNodes) {
      node.expand();
      node.eachChild(function(child) {
        child.ui.toggleCheck(checked);
      });
    }
  },
  
  checkAll: function() {
    this.pnlAccessTree.root.cascade(function(n) {
      if (!n.getUI().isChecked()) {
        n.getUI().toggleCheck(true);
      }
    });
  },
  
  uncheckAll: function() {
    this.pnlAccessTree.root.cascade(function(n) {
      if (n.getUI().isChecked()) {
        n.getUI().toggleCheck(false);
      }
    });
  },
      
  getAdminPanel: function() {
    this.pnlAdmin = new Ext.Panel({
      region: 'north',
      border: false,
      layout: 'form',
      autoHeight: true,
      labelSeparator: ' ',
      defaults: {
        anchor: '98%'
      },
      frame: false,
      style: 'padding: 5px',
      items: [
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_username'); ?>', 
          name: 'user_name', 
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_password'); ?>', 
          name: 'user_password',
          id:  'user_password',
          inputType: 'password', 
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_email'); ?>', 
          name: 'email_address', 
          allowBlank: false
        }
      ]
    });  
    
    return this.pnlAdmin;
  }, 
  
  getAccessPanel: function() {
    this.chkGlobal = new Ext.form.Checkbox({
      name: 'access_globaladmin', 
      boxLabel: '<?php echo $osC_Language->get('global_access'); ?>',
      listeners: {
        check: function(chk, checked) {
          if(checked)
            this.checkAll();
          else
            this.uncheckAll();
        },
        scope: this
      }
    });  
  
    this.pnlAccessTree = new Ext.ux.tree.CheckTreePanel({
      name: 'access_modules', 
      id: 'access_modules',
      region: 'center',
      xtype: 'checktreepanel',
      deepestOnly: true,
      bubbleCheck: 'none',
      cascadeCheck: 'none',
      autoScroll: true,
      border: true,
      bodyStyle: 'background-color:white;border:1px solid #B5B8C8',
      rootVisible: false,
      anchor: '-24 -60',
      root: {
        nodeType: 'async',
        text: 'root',
        id: 'root',
        expanded: true,
        uiProvider: false
      },
      loader: new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        preloadChildren: true, 
        baseParams: {
          module: 'administrators',
          action: 'get_accesses'
        },
        listeners: {
          load: function() {
            this.treeLoaded = true;
          },
          scope: this
        }
      }),
      listeners: {
        checkchange: this.onCheckChange,
        scope: this
      },
      tbar: [this.chkGlobal]
    });  
    
    return this.pnlAccessTree;
  },
  
  buildForm: function() {
    this.frmAdministrator = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'administrators'
      }, 
      border: false,
      layout: 'border',
      items: [
        this.getAccessPanel(), 
        this.getAdminPanel()
      ]                          
    });
    
    return this.frmAdministrator;
  },

  submitForm : function() {
    this.frmAdministrator.baseParams['modules'] = this.pnlAccessTree.getValue().toString();
    
    this.frmAdministrator.form.submit({
      url: Toc.CONF.CONN_URL,
      params: {
        'module' : 'administrators',
        'action' : 'save_administrator'
      },
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