<?php
/*
  $Id: guest_book_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.guest_book.GuestBookDialog = function(config) {
  config = config || {}; 
  
  config.id = 'guest_book-dialog';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.modal = true;
  config.width = 500;
  config.iconCls = 'icon-guest_book-win';
  config.items = this.buildForm();
    
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function () {
        this.submitForm();
      },
      scope: this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
  
  Toc.guest_book.GuestBookDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.guest_book.GuestBookDialog, Ext.Window, {
  show: function (guestBooksId) {
    var guestBooksId = guestBooksId || null;
    
    this.frmGuestBook.form.baseParams['guest_books_id'] = guestBooksId;

    if (guestBooksId > 0) {
      this.frmGuestBook.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'guest_book',
          action: 'load_guest_book'
        },
        success: function(form, action) {
          Toc.guest_book.GuestBookDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.guest_book.GuestBookDialog.superclass.show.call(this);
    }
  },

  buildForm: function() {
    dsLanguages = new Ext.data.Store({
      url:Toc.CONF.CONN_URL,
      baseParams: {
        module: 'guest_book',
        action: 'get_languages'
      },
      reader: new Ext.data.JsonReader({
        fields: ['id', 'text'],
        root: Toc.CONF.JSON_READER_ROOT
      }),
      autoLoad: true,
      listeners: {
        load: function() {this.cboLanguages.setValue('<?php echo osC_Language_Admin::getID(DEFAULT_LANGUAGE); ?>');},
        scope: this
      }
    });
    
    this.frmGuestBook = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        action: 'save_guest_book',
        module: 'guest_book',
      }, 
      labelSeparator: ': ',
      style: 'padding: 10px;',
      border: false,
      defaults: {
        anchor: '96%'
      },
      items: [
        {xtype:'textfield', fieldLabel: '<?php echo $osC_Language->get('field_title'); ?>', name: 'title', allowBlank: false},
        this.cboLanguages = new Ext.form.ComboBox({fieldLabel: '<?php echo $osC_Language->get('field_language'); ?>', store: dsLanguages, name: 'language', hiddenName: 'languages_id', displayField: 'text', valueField: 'id', triggerAction: 'all', editable: false, forceSelection: true}),
        {xtype:'textfield', fieldLabel: '<?php echo $osC_Language->get('field_email'); ?>', name: 'email'},
        {xtype:'textfield', fieldLabel: '<?php echo $osC_Language->get('field_url'); ?>', name: 'url'},
        {xtype:'textarea', fieldLabel: '<?php echo $osC_Language->get('field_content'); ?>', name: 'content', height: 200, allowBlank: false},
        {
          layout: 'column',
          border: false,
          items:[{
            layout: 'form',
            labelSeparator: ' ',
            border: false,
            items:[{fieldLabel: '&nbsp;<?php echo $osC_Language->get('field_status'); ?>', xtype:'radio', name: 'guest_books_status', boxLabel: '<?php echo $osC_Language->get('cbo_field_abled'); ?>', xtype:'radio', inputValue: '1'}]
          },{
            layout: 'form',
            border: false,
            items: [{fieldLabel: '&nbsp;<?php echo $osC_Language->get('cbo_field_disabled'); ?>', boxLabel: '<?php echo $osC_Language->get('cbo_field_disabled'); ?>', xtype:'radio', name: 'guest_books_status', hideLabel: true, inputValue: '0', checked: true}]
          }]
        }
      ]                          
    });
    
    return this.frmGuestBook;
  },
  
  submitForm : function() {
    this.frmGuestBook.form.submit({
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
