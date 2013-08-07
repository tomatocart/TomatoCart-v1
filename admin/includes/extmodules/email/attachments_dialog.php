<?php
/*
  $Id: attachments_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.email.AttachmentsDialog = function(config) {

  config = config || {};
  
  config.id = 'attachments_dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_attachments'); ?>';
  config.width = 400;
  config.height = 260;
  config.layout = 'fit';
  config.modal = true;
  config.items = this.buildGrid();
  
  config.buttons = [
    {
      text: TocLanguage.btnAdd,
      handler: this.onAdd,
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
  
  Toc.email.AttachmentsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.email.AttachmentsDialog, Ext.Window, {
  buildGrid: function() {
    var rowActions = new Ext.ux.grid.RowActions({
      actions:[
        {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
      ],
      widthIntercept: Ext.isSafari ? 4 : 2
    });
    rowActions.on('action', this.onRowAction, this);  
      
    this.grdAttachments = new Ext.grid.GridPanel({
      ds: new Ext.data.Store({  
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'email',
          action: 'list_attachments'        
        },  
        reader: new Ext.data.JsonReader(
          {
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'name'
          }, 
          ['name']
        ),            
        autoLoad: true
      }),
      cm: new Ext.grid.ColumnModel([
        {header: '<?php echo $osC_Language->get('table_heading_name'); ?>', dataIndex: 'name', sortable: true},
        rowActions
      ]),
      plugins: rowActions,
      view: new Ext.grid.GridView({
        forceFit: true,
        emptyText: TocLanguage.gridNoRecords   
      })
    });
    
    return this.grdAttachments;
  },
  
  onAdd: function() {
    var dlg = this.owner.createUploadDialog();
   
    dlg.on('saveSuccess', function(feedback, fileName){
      this.grdAttachments.getStore().reload();
    }, this);
    
    dlg.show();  
  },

  onDelete: function(record) {
    var attachment = record.get('name');
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'email',
        action: 'remove_attachment',
        name: attachment
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == false) {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
        }
        
        this.grdAttachments.getStore().reload();
      },
      scope: this
    });   
  },
  
  onRowAction: function(grid, record, action, row, col) {
    this.onDelete(record);
  },
   
  getAttachments: function () {
    var store = this.grdAttachments.getStore();
    
    var attachments = [];
    for (var i = 0; i < store.getTotalCount(); i++) {
      attachments.push(store.getAt(i).get('name'));
    }
    
    return attachments;
  }
});