<?php
/*
  $Id: email_templates_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.email_templates.EmailTemplatesGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'email_templates',
      action: 'list_email_templates'        
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'email_templates_id'
    }, 
    [
      'email_templates_id',
      'email_templates_name',
      'email_title',
      'email_templates_status'
    ]),
    autoLoad: true
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}
    ],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  renderPublish = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  };
  
  config.cm = new Ext.grid.ColumnModel([
    {header: '<?php echo $osC_Language->get('table_heading_email_template_name'); ?>', dataIndex: 'email_templates_name', width: 200},
    {id: 'email_templates_title', header: '<?php echo $osC_Language->get('table_heading_email_title'); ?>', width: 300, dataIndex: 'email_title'},
    {header: '<?php echo $osC_Language->get('table_heading_email_template_status'); ?>', dataIndex: 'email_templates_status', width: 80, align: 'center', renderer: renderPublish},
    config.rowActions
  ]);
  config.autoExpandColumn = 'email_templates_title';
    
  Toc.email_templates.EmailTemplatesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.email_templates.EmailTemplatesGrid, Ext.grid.GridPanel, {

  showEmailTemplatesDialog: function(record) {
    var dlg = this.owner.createEmailTemplatesDialog(record.get('email_templates_name'));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record);
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-edit-record':
        this.showEmailTemplatesDialog(record);
        break;
    }
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
        var emailTemplatesId = this.getStore().getAt(row).get('email_templates_id');
        var module = 'setStatus';
        
        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, emailTemplatesId, flag);
            break;
        }
      }
    }
  },
  
  onAction: function(action, emailTemplatesId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'email_templates',
        action: action,
        email_templates_id: emailTemplatesId,
        flag: flag
      },
      callback: function(options, success, response) {
        result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(emailTemplatesId).set('email_templates_status', flag);
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