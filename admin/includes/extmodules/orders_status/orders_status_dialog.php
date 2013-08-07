<?php
/*
  $Id: orders_status_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.orders_status.OrdersStatusDialog = function(config) {
  
  config = config || {};
  
  config.id = 'orders_status-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_order_status'); ?>';
  config.layout = 'fit';
  config.width = 450;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-orders_status-win';
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
  
  Toc.orders_status.OrdersStatusDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.orders_status.OrdersStatusDialog, Ext.Window, {
  
  show: function(id) {
    var ordersStatusId = id || null;      
    
    this.frmOrdersStatus.form.reset();  
    this.frmOrdersStatus.form.baseParams['orders_status_id'] = ordersStatusId;

    if (ordersStatusId > 0) {
      this.frmOrdersStatus.load({
        url: Toc.CONF.CONN_URL,
        params: {
          action: 'load_orders_status',
          orders_status_id: ordersStatusId
        },
        success: function(form, action) {
          if (action.result.data['default'] == '1') {
            Ext.getCmp('default_orders_status').disable();
          }
          
          Toc.orders_status.OrdersStatusDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        },
        scope: this       
      });
    } else {   
      Toc.orders_status.OrdersStatusDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function() {
    this.frmOrdersStatus = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'orders_status',
        action: 'save_orders_status'
      }, 
      autoHeight: true,
      defaults: {
        anchor: '97%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 150
    });
    
    <?php
      $i = 1; 
      foreach ( $osC_Language->getAll() as $l ) {
        echo 'var txtLang' . $l['id'] . ' = new Ext.form.TextField({name: \'name[' . $l['id'] . ']\',';
        
        if ($i != 1 ) 
          echo ' fieldLabel:"&nbsp;", ';
        else
          echo ' fieldLabel:"' . $osC_Language->get('field_name') . '", ';
          
        echo 'allowBlank: false,';
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;'});";
        
        echo 'this.frmOrdersStatus.add(txtLang' . $l['id'] . ');';
        $i++;
      }
    ?>
    this.frmOrdersStatus.add({xtype: 'checkbox', name: 'default', id:'default_orders_status', fieldLabel: '<?php echo $osC_Language->get('field_set_as_default'); ?>', anchor:''});
    this.frmOrdersStatus.add({xtype: 'checkbox', name: 'public_flag', fieldLabel: '<?php echo $osC_Language->get('field_public_flag'); ?>', anchor:''});
    this.frmOrdersStatus.add({xtype: 'checkbox', name: 'downloads_flag', fieldLabel: '<?php echo $osC_Language->get('field_downloads_flag'); ?>', anchor:''});
    this.frmOrdersStatus.add({xtype: 'checkbox', name: 'returns_flag', fieldLabel: '<?php echo $osC_Language->get('field_returns_flag'); ?>', anchor:''});
    this.frmOrdersStatus.add({xtype: 'checkbox', name: 'gift_certificates_flag', fieldLabel: '<?php echo $osC_Language->get('field_gift_certificates_flag'); ?>', anchor:''});
    
    return this.frmOrdersStatus;
  },

  submitForm: function() {
    this.frmOrdersStatus.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if(action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });   
  }
});