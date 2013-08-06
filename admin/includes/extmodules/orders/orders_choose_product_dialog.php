<?php
/*
  $Id: orders_choose_product_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.orders.OrdersChooseProductDialog = function (config) {
  
  config = config || {};
  
  config.id = 'orders-choose-product-win';
  config.title = '<?php echo $osC_Language->get('heading_title_choose_product_title'); ?>';
  config.layout = 'fit';
  config.width = 700;
  config.height = 400;
  config.modal = true;
  config.border = false;
  config.iconCls = 'icon-products-win';
  config.items = this.buildPanel(config.ordersId);
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
  
  this.addEvents({'saveSuccess': true});  
  
  Toc.orders.OrdersChooseProductDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.orders.OrdersChooseProductDialog, Ext.Window, {

  buildPanel: function (ordersId) {
    var dsProducts = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'orders',
        action: 'list_choose_products',
        orders_id: ordersId
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'products_id'
      }, [
        'products_id',
        'products_name',
        'products_type',
        'products_sku',
        'products_price',
        'products_quantity',
        'new_qty',
        'has_variants'
      ]),
      autoLoad: true
    });

    var tpl = new Ext.XTemplate(
      '<table cellspacing="0" cellpadding="0" border="0" class="product-dataview">',
        '<tr>',
          '<th><?php echo $osC_Language->get("table_heading_products");?></th>',
          '<th align="center"><?php echo $osC_Language->get("table_heading_product_sku");?></th>',
          '<th align="center"><?php echo $osC_Language->get("table_heading_price");?></th>',
          '<th align="center"><?php echo $osC_Language->get("table_heading_quantity_in_stock");?></th>',
          '<th align="center"><?php echo $osC_Language->get("table_heading_quantity");?></th>',
          '<th>&nbsp;</th>',
        '</tr>',
        '<tpl for=".">',
          '<tr>',
            '<tpl if="has_variants == true">',
              '<td colspan="5">{products_name}</td>',
              '<td><a href = "#"></a></td>',
            '</tpl>',
            '<tpl if="has_variants == false">',
              '<td>{products_name}</td>',
              '<td>{products_sku}</td>',
              '<td>{products_price}</td>',
              '<td align="center">{products_quantity}</td>',
              '<td align="center"><input type="text" value="{new_qty}" <tpl if="products_type == <?php echo PRODUCT_TYPE_GIFT_CERTIFICATE; ?>">disabled="disabled"</tpl> id="{products_id}_qty" size="5" class="x-form-text x-form-field" style="width: 40px" /></td>',
              '<td><a href = "#"><img border="0" title="<?php echo $osC_Language->get('button_add_product'); ?>" alt="<?php echo $osC_Language->get('button_add_product'); ?>" src="templates/default/images/icons/16x16/add.png" /></a></td>',
            '</tpl>',
          '</tr>',
        '</tpl>',
      '</table>');
        
    var dvProducts = new Ext.DataView({
      autoWidth: true,
      autoHeight: true,
      store: dsProducts,
      border: false,
      tpl: tpl,
      itemSelector: 'a',
      multiSelect: true,
      emptyText: TocLanguage.gridNoRecords,
      listeners:{  
       'click': function(dataview, index, node, e) {  
         this.onAddProduct(dataview.getStore().getAt(index));
       },  
       scope:this  
     }  
    });
    
    this.pnlProducts = new Ext.Panel({
      region: 'center',
      layout: 'fit',
      autoScroll: true,
      items: dvProducts,
      border: false,
      bbar : new Ext.PageToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: dsProducts,
        steps: Toc.CONF.GRID_STEPS,
        beforePageText: TocLanguage.beforePageText,
        firstText: TocLanguage.firstText,
        lastText: TocLanguage.lastText,
        nextText: TocLanguage.nextText,
        prevText: TocLanguage.prevText,
        afterPageText: TocLanguage.afterPageText,
        refreshText: TocLanguage.refreshText,
        displayInfo: true,
        displayMsg: TocLanguage.displayMsg,
        emptyMsg: TocLanguage.emptyMsg,
        prevStepText: TocLanguage.prevStepText,
        nextStepText: TocLanguage.nextStepText
      })
    }); 
    
    return this.pnlProducts;
  },
  
  verifyQuantity: function (quantity, new_qty) {
    var new_qty = parseInt(new_qty);
    var quantity = parseInt(quantity);
    
    if (new_qty <= 0 || isNaN(new_qty)) {
      alert('<?php echo $osC_Language->get('error_wrong_quantity');?>');
      
      return false;
    }
    
  <?php if (STOCK_ALLOW_CHECKOUT == '-1') { ?>
    
    if (new_qty > quantity) {
      alert('<?php echo $osC_Language->get('error_max_stock_value_reached');?>');
    
      return false;
    } else {
      return true;
    }
    
  <?php } else if (STOCK_CHECK == '1') { ?>
    
    if (new_qty > quantity) {
      return confirm('<?php echo $osC_Language->get('warning_max_stock_value_reached');?>');
    } else {
      return true;
    }
    
  <?php } ?>
    
    return true;
  },
  
  onAddProduct: function(record) {
    var products_id = record.get('products_id');
    var products_type = record.get('products_type');
    var qty = record.get('products_quantity');
    var new_qty = Ext.getDom(products_id + '_qty').value;
    
    var params = {        
      module: 'orders',
      action: 'add_product',
      orders_id: this.ordersId,
      products_id: products_id,
      new_qty: new_qty};

    params.gift_certificate_amount = ((Ext.getDom(products_id + '_price') != null) ? Ext.getDom(products_id + '_price').value : null);
    params.senders_name = ((Ext.getDom(products_id + '_sender_name') != null) ? Ext.getDom(products_id + '_sender_name').value : null);
    params.senders_email = ((Ext.getDom(products_id + '_sender_email') != null) ? Ext.getDom(products_id + '_sender_email').value : null);
    params.recipients_name = ((Ext.getDom(products_id + '_recipient_name') != null) ? Ext.getDom(products_id + '_recipient_name').value : null);
    params.recipients_email = ((Ext.getDom(products_id + '_recipient_email') != null) ? Ext.getDom(products_id + '_recipient_email').value : null);
    params.message = ((Ext.getDom(products_id + '_message') != null) ? Ext.getDom(products_id + '_message').value : null);

    if (this.verifyQuantity(qty, new_qty)) {
      Ext.Ajax.request({
        waitMsg: TocLanguage.formSubmitWaitMsg,
        url: Toc.CONF.CONN_URL,
        params: params,
        callback: function (options, success, response) {
          var result = Ext.decode(response.responseText);
          
          if (result.success == true) {
            this.fireEvent('saveSuccess', result.feedback);
            this.close();
          } else {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
          }
        },
        scope: this
      });
    }
  }
})
