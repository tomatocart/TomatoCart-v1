<?php
/*
  $Id: account_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.email.AccountDialog = function(config) {

  config = config || {};
  
  config.id = 'account_dialog-win';
  config.iconCls = 'icon-account-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_account'); ?>';
  config.width = 500;
  config.height = 400;
  config.modal = true;
  config.layout = 'fit';
  config.plain = true;

  config.items = this.buildForm();  
  
  config.buttons = [
    {
      text: TocLanguage.btnOk,
      handler: this.submitForm,
      scope: this
    },
    {
      text: '<?php echo $osC_Language->get('button_apply'); ?>',
      handler: this.submitForm,
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
  
  this.addEvents('saveSuccess'); 
    
  Toc.email.AccountDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.email.AccountDialog, Ext.Window, {

  show: function(id) {
    this.accountId = id || null;
    
    this.frmAccount.form.reset();  
    this.frmAccount.form.baseParams['accounts_id'] = this.accountId;
    
    if (this.accountId > 0) {
      this.frmAccount.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'email',
          action: 'load_account'
        },
        success: function(form,action) {
          Toc.email.AccountDialog.superclass.show.call(this);
          
          this.cboProtocolType.disable();
          this.oldPassword = action.result.data.old_password;
          this.updateIncomingMailForm();
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {
      Toc.email.AccountDialog.superclass.show.call(this);
    }
    
    this.updateIncomingMailForm();
  },
  
  buildPropertiesPanel: function(){
    this.pnlProperties = new Ext.Panel({
      title: '<?php echo $osC_Language->get('section_properties'); ?>',
      layout: 'form',
      defaults: {
        xtype: 'textfield',
        allowBlank: false,
        anchor: '97%'
      },
      labelSeparator: ' ',
      style: 'padding: 8px',
      items: [
        {fieldLabel: '<?php echo $osC_Language->get('field_name'); ?>', name: 'accounts_name', value: '<?php echo STORE_OWNER ?>'},
        {fieldLabel: '<?php echo $osC_Language->get('field_email'); ?>', name: 'accounts_email', value: '<?php echo  STORE_OWNER_EMAIL_ADDRESS ?>'}, 
        {fieldLabel: '<?php echo $osC_Language->get('field_signature'); ?>', name: 'signature', xtype: 'textarea', allowBlank: true, height: 100}
      ]
    });
    
    return this.pnlProperties;
  },
  
  buildIncomingMailPanel: function(){
    this.dsSubscribedFolders = new Ext.data.SimpleStore({
      data: [],
      fields: ['folders_name']
    });  
    
    this.pnlIncomingMail = new Ext.Panel({
      title: '<?php echo $osC_Language->get('section_incoming_mail'); ?>',
      layout: 'form',
      defaults: {
        xtype: 'textfield',
        allowBlank: false
      },
      labelWidth: 110,
      labelSeparator: ' ',
      style: 'padding: 8px',
      waitMsgTarget: true,
      items: [
        this.txtHost = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_host'); ?>', name: 'host', anchor: '97%'}), 
        this.txtEmail = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_username'); ?>', name: 'username', anchor: '97%'}),
        this.txtPassword = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_passsword'); ?>', name: 'password', inputType: 'password', anchor: '97%'}), 
        this.cboProtocolType = new Ext.form.ComboBox({
          fieldLabel: '<?php echo $osC_Language->get('field_type'); ?>',
          hiddenName: 'type',
          store: new Ext.data.SimpleStore({
            fields: ['value', 'text'],
            data: [
              ['pop3', 'POP-3'],
              ['imap', 'IMAP']
            ]
          }),
          value: 'pop3',
          valueField: 'value',
          displayField: 'text',
          mode: 'local',
          triggerAction: 'all',
          editable: false,
          selectOnFocus: true,
          forceSelection: true,
          anchor: '97%',
          listeners: {
            select: this.onProtocolTypeChange,
            scope: this
          }
        }),
        this.cboSentItems = new Ext.form.ComboBox({
          fieldLabel: '<?php echo $osC_Language->get('field_sent_items_folder'); ?>',
          hiddenName: 'sent',
          disabled: true,
          editable: false,
          store: this.dsSubscribedFolders,
          mode: 'local',
          valueField: 'folders_name',
          displayField: 'folders_name',
          typeAhead: true,
          triggerAction: 'all',
          selectOnFocus: true,
          allowBlank: true,
          forceSelection: false,
          emptyText: '<?php echo $osC_Language->get('empty_text_cbo_sent_items_folder'); ?>',
          anchor: '94%',
          listeners: {
            focus: this.loadImapFolders,
            scope: this
          }
        }), 
        this.cboTrash = new Ext.form.ComboBox({
          fieldLabel: '<?php echo $osC_Language->get('field_trash_folder'); ?>',
          hiddenName: 'trash',
          disabled: true,
          editable: false,
          store: this.dsSubscribedFolders,
          mode: 'local',
          valueField: 'folders_name',
          displayField: 'folders_name',
          typeAhead: true,
          triggerAction: 'all',
          selectOnFocus: true,
          forceSelection: true,
          emptyText: '<?php echo $osC_Language->get('empty_text_cbo_trash_folder'); ?>',
          anchor: '94%',
          listeners: {
            focus: this.loadImapFolders,
            scope: this
          }
        }), 
        this.cboDraft = new Ext.form.ComboBox({
          fieldLabel: '<?php echo $osC_Language->get('field_draft_folder'); ?>',
          hiddenName: 'drafts',
          disabled: true,
          editable: false,
          store: this.dsSubscribedFolders,
          mode: 'local',
          valueField: 'folders_name',
          displayField: 'folders_name',
          typeAhead: true,
          triggerAction: 'all',
          selectOnFocus: true,
          allowBlank: true,
          forceSelection: false,
          emptyText: '<?php echo $osC_Language->get('empty_text_cbo_draft_folder'); ?>',
          anchor: '94%',
          listeners: {
            focus: this.loadImapFolders,
            scope: this
          }
        }),        
        this.txtPort = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_port'); ?>', name: 'port', value: '110', allowBlank: false}),
        this.chkKeepCopyOnServer = new Ext.form.Checkbox({fieldLabel: ' ', boxLabel: '<?php echo $osC_Language->get('field_save_copy_on_server'); ?>', name: 'save_copy_on_server', checked: true}),          
        this.chkSSL = new Ext.form.Checkbox({
          fieldLabel: ' ', 
          boxLabel: '<?php echo $osC_Language->get('field_ssl'); ?>', 
          name: 'use_ssl', 
          checked: false,
          listeners: {
            check: this.onChkSSLCheck,
            scope: this
          }
        }),
        this.chkNovalidateCert = new Ext.form.Checkbox({xtype: 'checkbox', fieldLabel: ' ', boxLabel: '<?php echo $osC_Language->get('filed_validate_certificate'); ?>', name: 'novalidate_cert', checked: false})
      ]
    }); 
    
    return this.pnlIncomingMail; 
  },

  buildOutgoingMailPanel: function(){
    this.pnlOutgoingMail = new Ext.Panel({
      title: '<?php echo $osC_Language->get('section_outgoing_mail'); ?>',
      layout: 'form',
      labelSeparator: ' ', 
      style: 'padding: 4px 8px 8px 8px',
      defaults: {
        anchor: '97%'
      },
      items: [
        this.chkUseSystemMailer = new Ext.form.Checkbox({
          fieldLabel: '',
          boxLabel: '<?php echo $osC_Language->get('field_use_system_mailer'); ?>',
          name: 'use_system_mailer',
          listeners: {
            check: this.onChkUseSystemMailerCheck,
            scope: this
          }
        }),
        this.txtSmtpHost = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_host'); ?>', name: 'smtp_host', allowBlank: false}), 
        this.cboEncryptrion = new Ext.form.ComboBox({
          fieldLabel: '<?php echo $osC_Language->get('field_encryption'); ?>',
          hiddenName: 'smtp_encryption',
          store: new Ext.data.SimpleStore({
            fields: ['value', 'text'],
            data: [
              ['', 'No encryption'],
              ['tls', 'TLS'], 
              ['ssl', 'SSL']
            ]
          }),
          valueField: 'value',
          displayField: 'text',
          typeAhead: true,
          mode: 'local',
          triggerAction: 'all',
          editable: false,
          selectOnFocus: true,
          forceSelection: true,
          listeners: {
            select: this.onCboEncryptionSelect,
            scope: this
          }
        }), 
        this.txtSmtpPort = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_port'); ?>', name: 'smtp_port', value: '25', allowBlank: false}), 
        this.txtSmtpUsername = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_username'); ?>', name: 'smtp_username', allowBlank: false}), 
        this.txtSmtpPassword = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_passsword'); ?>', name: 'smtp_password', inputType: 'password', allowBlank: false})
      ]
    });
    
    return this.pnlOutgoingMail;
  },
  
  updateIncomingMailForm: function () {
    if (this.cboProtocolType.getValue() == 'imap') {
      this.chkKeepCopyOnServer.getEl().up('.x-form-item').setDisplayed(false);
      
      this.cboSentItems.getEl().up('.x-form-item').setDisplayed(true);
      this.cboTrash.getEl().up('.x-form-item').setDisplayed(true);
      this.cboDraft.getEl().up('.x-form-item').setDisplayed(true);
              
      this.cboSentItems.enable();
      this.cboTrash.enable();
      this.cboDraft.enable();
    } else {
      this.chkKeepCopyOnServer.getEl().up('.x-form-item').setDisplayed(true);
      
      this.cboSentItems.getEl().up('.x-form-item').setDisplayed(false);
      this.cboTrash.getEl().up('.x-form-item').setDisplayed(false);
      this.cboDraft.getEl().up('.x-form-item').setDisplayed(false);
              
      this.cboSentItems.disable();
      this.cboTrash.disable();
      this.cboDraft.disable();
    }
  },
  
  loadImapFolders: function() {
    if (this.dsSubscribedFolders.getCount() == 0) {
      if (this.cboProtocolType.getValue() == 'imap') {
        if( !( Ext.isEmpty(this.txtHost.getValue()) || Ext.isEmpty(this.txtEmail.getValue()) || Ext.isEmpty(this.txtPassword.getValue()) ) ) {
          this.body.mask(TocLanguage.loadingText);
          
          Ext.Ajax.timeout = 600000;
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'email',
              action: 'get_imap_folders',
              host: this.txtHost.getValue(),
              email: this.txtEmail.getValue(),
              password: ( (this.accountId == null) ? this.txtPassword.getValue() : this.oldPassword ),
              port: this.txtPort.getValue(),
              use_ssl: ((this.chkSSL.getValue() == true) ? 1 : 0),
              novalidate_cert: ((this.chkNovalidateCert.getValue() == true) ? 1 : 0)
            },
            callback: function(options, success, response) {
              this.body.unmask();
              
              result = Ext.decode(response.responseText);
              if (result.success == true) {
                this.dsSubscribedFolders.loadData(result.folders);
              } else {
                alert(result.feedback);
              } 
            },
            scope: this
          });    
        }
      }
    }
  },
  
  onProtocolTypeChange: function() {
    var type = this.cboProtocolType.getValue();
    var port = (type == 'pop3') ? '110' : '143';
    this.txtPort.setValue(port);
    
    this.loadImapFolders();
    this.updateIncomingMailForm();
  },
  
  onChkUseSystemMailerCheck: function(checkbox, checked) {
      if (checked) {
        this.txtSmtpHost.allowBlank = true;
        this.txtSmtpPort.allowBlank = true;
        this.txtSmtpUsername.allowBlank = true;
        this.txtSmtpPassword.allowBlank = true;
        
        this.txtSmtpHost.setValue('');
        this.txtSmtpPort.setValue('');
        this.txtSmtpUsername.setValue('');
        this.txtSmtpPassword.setValue('');
        
        this.txtSmtpHost.disable();
        this.cboEncryptrion.disable();
        this.txtSmtpPort.disable();
        this.txtSmtpUsername.disable();
        this.txtSmtpPassword.disable();
      } else {
        this.txtSmtpHost.allowBlank = false;
        this.txtSmtpPort.allowBlank = false;
        this.txtSmtpUsername.allowBlank = false;
        this.txtSmtpPassword.allowBlank = false;
        
        this.txtSmtpHost.enable();
        this.cboEncryptrion.enable();
        this.txtSmtpPort.enable();
        this.txtSmtpUsername.enable();
        this.txtSmtpPassword.enable();
        
        this.txtSmtpPort.setValue('25');
      }
  },
  
  onCboEncryptionSelect: function (combo, record, index) {
    var encryptionType = record.get('value');
    var port = ((encryptionType == 'tls') || (encryptionType == 'ssl')) ? '465' : '25';
    
    this.txtSmtpPort.setValue(port);
  },
  
  onChkSSLCheck: function (checkbox, checked) {
    var type = this.cboProtocolType.getValue();
    
    if (type == 'imap') {
      var port = checked ? 993 : 143;
    } else if (type == 'pop3') {
      var port = checked ? 995 : 110;
    }
    
    this.txtPort.setValue(port);
  },
  
  buildForm: function() {
    this.frmAccount = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'email',
        action: 'save_account'
      }, 
      layout: 'fit',
      waitMsgTarget: true,
      border: false,
      items:[
        this.tabAccount = new Ext.TabPanel({
          hideLabel: true,
          deferredRender: false,
          activeTab: 0,
          border: false,
          items: [
            this.buildPropertiesPanel(), 
            this.buildIncomingMailPanel(), 
            this.buildOutgoingMailPanel()
          ]
        })
      ]
    });
    
    return this.frmAccount;
  },

  submitForm: function() {
    this.frmAccount.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        accountNode = action.result.accounts_node;
        
        if (accountNode == null) {
          this.close();
          return;
        }
        
        this.fireEvent('saveSuccess', action.result.feedback, accountNode);
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