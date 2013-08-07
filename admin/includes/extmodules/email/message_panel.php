<?php
/*
  $Id: message_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.email.MessagePanel = function(config){
  config = config || {};
  
  config.border = false;
  config.region = 'center';
  config.autoScroll = true;
  
  config.tbar = [
    this.btnReply = new Ext.Button({iconCls: 'reply', text: '<?php echo $osC_Language->get('button_reply'); ?>', handler: this.onReply, scope: this}),
    '-',
    this.btnReplyAll = new Ext.Button({iconCls: 'replyAll', text: '<?php echo $osC_Language->get('button_replyAll'); ?>', handler: this.onReplyAll, scope: this}),
    '-',  
    this.btnForward = new Ext.Button({iconCls: 'forward', text: '<?php echo $osC_Language->get('button_forward'); ?>', handler: this.onForward, scope: this}),
    '-'
  ];

  if (config.type != 'dialog') {
    this.btnQuickCreate = new Ext.Button({
      disabled:'true',
      text: '<?php echo $osC_Language->get('button_quick_create'); ?>',
      iconCls: 'add',
      menu: {
        xtype: 'menu',
        defaults: {xtype: 'button'},
        items: [
          {
            text: '<?php echo $osC_Language->get('button_quick_create_customer'); ?>',
            handler: function(){
              TocDesktop.callModuleFunc('customers', 'createCustomersDialog', function(dlg){
                dlg.show();
              });
            },
            iconCls: 'icon-customers-win',
            scope: this           
          },
          {
            text: '<?php echo $osC_Language->get('button_quick_create_order'); ?>',
            handler: function(){
              TocDesktop.callModuleFunc('orders', 'createNewOrderDialog', function(dlg) {
                dlg.show();
              });
            },
            iconCls: 'icon-orders-win',
            scope: this
          },
          {
            text: '<?php echo $osC_Language->get('button_quick_create_product'); ?>',
            handler: function(){
              TocDesktop.callModuleFunc('products', 'createProductDialog', function(dlg){
                dlg.show();
              });
            },
            iconCls: 'icon-products-win',
            scope: this         
          }
        ]
      }  
    });
    
    config.tbar.push(this.btnDelete = new Ext.Button({disabled: true, text: TocLanguage.btnDelete, iconCls: 'remove', handler: this.onDelete, scope: this})); 
    config.tbar.push('-');
    config.tbar.push(this.btnQuickCreate);
    config.tbar.push('-');
  }
  config.tbar.push(this.btnPrint = new Ext.Button({disabled: true, iconCls: 'print',text: TocLanguage.btnPrint, handler: this.print, scope: this}));
  
  this.bodyId = Ext.id();
  this.accountsId = null;
  this.messagesId = null;
  this.attachmentsId = Ext.id();
  this.data = null;

  this.template = new Ext.XTemplate(
    '<div class="message-header">',
      '<table class="message-header-table" cellspacing="5" cellpadding="0" width="100%" style="background: #ECECEC; border-bottom: 1px solid #A0A0A0">',
        '<tr>',
          '<td valign="top" width="80"><b><?php echo $osC_Language->get('field_Subject'); ?></b></td>',
          '<td>{subject}<span style="float: right"><b>{date}</b></span></td>',
        '</tr>',
        '<tr>',
          '<td><b><?php echo $osC_Language->get('field_from'); ?></b></td>',      
          '<td>',
            '<tpl if="from.length &gt; 0">{from} &lt;{sender}&gt;; </tpl>',
            '<tpl if="from.length == 0">{sender};</tpl>',
          '</td>',
        '</tr>',
        '<tr>',
          '<td><b><?php echo $osC_Language->get('field_to'); ?></b></td>',
          '<td>',
            '<tpl for="to">',
                '<tpl if="name.length &gt; 0">{name} &lt;{email}&gt;; </tpl>',
                '<tpl if="name.length == 0">{email};</tpl>',
            '</tpl>',
          '</td>',
        '</tr>',
        '<tpl if="cc.length">',
          '<tr>',
            '<td valign="top"><b><?php echo $osC_Language->get('field_message_cc'); ?></b></td>',
            '<td>',
              '<tpl for="cc">',
                '<tpl if="name.length &gt; 0">{name} &lt;{email}&gt;; </tpl>',
                '<tpl if="name.length == 0">{email};</tpl>',
              '</tpl>',
            '</td>',
          '</tr>',
        '</tpl>',
        '<tpl if="bcc.length">',
          '<tr>',
            '<td valign="top"><b><?php echo $osC_Language->get('field_message_bcc'); ?></b></td>',
            '<td>',
              '<tpl for="bcc">',
                '<tpl if="name.length &gt; 0">{name} &lt;{email}&gt;; </tpl>',
                '<tpl if="name.length == 0">{email};</tpl>',
              '</tpl>',
            '</td>',
          '</tr>',
        '</tpl>',
        '<tpl if="attachments.length">',
          '<tr>',
            '<td valign="top"><b><?php echo $osC_Language->get('field_attachments'); ?></b></td>',
            '<td id="' + this.attachmentsId + '">',
              '<tpl for="attachments">',
                '<a class="filetype-link filetype-{extension}" id="' + this.attachmentsId + '_{index}" href="#">{name} ({human_size})</a>&nbsp;',
              '</tpl>',
            '</td>',
          '</tr>',
        '</tpl>',
      '</table>',
    '</div>',
    '<p id="' + this.bodyId + '">{body}</p>'
  ); 
  this.template.compile();
  
  this.addEvents({attachmentClicked: true});
  
  Toc.email.MessagePanel.superclass.constructor.call(this, config);
}

Ext.extend(Toc.email.MessagePanel, Ext.Panel, {
  
  getMessagesId: function() {
    return this.messagesId;
  },
    
  setData: function(data) {
    this.data = data;
  },
  
  getData: function() {
    return this.data;
  },
  
  reload: function() {
    this.loadMessage(this.messagesId, this.foldersId, this.accountsId, this.fetchTime);
  },
  
  loadMessage: function(id, accountsId, foldersId, fetchTime) {   
    this.messagesId = id;
    this.foldersId = foldersId;
    this.accountsId = accountsId;
    this.fetchTime = fetchTime;
    
    this.el.mask(TocLanguage.loadingText);  
       
    Ext.Ajax.timeout = 600000;   
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'email',
        action: 'load_message',
        id: id,
        accounts_id: accountsId,
        folders_id: foldersId,
        fetch_time: fetchTime
      },
      scope: this,
      callback: function(options, success, response) {
        this.el.unmask();
                 
        var result = Ext.decode(response.responseText);
        if (result.success  == true) {
          var data = result.data;
          
          if (data.attachments == null) {
            data.attachments = [];
          }
          
          this.setData(data);
          this.template.overwrite(this.body, data);
          
          if (data.attachments.length) {
            this.attachmentsEl = Ext.get(this.attachmentsId);     
            this.attachmentsEl.on('click', this.openAttachment, this);
          }
          
          this.btnReplyAll.setDisabled(false);
          this.btnReply.setDisabled(false);
          this.btnForward.setDisabled(false);
          if (this.btnDelete) {
	          this.btnDelete.setDisabled(false);
          }
          if (this.btnQuickCreate) {
            this.btnQuickCreate.setDisabled(false);
          }
          this.btnPrint.setDisabled(false);
          
          this.fireEvent('load', result.unseen);
        }       
      }
    });
  },
  
  reset: function() {
    this.data = false;
    
    this.btnReplyAll.setDisabled(true);
    this.btnReply.setDisabled(true);
    this.btnForward.setDisabled(true);
    this.btnDelete.setDisabled(true);
    this.btnPrint.setDisabled(true);
    this.btnQuickCreate.setDisabled(true);
    
    if (this.messageBodyEl) {
      this.messageBodyEl.removeAllListeners();
    }
    
    if(this.attachmentsEl){
      this.attachmentsEl.removeAllListeners();
    }
    
    this.body.update('');
  },
  
  openAttachment: function(e, target) {
    if (target.id.substr(0, this.attachmentsId.length) == this.attachmentsId) {
      var attachment_no = target.id.substr(this.attachmentsId.length + 1);
      var attachment = this.data.attachments[attachment_no]; 
     
      var href = Toc.CONF.CONN_URL +
        '?module=email&action=download_attachment&accounts_id='+this.accountsId+
        '&filename='+ attachment.name +
        '&number=' + attachment.number + 
        '&id=' + this.messagesId + 
        '&fetch_time=' + this.fetchTime;
       
      document.location.href = href;
    }
  }, 
  
  onReply: function(){
    var dlg = this.owner.createEmailComposerDialog(this.owner);
    dlg.setAccountsId(this.accountsId);
    dlg.show();
    
    dlg.setSubject('<?php echo $osC_Language->get('leading_string_reply'); ?>' + this.data.subject);
    dlg.setBody('<?php echo $osC_Language->get('leading_string_origial_message'); ?>' + this.data.body);  
  },
  
  onReplyAll: function(){
    var dlg = this.owner.createEmailComposerDialog(this.owner);
    dlg.setAccountsId(this.accountsId);
    dlg.show();
    
    var to = [];
    to.push((this.data.from == '') ? this.data.sender : (this.data.from + ' <' + this.data.sender + '>'));
    for (var i = 0; i < this.data.to.length; i++) {
      if (this.data.sender != this.data.to[i].email) {
        if ((this.data.to[i].name.trim() == "")) {
          to.push(this.data.to[i].email);
        } else {
          to.push(this.data.to[i].name + ' <' + this.data.to[i].email + '>');
        }
      }
    }    
    
    var cc = [];
    for (var i = 0; i < this.data.cc.length; i++) {
      if (this.data.sender != this.data.to[i].email) {
        if (this.data.cc[i].name.trim() == "") {
          cc.push(this.data.cc[i].email);
        } else {
          cc.push(this.data.cc[i].name + ' <' + this.data.cc[i].email + '>');
        }
      }
    }  
    
    dlg.setTo(to.join(';'));
    
    if (cc.length > 0) {
      dlg.setCc(cc.join(';'));
    }
    
    dlg.setSubject('<?php echo $osC_Language->get('leading_string_reply'); ?>' + this.data.subject);
    dlg.setBody('<?php echo $osC_Language->get('leading_string_origial_message'); ?>' + this.data.body);  
  },
  
  onForward: function(){
    var dlg = this.owner.createEmailComposerDialog(this.owner);
    dlg.show();  
    
    var attachments = [];
    for (var i = 0; i < this.data.attachments.length; i++) {
      attachments.push(this.data.attachments[i].name);
    }
    
    if (attachments.length > 0) {
	    dlg.setAttachments(attachments);
    }

    dlg.setSubject('<?php echo $osC_Language->get('leading_string_forward'); ?>' + this.data.subject);
    dlg.setBody(this.data.body);
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'email',
        action: 'forward_attachments',
        id: this.messagesId         
      }
    });
  },
  
  onDelete: function() {
    var params = {
        module: 'email',
        action: 'delete_message',
        accounts_id: this.accountsId,
        id: this.messagesId
    };
           
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          this.body.mask(TocLanguage.loadingText);
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: params,
            callback: function(options, success, response) {
              this.body.unmask();
              var result = Ext.decode(response.responseText);
                
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                this.ownerCt.ownerCt.dsMessages.reload();
                  
                this.reset();
              }else{
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });   
        }
      }, this);
  },
  
  print: function(){
      var params = '?module=email&action=print_message&accounts_id=' + this.accountsId + '&fetch_time=' + this.fetchTime + '&id=' + this.messagesId + '&token=' + token;
      window.open('<?php echo osc_href_link_admin(FILENAME_JSON); ?>' + params); 
  }
  
});