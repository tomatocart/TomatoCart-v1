<?php
/*
  $Id: orders_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.orders.OrdersDialog = function(config) {
  
  config = config || {};
  
  config.id = 'orders-dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.width = 700;
  config.height = 520;
  config.layout = 'fit';
  config.modal = true;
  config.iconCls = 'icon-orders-win';
  config.items = this.buildForm(config.ordersId);
  
  config.tplSummary = new Ext.Template(
    '<table width="100%">',
      '<tr>',
        '<td width= "33%" valign="top">',
          '<h1><img src= "templates/default/images/icons/16x16/personal.png" /><span style= "margin-left:4px;"><?php echo $osC_Language->get('subsection_customer'); ?></span></h1>',
          '{customer}',
        '</td>',
       
        '<td width= "33%" valign="top">',
          '<h1><img src= "templates/default/images/icons/16x16/home.png" /><span style= "margin-left:4px;"><?php echo $osC_Language->get('subsection_shipping_address'); ?></span></h1>', 
          '{shippingAddress}', 
        '</td>',
       
        '<td valign="top">',
          '<h1><img src= "templates/default/images/icons/16x16/bill.png" /><span style= "margin-left:4px;"><?php echo $osC_Language->get('subsection_billing_address'); ?></span></h1>',
          '{billingAddress}',
        '</td>',
      '</tr>',
      '<tr>',    
        '<td width= "33%" valign="top">',
          '<h1><img src= "templates/default/images/icons/16x16/payment.png" /><span style= "margin-left:4px;"><?php echo $osC_Language->get('subsection_payment_method'); ?></span></h1>',
          '{paymentMethod}',
        '</td>',
        '<td width= "33%" valign="top">',
          '<h1><img src= "templates/default/images/icons/16x16/history.png" /><span style= "margin-left:4px;"><?php echo $osC_Language->get('subsection_status'); ?></h1>',
          '{status}',
        '</td>',
        '<td valign="top">',
          '<h1><img src= "templates/default/images/icons/16x16/calculator.png" /><span style= "margin-left:4px;"><?php echo $osC_Language->get('subsection_total'); ?></span></h1>',
          '{total}',
        '</td>',
      '</tr>',
    '</table>',
    '<div>',
      '<fieldset style="padding:5px;border: 1px solid #DDDDDD; float:left;height:110px;width:310px;">',
        '<legend style="color:#0069BF; font-weight:bolder;">',
          '<?php echo $osC_Language->get('subsection_customers_comments');?>',
        '</legend>',
        '<p>',
          '{customers_comment}',
        '</p>',
      '</fieldset>',
      '<fieldset style="padding:5px;border: 1px solid #DDDDDD; float:right;height:110px;width:310px;">',
         '<legend style="color:#0069BF; font-weight:bolder;">',
           '<?php echo $osC_Language->get('subsection_internal_comments');?>',
         '</legend>', 
         '<textarea id="admin-comment" class="x-form-textarea x-form-field" style="width: 295px; height: 85px">',
          '{admin_comment}',
         '</textarea>',
       '</fieldset>',
    '</div>'
  );
    
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];
    
  this.addEvents({'saveSuccess' : true});
  
  Toc.orders.OrdersDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.orders.OrdersDialog, Ext.Window, {

  loadSummaryPanel: function(ordersId){
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'orders',
        action: 'load_summary_data',
        orders_id: ordersId        
      },
      success: function(response) {
        var data = Ext.decode(response.responseText);
        var html = this.tplSummary.apply(data);
        this.pnlSummary.body.update(html);
      },
      scope: this
    });
  },
  
  updateComment: function(ordersId) {
    var adminComment = Ext.get('admin-comment').getValue();
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'orders',
        action: 'update_comment',
        admin_comment: adminComment,
        orders_id: ordersId     
      },
      callback: function(options, success, response) {
        result = Ext.decode(response.responseText);
              
        if (result.success == true) {
          Ext.MessageBox.alert(TocLanguage.msgSuccessTitle, result.feedback);
        } else {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
      },
      scope: this
    });
  },
  
  buildForm: function(ordersId) {
    this.pnlSummary = new Ext.Panel({
      title: '<?php echo $osC_Language->get('section_summary'); ?>',
      style: 'padding: 10px',
      buttons: [{
	      text: '<?php echo $osC_Language->get('button_update'); ?>',
	      handler: function() { 
	        this.updateComment(ordersId);
	      },
	      scope: this
      }]
    });
    this.grdProducts = new Toc.orders.OrdersProductsGrid({ordersId: ordersId});
    this.grdTransactionHistory = new Toc.orders.OrdersTransactionGrid({ordersId: ordersId}); 
    this.pnlOrdersStatus = new Toc.orders.OrdersStatusPanel({ordersId: ordersId, owner: this.owner});
    
    this.pnlOrdersStatus.on('saveSuccess', function() {
      this.fireEvent('saveSuccess');
    }, this);
    
    this.pnlRefunds = new Toc.orders.RefundsGrid({ordersId: ordersId, owner: this.owner});
    this.pnlReturns = new Toc.orders.ReturnsGrid({ordersId: ordersId, owner: this.owner});
    
    this.tabOrders = new Ext.TabPanel({
      activeTab: 0,
      defaults:{autoScroll: true},
      items: [this.pnlSummary, this.grdProducts, this.grdTransactionHistory, this.pnlOrdersStatus, this.pnlRefunds, this.pnlReturns]
    });
    
    this.loadSummaryPanel(ordersId);
    
    return this.tabOrders;    
  }
});