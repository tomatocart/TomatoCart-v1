<?php
/*
  $Id: gift_certificates_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.gift_certificates.GiftCertificatesGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'gift_certificates',
      action: 'list_gift_certificates'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'gift_certificates_id'
    }, [
      'gift_certificates_id',
      'gift_certificates_code',
      'gift_certificates_customer',
      'gift_certificates_amount',
      'gift_certificates_balance',
      'gift_certificates_date_purchased',
      'gift_certificates_date_status',
      'recipients_name',
      'recipients_email',
      'senders_name',
      'senders_email',
      'messages',
      'certificate_details',
      'history'
    ]),
    autoLoad: true
  }); 
  
  var expander = new Ext.grid.RowExpander({
    tpl: new Ext.Template(
      '<table width="100%">',
        '<tr>',
          '<td width="50%">',
            '<p><b><?php echo $osC_Language->get('section_certificate_details'); ?></b>{certificate_details}</p>',
          '</td>',
          '<td>',
            '<p><b><?php echo $osC_Language->get('section_redeem_history'); ?></b> {history}</p>',
          '</td>',
        '</tr>',
      '</table>')
  });  
  config.plugins = expander;
  
  renderPublish = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    expander,
    config.sm,
    {id:'gift_certificates_code', header: '<?php echo $osC_Language->get('table_heading_gift_certificates_code'); ?>', dataIndex: 'gift_certificates_code', width: 200, sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_gift_certificates_customer'); ?>', dataIndex: 'gift_certificates_customer', align: 'center'},
    {header: '<?php echo $osC_Language->get('table_heading_gift_certificates_amount'); ?>', dataIndex: 'gift_certificates_amount', align: 'center', sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_gift_certificates_balance'); ?>', dataIndex: 'gift_certificates_balance', align: 'center', sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_gift_gift_certificates_date_purchased'); ?>', dataIndex: 'gift_certificates_date_purchased', align: 'center', sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_gift_gift_certificates_status'); ?>', dataIndex: 'gift_certificates_date_status', align: 'center', renderer: renderPublish, sortable: true}
  ]);
  config.autoExpandColumn = 'gift_certificates_code';
  
  config.txtSearch = new Ext.form.TextField({
    name: 'search',
    width: 150,
    hideLabel: true
  });

  config.tbar = [
    { 
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }, 
    '->',
    config.txtSearch, 
    ' ', 
    { 
      text: '',
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }
  ];
  
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    beforePageText : TocLanguage.beforePageText,
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
  });
  
  Toc.gift_certificates.GiftCertificatesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.gift_certificates.GiftCertificatesGrid, Ext.grid.GridPanel, {

  onRefresh: function() {
    this.getStore().reload();
  },
  
  onSearch: function() {
    var filter = this.txtSearch.getValue() || null;
    var store = this.getStore();
    
    store.baseParams['search'] = filter;
    store.reload();
  },
  
  onClick: function(e, target) {
    var t = e.getTarget();
    var v = this.view;
    var row = v.findRowIndex(t);
    var action = false;
  
    if (row !== false) {
      var btn = e.getTarget(".img-button");
      
      if (btn) {
        action = btn.className.replace(/img-button btn-/, '').trim();
      }

      if (action != 'img-button') {
        var certificatesId = this.getStore().getAt(row).get('gift_certificates_id');
        var module = 'setStatus';
        
        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, certificatesId, flag);

            break;
        }
      }
    }
  },
  
  onAction: function(action, certificatesId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'gift_certificates',
        action: action,
        gift_certificates_id: certificatesId,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(certificatesId).set('gift_certificates_date_status', flag);
          store.commitChanges();
          
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
        else
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
      },
      scope: this
    });
  }
});