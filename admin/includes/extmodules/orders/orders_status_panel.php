<?php
/*
  $Id:orders_status_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.orders.OrdersStatusPanel = function(config) {

  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_status_history'); ?>';
  config.layout = 'border';
  
  config.items = this.buildForm(config.ordersId);  
    
  Toc.orders.OrdersStatusPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.orders.OrdersStatusPanel, Ext.Panel, {
  
  getOrdersStatusGrid: function(ordersId) {
    grdOrdersStatus = new Ext.grid.GridPanel({
      region: 'center',
      ds: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'orders',
          action: 'list_orders_status',
          orders_id: ordersId     
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          id: 'orders_status_history_id'
        },[
          'orders_status_history_id',
          'date_added',
          'status',
          'comments',
          'customer_notified'
        ]),
        autoLoad: true
      }),
      cm: new Ext.grid.ColumnModel([
        {header: '<?php echo $osC_Language->get('table_heading_date_added');?>', dataIndex: 'date_added', width: 120, align: 'center'},
        {header: '<?php echo $osC_Language->get('table_heading_status');?>', dataIndex: 'status', width: 120, align: 'center'},
        {id: 'comments', header: '<?php echo $osC_Language->get('table_heading_comments');?>', dataIndex: 'comments'},
        {header: '<?php echo $osC_Language->get('table_heading_customer_notified');?>', dataIndex: 'customer_notified', width: 120, align: 'center'}
      ]),
      autoExpandColumn: 'comments',
      border: false
    });
    
    return grdOrdersStatus;
  },
  
  getOrdersStatusForm: function(ordersId) {
    var dsStatus = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'orders', 
        action: 'get_status'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['status_id', 'status_name']
      }),
      autoLoad: true                                                                                    
    });
      
    this.frmOrdersStatus = new Ext.form.FormPanel({
      region: 'south',
      height: 200, 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'orders',
        action: 'update_orders_status',
        orders_id: ordersId
      }, 
      labelWidth: 200,
      border: false,
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        {xtype: 'combo', fieldLabel: '<?php echo $osC_Language->get('field_status'); ?>', store: dsStatus, displayField: 'status_name', valueField: 'status_id', hiddenName: 'status', editable: false, triggerAction: 'all', allowBlank: false},                        
        {xtype: 'textarea', fieldLabel: '<?php echo $osC_Language->get('field_add_comment'); ?>', name: 'comment', anchor: '97%'},
        {xtype: 'checkbox', fieldLabel: '<?php echo $osC_Language->get('field_notify_customer'); ?>', name: 'notify_customer'},
        {xtype: 'checkbox', fieldLabel: '<?php echo $osC_Language->get('field_notify_customer_with_comments'); ?>', name: 'notify_with_comments'}
      ],
      buttons: [{text: '<?php echo $osC_Language->get('button_update');?>', iconCls:'refresh', handler: this.submitForm, scope: this}]
    });
    
    return this.frmOrdersStatus;
  },
  
  buildForm: function(ordersId){
    this.grdOrdersStatus = this.getOrdersStatusGrid(ordersId);
    this.frmOrdersStatus = this.getOrdersStatusForm(ordersId);
    
    return [this.grdOrdersStatus, this.frmOrdersStatus];
  },
  
  submitForm : function() {
    this.frmOrdersStatus.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action){
         this.grdOrdersStatus.getStore().reload(); 
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