<?php
/*
  $Id: templates_modules_layout_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.templates_modules_layout.TemplatesModulesLayoutGrid = function(config) {
  
  config = config || {};
  
  config.border = false;
  config.viewConfig = {forceFit: true};  
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'templates_modules_layout',
      action: 'list_templates_modules_layout',
      set: config.set
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'id'
    }, [
      'id',
      'content_page',
      'boxes_group',
      'sort_order',
      'page_specific',
      'templates_boxes_id',
      'box_title',
      'code'
    ])
  });  
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {header: '<?php echo $osC_Language->get('table_heading_modules'); ?>',dataIndex: 'box_title'},
    {header: '<?php echo $osC_Language->get('table_heading_pages'); ?>',dataIndex: 'content_page', sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_page_specific'); ?>',dataIndex: 'page_specific', sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_group'); ?>',dataIndex: 'boxes_group', sortable: true},
    {header: '<?php echo $osC_Language->get('table_heading_sort_order'); ?>',dataIndex: 'sort_order', sortable: true},
    config.rowActions
  ]);
     
  dsTemplates = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'templates_modules_layout',
      action: 'get_templates'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
    }, [
      'id',
      'title',
      'default'
    ]),
    autoLoad: true,
    listeners: {
      load: this.onCboTemplatesLoad,
      scope: this
    }
  }); 
  
  config.cboTemplates = new Ext.form.ComboBox({
    width:200,
    store: dsTemplates,
    triggerAction: 'all',
    displayField: 'title',
    valueField: 'id',
    hiddenName: 'filter_code',
    readOnly: true,
    listeners: {
      select: this.onCboTemplatesSelect,
      scope: this
    }
  });
  
  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls:'add',
      handler: this.onAdd,
      scope: this
    },
    '-', 
    {
      text: TocLanguage.btnDelete,
      iconCls:'remove',
      handler: this.onBatchDelete,
      scope: this
    },
    '-',
    { 
      text: TocLanguage.btnRefresh,
      iconCls:'refresh',
      handler: this.onRefresh,
      scope: this
    }, 
    '->', 
    config.cboTemplates
  ];
  
  Toc.templates_modules_layout.TemplatesModulesLayoutGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.templates_modules_layout.TemplatesModulesLayoutGrid, Ext.grid.GridPanel, {
  onCboTemplatesLoad: function() {
    var store = this.cboTemplates.getStore();
    
    var record = store.getAt(store.find('default', '1'));
    this.cboTemplates.setValue(record.get('id'));
    this.onCboTemplatesSelect();
  },
  
  onCboTemplatesSelect: function() {
    this.getStore().baseParams['id'] = this.cboTemplates.getValue();
    this.onRefresh();
  },
  
  onAdd: function() {
    var dlg = this.owner.createTemplatesModulesLayoutDialog();
    dlg.setTitle('<?php echo $osC_Language->get('action_heading_new_template_layout_module'); ?>');
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(null, this.cboTemplates.getValue(), this.set);
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createTemplatesModulesLayoutDialog();
    dlg.setTitle(record.get("box_title"));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get("id"), this.cboTemplates.getValue(), this.set);
  },
  
  onDelete: function(record) {
    var id = record.get('id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          btn.disabled = true;
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'templates_modules_layout',
              action: 'delete_box_layout',
              box_layout_id: record.get('id'),
              set: this.set
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                this.getStore().reload();
              } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            }, scope: this
          });   
        }
      }, this);
  },
  
  onBatchDelete: function() {
    var keys = this.selModel.selections.keys;
    
    if (keys.length > 0) {
      var batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            btn.disabled = true;
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'templates_modules_layout',
                action: 'delete_box_layouts',
                batch: batch,
                set: this.set
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.getStore().reload();
                } else {
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              }, scope: this
            });   
          }
        }, this);
    } else {
       Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onRowAction:function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  } 
});