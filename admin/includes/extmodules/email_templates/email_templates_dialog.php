<?php
/*
  $Id: email_templates_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.email_templates.EmailTemplatesDialog = function(config) {

  config = config || {};
  
  config.id = 'email_templatesDialog-win';
  config.layout = 'fit';
  config.width = 720;
  config.height = 450;
  config.modal = true;
  config.iconCls = 'icon-email_templates-win';
  config.items = this.buildForm();
  
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
  
  Toc.email_templates.EmailTemplatesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.email_templates.EmailTemplatesDialog, Ext.Window, {

  show: function (record) {
    emailTemplateId = record.get('email_templates_id');
    this.frmEmailTemplate.form.baseParams['email_templates_id'] = emailTemplateId;
    
    this.frmEmailTemplate.load({
      url: Toc.CONF.CONN_URL,
      params:{
        module: 'email_templates',
        action: 'load_email_template',
        email_templates_id: emailTemplateId
      },
      success: function(form, action) {
        this.dsVariables.baseParams['email_templates_name'] = record.get('email_templates_name');
        this.dsVariables.load();
        
        Toc.email_templates.EmailTemplatesDialog.superclass.show.call(this);
      },
      failure: function(form, action) {
        Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
      }, 
      scope: this
    });
    
    Toc.email_templates.EmailTemplatesDialog.superclass.show.call(this);
  },
  
  getDataPanel: function() {
    this.pnlData = new Ext.Panel({ 
      region: 'north',
      title: '<?php echo $osC_Language->get('heading_title_data'); ?>',
      labelWidth: 150,
      autoHeight: true,
      layout: 'form',
      defaults: {
        style: 'padding: 3px',
        anchor: '97%'
      },
      items: [                           
        { 
          labelSeparator: ' ',
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_email_templates_name'); ?>', 
          name: 'email_templates_name', 
          readOnly: true
        },
        {
          layout: 'column',
          border: false,
          items:[
            {
              width: 280,
              layout: 'form',
              labelSeparator: ' ',
              border: false,
              items:[
                {fieldLabel: '<?php echo $osC_Language->get('field_email_templates_status'); ?>', boxLabel: '<?php echo $osC_Language->get('status_enabled'); ?>' , name: 'email_templates_status', xtype:'radio', inputValue: '1'}
              ]
            },
            {
              width: 120,
              layout: 'form',
              border: false,
              items: [
                {hideLabel: true, boxLabel: '<?php echo $osC_Language->get('status_disabled'); ?>', xtype:'radio', name: 'email_templates_status', inputValue: '0'}
              ]
            }
          ]
        }
      ]
    });
    
    return this.pnlData;
  },  
  
  getContentPanel: function() {
    this.tabLanguage = new Ext.TabPanel({
       region: 'center',
       defaults:{
         hideMode:'offsets'
       },
       activeTab: 0,
       deferredRender: false
    });  
    
    this.dsVariables = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'email_templates', 
        action: 'get_variables'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['id', 'value']
      })                                                                        
    });
    
    <?php
      foreach ($osC_Language->getAll() as $l) {
      
        echo 'this.pnlLang' . $l['id'] . ' = new Ext.Panel({
          labelWidth: 150,
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          labelSeparator: \' \',
          style: \'padding: 6px\',
          items: [
            {
              xtype: \'textfield\', 
              fieldLabel: \'' . $osC_Language->get('field_email_title') . '\', 
              name: \'email_title[' . $l['id'] . ']\', 
              id: \'title[' . $l['id'] . ']\', 
              allowBlank: false,
              width: 520
            },
            {
              layout: \'column\',
              border: false,
              items:[
                {
                  width: 560,
                  layout: \'form\',
                  labelSeparator: \' \',
                  border: false,
                  items:[
                    {
                      fieldLabel: \'' . $osC_Language->get('field_variables') . '\', 
                      name: \'variable[' . $l['id'] . ']\', 
                      id: \'email-template-variables' . $l['id'] . '\', 
                      xtype: \'combo\', 
                      store: this.dsVariables, 
                      displayField: \'value\', 
                      valueField: \'value\', 
                      editable: false, 
                      triggerAction: \'all\', 
                      width: 300
                    }
                  ]
                },
                {
                  width: 80,
                  layout: \'form\',
                  border: false,
                  items: [
                    { 
                      xtype: \'button\', 
                      id: \'btn-insert-variables-'.$l['id'].'\', 
                      text: \'' . $osC_Language->get('button_insert') . '\', 
                      handler: function(){
                        this.insertVariable(' . $l['id'] . ');
                      },
                      scope: this
                    }
                  ]
                }
              ]
            },
            {
              xtype: \'htmleditor\', 
              fieldLabel: \'' . $osC_Language->get('field_email_content') . '\', 
              name: \'email_content[' . $l['id'] . ']\', 
              id: \'email-template-content' . $l['id'] . '\',
              height: \'auto\',
              width: 520,
              listeners: {
                editmodechange: this.onEditModeChange
              }
            }
          ]
        });
        
        this.tabLanguage.add(this.pnlLang' . $l['id'] . ');
        ';
      }
    ?>
    
    return this.tabLanguage;
  },
  
  onEditModeChange: function(htmlEditor, sourceEdit) {
    var code = htmlEditor.getId().toString().substr(22);
    var btn = Ext.getCmp('btn-insert-variables-'+ code);
    
    if (sourceEdit === true) {
      btn.disable();
    } else {
      btn.enable();
    }
  },
  
  insertVariable: function(id) {
     var variable = Ext.getCmp('email-template-variables'+ id).getValue();
     
     var editor = Ext.getCmp('email-template-content'+ id);
     editor.focus(); 
     editor.insertAtCursor(variable);
  },

  buildForm: function() {
    this.frmEmailTemplate = new Ext.FormPanel({
      layout: 'border',
      width: 700,
      border: false,
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'email_templates',
        action: 'save_email_template'
      }, 
      items: [this.getDataPanel(), this.getContentPanel()]
    });
    
    return this.frmEmailTemplate;    
  },
  
  submitForm : function() {
    this.frmEmailTemplate.form.submit({
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