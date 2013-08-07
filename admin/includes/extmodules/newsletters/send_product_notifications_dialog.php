<?php
/*
  $Id: send_product_notifications_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.newsletters.SendProductNotificationsDialog = function(config) {

  config = config || {};
  
  config.id = 'send-product-notifications-dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.layout = 'fit';
  config.width = 650;
  config.height = 370;
  config.modal = true;
  config.items = this.buildForm();  
    
  config.buttons = [
    {
      text: "<?php echo $osC_Language->get('newsletter_product_notifications_button_global');?>",
      id: 'btn-send-product-notifications-global',
      handler: this.onAction,
      scope: this
    },  
    {
      text: '<?php echo $osC_Language->get('button_ok') ?>',
      id: 'btn-send-product-notifications',
      handler: this.onAction,
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
  
  Toc.newsletters.SendProductNotificationsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.newsletters.SendProductNotificationsDialog, Ext.Window, {
  
  show: function (newslettersId) {
    this.newslettersId = newslettersId;
    Toc.newsletters.SendProductNotificationsDialog.superclass.show.call(this);
  },
  
  onAction: function(btn) {
    text = btn.getText();
    
    if (text == '<?php echo $osC_Language->get('newsletter_product_notifications_button_global') ?>') {
      this.global = true;
      this.showConfirmation();
    } else if (text == '<?php echo $osC_Language->get('button_ok') ?>') {
      this.global = false;
      this.showConfirmation();
    } else {
      this.sendEmails();
    }
  },
    
  showConfirmation: function() {
    var batch = this.selProducts.getValue();
    
    if ( this.global == false && Ext.isEmpty(batch) ) {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
      return;
    }  
  
    this.frmNotification.el.mask(TocLanguage.formSubmitWaitMsg, 'x-mask-loading');
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'newsletters',
        action: 'get_product_notifications_confirmation',
        newsletters_id: this.newslettersId,
        batch: batch,
        global: this.global
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          this.frmNotification.removeAll();
          
          this.frmNotification.body.update(result.confirmation);
          
          Ext.getCmp('btn-send-product-notifications-global').hide();
          if (result.execute == true) {
            Ext.getCmp('btn-send-product-notifications').setText('<?php echo $osC_Language->get('button_send') ?>');
          } else {
            Ext.getCmp('btn-send-product-notifications').hide();
          }
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
        
        this.frmNotification.el.unmask();
      },
      scope: this
    }); 
  },  
  
  sendEmails: function() {
    var batch = this.selProducts.getValue();
  
    this.frmNotification.el.mask('<?php echo $osC_Language->get('sending_please_wait') ?>', 'x-mask-loading');
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'newsletters',
        action: 'send_product_notifications',
        newsletters_id: this.newslettersId,
        batch: batch,
        global: this.global
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
         this.fireEvent('sendSuccess', result.feedback);
         this.close();        
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
        
        this.frmNotification.el.unmask();
      },
      scope: this
    }); 
  },
      
  buildForm: function() {
    this.selProducts = new Ext.ux.ItemSelector({
      name: "products",
      hideLabel: true,
      msWidth: 290,
      msHeight: 260,
      dataFields: ["id", "text"],
      toData: [],
      valueField: "id",
      displayField: "text",
      imagePath: "images/",
      toLegend: "<?php echo $osC_Language->get('newsletter_product_notifications_table_heading_selected_products');?>",
      fromLegend: "<?php echo $osC_Language->get('newsletter_product_notifications_table_heading_products');?>",
      fromStore: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'newsletters', 
          action: 'get_products'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          fields: ['id', 'text']
        }),
        autoLoad: true                                                                                  
      })
    });
        
    this.frmNotification = new Ext.form.FormPanel({ 
      border: false,
      bodyStyle: 'padding:10px;',
      items: this.selProducts
    });
    
    return this.frmNotification;
  }
});