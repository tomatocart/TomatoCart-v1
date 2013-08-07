<?php
/*
  $Id: invoices_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.invoices.InvoicesDialog = function(config) {
  
  config = config || {};
  
  config.id = 'invoices-dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.width = 700;
  config.height = 480;
  config.layout = 'fit';
  config.modal = true;
  config.iconCls = 'icon-invoices-win';
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
    '</table>'                    
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
    
  Toc.invoices.InvoicesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.invoices.InvoicesDialog, Ext.Window, {

  loadSummaryPanel: function(ordersId){
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'invoices',
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
  
  buildForm: function(ordersId) {
    this.pnlSummary = new Ext.Panel({
      title: '<?php echo $osC_Language->get('section_summary'); ?>',
      style: 'padding: 10px'
    });

    this.grdProducts = new Toc.invoices.InvoicesProductsGrid({ordersId: ordersId});
    this.grdTransactionHistory = new Toc.invoices.InvoicesTransactionGrid({ordersId: ordersId}); 
    this.pnlInvoicesStatus = new Toc.invoices.InvoicesStatusPanel({ordersId: ordersId, owner: this.owner});
    this.pnlRefunds = new Toc.invoices.RefundsGrid({ordersId: ordersId, owner: this.owner});
    this.pnlReturns = new Toc.invoices.ReturnsGrid({ordersId: ordersId, owner: this.owner});
    
    this.tabInvoices = new Ext.TabPanel({
      activeTab: 0,
      defaults:{autoScroll: true},
      items: [this.pnlSummary, this.grdProducts, this.grdTransactionHistory, this.pnlInvoicesStatus, this.pnlRefunds, this.pnlReturns]
    });
    
    this.loadSummaryPanel(ordersId);
    
    return this.tabInvoices;    
  }
});