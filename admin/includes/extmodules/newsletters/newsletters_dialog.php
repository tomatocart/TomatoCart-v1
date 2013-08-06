<?php
/*
  $Id: newsletters_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.newsletters.NewslettersDialog = function(config) {

  config = config || {};
  
  config.id = 'newsletters-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_newsletter'); ?>';
  config.width = 700;
  config.height = 400;
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
  
  Toc.newsletters.NewslettersDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.newsletters.NewslettersDialog, Ext.Window, {
  
  show: function (newslettersId) {
    this.newslettersId = newslettersId || null;
    
    this.frmNewsletter.form.reset();
    this.frmNewsletter.form.baseParams['newsletters_id'] = this.newslettersId;

    if (this.newslettersId > 0) {
      this.frmNewsletter.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'newsletters',
          action: 'load_newsletter'
        },
        success: function(form, action) {
          Toc.newsletters.NewslettersDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
        Toc.newsletters.NewslettersDialog.superclass.show.call(this);
    }
  },
      
  buildForm: function() {
    this.dsModules = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'newsletters', 
        action: 'get_modules'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['id', 'text']
      }),
      autoLoad: true                                                                                  
    });
    
    this.frmNewsletter = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'newsletters',
        action: 'save_newsletter'
      }, 
      layoutConfig: {
        labelSeparator: ''
      },
      defaults: {
        anchor: '97%'
      },
      labelWidth: 100,
      items: [ 
        {
          xtype: 'combo',
          name: 'newsletter_module',
          hiddenName: 'newsletter_module',
          fieldLabel: '<?php echo $osC_Language->get('field_module'); ?>', 
          store: this.dsModules,
          valueField: 'id',
          editable: false,
          displayField: 'text',
          triggerAction: 'all',
          allowBlank: false
        },
        {
          xtype: 'textfield', 
          name: 'title', 
          fieldLabel: '<?php echo $osC_Language->get('field_title'); ?>', 
          allowBlank: false
        },
        {
          xtype: 'htmleditor',
          name: 'content', 
          fieldLabel: '<?php echo $osC_Language->get('field_content'); ?>', 
          height: 250
        }
      ]
    });
    
    return  this.frmNewsletter;
  },

  submitForm : function() {
    this.frmNewsletter.form.submit({
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