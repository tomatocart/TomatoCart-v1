<?php
/*
  $Id: email_composer_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
Toc.email.EmailComposerDialog = function(config) {
  
  config = config || {};
  
  config.id = 'composer_dialog-win';
  config.layout = 'fit';
  config.title = '<?php echo $osC_Language->get('action_heading_email_compose'); ?>';
  config.iconCls = 'icon-email_compose-win';
  config.width = 700;
  config.height = 470;
  config.modal = true;
  config.items = this.buildComposerForm();
  
  config.listeners = {
    close: this.clearAttachmentsCache
  };
  
  Toc.email.EmailComposerDialog.superclass.constructor.call(this, config);
  
  this.accountsId = null;
  this.messagesId = null;
  this.priority = 3;
  this.contentType = 'html';  
  this.notification = false;
  this.attachments = [];
  
  this.needSaveDraft = true;
  
  this.addEvents('saveSuccess'); 
};

Ext.extend(Toc.email.EmailComposerDialog, Ext.Window, {

  buildComposerForm: function(){
    this.dsSenders = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'email',
        action: 'list_composer_senders'  
      },
      reader: new Ext.data.JsonReader(
        {id : 'accounts_id'},
        ['accounts_id', 'email_address','signature']
      ),
      listeners: {
        load: this.onDsSendersLoad,
        scope: this
      },
      autoLoad: false
    });
    
    this.frmComposer = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      border: false,
      labelSeparator: ' ',
      timeout: 300,
      defaults: {
       anchor: '97%'
      },
      items: [
        this.txtAccountsId = new Ext.form.TextField({hideLabel: true, hidden: true, name: 'accounts_id'}),
        this.cboSenders = new Ext.form.ComboBox({
          fieldLabel: '<?php echo $osC_Language->get('field_from'); ?>',
          store: this.dsSenders,
          name: 'full_from',
          displayField: 'email_address',
          valueField: 'email_address',
          editable: false,
          forceSelection: true,
          triggerAction: 'all',
          mode: 'local',
          listeners: {
            select: this.onCboSendersSelect,
            scope: this
          }
        }),
        this.txtTo = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_send_to'); ?>', name: 'to',allowBlank: false}),
        this.txtCc = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_message_cc'); ?>', name: 'cc',minChars: 2}),
        this.txtBcc = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_message_bcc'); ?>', name: 'bcc',minChars: 2}),      
        this.txtSubject = new Ext.form.TextField({fieldLabel: '<?php echo $osC_Language->get('field_Subject'); ?>', name: 'subject', allowBlank: false}),  
        this.stxAttachments = new Ext.ux.form.StaticTextField({fieldLabel: '<?php echo $osC_Language->get('field_attachments'); ?>', submitValue: false}),
        this.txtContent = new Ext.form.HtmlEditor({
          hideLabel: true, 
          name: 'body',
          listeners: {
            editmodechange: this.onEditModeChange,
            scope: this
          }
        })
      ],
      tbar:[
        {text: '<?php echo $osC_Language->get('button_send'); ?>', iconCls: 'send', handler: this.sendMail, scope: this},
        '-',
        {text: '<?php echo $osC_Language->get('button_save_draft'); ?>', iconCls: 'save', handler: this.saveDraft, scope: this},
        '-',
        {text: '<?php echo $osC_Language->get('button_extra_options'); ?>', iconCls: 'options', menu: this.buildExtraOptionsMenu()}, 
        '-',
        {text: '<?php echo $osC_Language->get('button_show'); ?>', iconCls: 'show', menu: this.buildShowMenu()}, 
        '-',
        {text: '<?php echo $osC_Language->get('button_attachments'); ?>', iconCls: 'attachment', handler: this.onAttachments, scope: this}
      ]
    });
    
    return this.frmComposer;   
  },

  buildExtraOptionsMenu: function(){
    return new Ext.menu.Menu({
      defaults: {xtype: 'menucheckitem'},
      items: [
        this.chkNotification = new Ext.menu.CheckItem({text: '<?php echo $osC_Language->get('field_notification'); ?>', checked: false, checkHandler: function(item, checked) {this.notification = checked;}, scope: this}),
        '-',
        '<div class="menu-title" style="text-indent: 26px"><img class="x-menu-item-icon" src="'+ Ext.BLANK_IMAGE_URL + '" />' + '<b><?php echo $osC_Language->get('field_priority'); ?></b>' + '</div>', 
        this.chkPriority1 = new Ext.menu.CheckItem({text: '<?php echo $osC_Language->get('field_high'); ?>', checked: false, group: 'priority', checkHandler: function() {this.priority = 1;}, scope: this}),
        this.chkPriority3 = new Ext.menu.CheckItem({text: '<?php echo $osC_Language->get('field_normal'); ?>', checked: true, group: 'priority', checkHandler: function() {this.priority = 3;}, scope: this}), 
        this.chkPriority5 = new Ext.menu.CheckItem({text: '<?php echo $osC_Language->get('field_low'); ?>', checked: false, group: 'priority', checkHandler: function() {this.priority = 5;}, scope: this})
      ]
    });
  },
  
  buildShowMenu: function(){
    return new Ext.menu.Menu({
      defaults: {xtype: 'menucheckitem'},
      items: [
        this.chkShowSenders = new Ext.menu.CheckItem({text: '<?php echo $osC_Language->get('field_sender'); ?>', checked: true, checkHandler: this.onShowFieldCheck, scope: this}),
        this.chkShowCcField = new Ext.menu.CheckItem({text: '<?php echo $osC_Language->get('field_cc_field'); ?>', checked: false, checkHandler: this.onShowFieldCheck, scope: this}),
        this.chkShowBccField = new Ext.menu.CheckItem({text: '<?php echo $osC_Language->get('field_bcc_field'); ?>', checked: false, checkHandler: this.onShowFieldCheck, scope: this})
      ]
    });    
  },
  
  onCboSendersSelect: function(combo, record, index) {
    this.txtAccountsId.setValue(this.cboSenders.getStore().getAt(index).get('accounts_id'));
    var email = this.cboSenders.getStore().getAt(index).get('email_address');
    email = email.replace("\&lt\;", "<");
    email = email.replace("\&gt\;", ">");
    this.cboSenders.setValue(email);
  },
  
  onDsSendersLoad: function() {
    if (this.dsSenders.getCount() > 0) {
      if (this.accountsId == null) {
        var accountsId = this.dsSenders.getAt(0).get('accounts_id');
        var email = this.dsSenders.getAt(0).get('email_address');
        var signature = this.dsSenders.getAt(0).get('signature').replace(/\n/g, '<br />');
      } else {
        var record = this.dsSenders.getById(this.accountsId);
        
        var accountsId = record.get('accounts_id');
        var email = record.get('email_address');
        var signature = record.get('signature').replace(/\n/g, '<br />');
      }
      
      email = email.replace("\&lt\;", "<");
      email = email.replace("\&gt\;", ">");
      this.txtAccountsId.setValue(accountsId);       
      this.cboSenders.setValue(email);
      this.txtContent.setValue(this.txtContent.getValue() + '<br /><br />' + signature);
    }
  },
  
  onEditModeChange: function(htmlEditor, sourceEdit) {
    if (sourceEdit === true) {
      this.contentType = 'text';
    } else {
      this.contentType = 'html';
    }
  },
  
  showCC: function (show) {
    this.txtCc.getEl().up('.x-form-item').setDisplayed(show);
  },
  
  showBCC: function (show) {
    this.txtBcc.getEl().up('.x-form-item').setDisplayed(show);
  },
  
  showAttachments: function(show) {
    this.stxAttachments.getEl().up('.x-form-item').setDisplayed(show);
  },

  onShowFieldCheck: function (check, checked) {
    switch (check.id) {
      case this.chkShowSenders.id:
        this.cboSenders.getEl().up('.x-form-item').setDisplayed(checked);
        break;

      case this.chkShowCcField.id:
        this.showCC(checked);
        break;

      case this.chkShowBccField.id:
        this.showBCC(checked);
        break;
    }
  },
  
  show: function (id) {
    this.messagesId = id || null;
    
    this.frmComposer.form.reset();  
    
    if (id > 0) {
      this.frmComposer.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'email',
          action: 'load_draft',
          id: id
        },
        success: function(form, action) {
          var data = Ext.decode(action.response.responseText).data;

          //to
          var to = [];
          for (var i = 0 ; i < data.to.length; i++) {
            if (data.to[i].name.trim() == "") {
              to.push(data.to[i].email);
            } else {
              to.push(data.to[i].name + ' <' + data.to[i].email + '>');
            }
          }
          this.txtTo.setValue(to.join(';'));
          
          //cc
          var cc = [];
          for (var i = 0 ; i < data.cc.length; i++) {
            if (data.cc[i].name.trim() == "") {
              cc.push(data.cc[i].email);
            } else {
              cc.push(data.cc[i].name + ' <' + data.cc[i].email + '>');
            }
          }
          this.txtCc.setValue(cc.join(';'));
          this.showCC((data.cc.length > 0) ? true : false);
          
          //bcc
          var bcc = [];
          for (var i = 0 ; i < data.bcc.length; i++) {
            if (data.bcc[i].name.trim() == "") {
              bcc.push(data.bcc[i].email);
            } else {
              bcc.push(data.bcc[i].name + ' <' + data.bcc[i].email + '>');
            }
          }
          this.txtBcc.setValue(bcc.join(';'));
          this.showBCC((data.bcc.length > 0) ? true : false);
          
          data.attachments = data.attachments || [];
          if (data.attachments.length > 0) {
            var attachments = [];
            
            for (var i = 0; i < data.attachments.length; i++) {
              attachments.push(data.attachments[i].name);
            }
           
            this.attachments = attachments;
            this.stxAttachments.setValue(attachments.join(';'));
          } 
          this.showAttachments((data.attachments.length > 0) ? true : false);
          
          switch(data.priority) {
            case '1':
              this.chkPriority1.setChecked(true);
              break;
            case '3':
              this.chkPriority3.setChecked(true);
              break;
            case '5':
              this.chkPriority5.setChecked(true);
              break;
          }
          
          this.chkNotification.setChecked((data.notification == "true") ? true : false);
          
          Toc.email.EmailComposerDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.email.EmailComposerDialog.superclass.show.call(this);
      
      this.showCC(false);
      this.showBCC(false);
      this.showAttachments(false);
    }
    
    this.dsSenders.load();
  },
  
  close: function() {
    if (this.needSaveDraft == true) {
      this.body.mask(TocLanguage.loadingText); 
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        '<?php echo $osC_Language->get('msgSaveDraftsConfirm'); ?>',
        function(btn) {
          if (btn == 'yes') {
            this.saveDraft();
          } else {
            Toc.email.EmailComposerDialog.superclass.close.call(this);
          }
        }, 
        this
      );
    }
  },
  
  onAttachments: function(){
   var dlg = this.owner.createAttachmentsDialog();
   
    dlg.on('close', function() {
      this.attachments = dlg.getAttachments();
      this.stxAttachments.setValue(this.attachments.join(';'));
     
      this.showAttachments((this.attachments.length > 0) ? true : false);
    }, this);

    dlg.show();  
  },
  
  saveDraft: function() {
    this.frmComposer.form.submit({
      params: {
        module: 'email',
        action: 'save_draft',
        id: this.messagesId,
        priority: this.priority,
        content_type: this.contentType,
        notification: this.notification,
        attachments: this.attachments.join(';')
      },
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.needSaveDraft = false;
        
        this.fireEvent('saveSuccess', action.result.feedback);
        
        Toc.email.EmailComposerDialog.superclass.close.call(this); 
      },    
      failure: function(form, action) {
        this.body.unmask();

        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });      
  },
  
  sendMail: function(){
    this.frmComposer.form.submit({
      params: {
        module: 'email',
        action: 'send_mail',
        id: this.messagesId,
        priority: this.priority,
        content_type: this.contentType,
        notification: this.notification,
        attachments: this.attachments.join(';')
      },
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.needSaveDraft = false;
        
        this.fireEvent('saveSuccess', action.result.feedback);
        
        Toc.email.EmailComposerDialog.superclass.close.call(this);
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });      
  },
  
  clearAttachmentsCache: function(){
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'email',
        action: 'clear_attachments_cache'
      },
      scope: this
    });   
  },

  setAccountsId: function(accountsId) {
    this.accountsId = accountsId;
  },
    
  setTo: function(to) {
    this.txtTo.setValue(to);
  },
  
  setCc: function(cc) {
    this.txtCc.setValue(cc);
    this.showCC(true);
  },
  
  setBody: function(body) {
    this.txtContent.setValue(body);
  },
  
  setSubject: function(subject) {
    this.txtSubject.setValue(subject);
  },
  
  setAttachments: function(attachments) {
    this.attachments = attachments;
    this.stxAttachments.setValue(this.attachments.join(';'));
    this.showAttachments(true);
  }
});