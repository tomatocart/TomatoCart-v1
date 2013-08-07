<?php
/*
  $Id: articles_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.articles.ArticlesGrid = function(config) {
  
  config = config || {};

  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'articles',
      action: 'list_articles'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'articles_id'
    }, [
      'articles_id',
      'articles_status',
      'articles_order',
      'articles_name',
      'articles_categories_name'
    ]),
    autoLoad: true
  }); 
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}],
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
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'articles_name', header: '<?php echo $osC_Language->get('table_heading_articles'); ?>', dataIndex: 'articles_name', sortable: true},
    { header: '<?php echo $osC_Language->get('table_heading_articles_categories'); ?>', align: 'center', dataIndex: 'articles_categories_name'},
    { header: '<?php echo $osC_Language->get('table_heading_publish'); ?>', align: 'center', renderer: renderPublish, dataIndex: 'articles_status'},
    { header: '<?php echo $osC_Language->get('table_heading_sort_order'); ?>', align: 'center', dataIndex: 'articles_order', sortable: true},
    config.rowActions
  ]);
  config.autoExpandColumn = 'articles_name'; 
  
  dsCategories = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'articles',
      action: 'get_articles_categories',
      top: '1'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
    }, [
      'id',
      'text'
    ])
  });
  
  config.cboCategories = new Ext.form.ComboBox({
    store: dsCategories,
    valueField: 'id', 
    displayField: 'text',
    readOnly: true,
    emptyText: '<?php echo $osC_Language->get('top_articles_category'); ?>',
    triggerAction: 'all',
    listeners: {
      select: this.onSearch,
      scope: this
    }
  });
  
  config.txtSearch = new Ext.form.TextField({
    width: 100,
    hideLabel: true
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
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }, 
    '->', 
    config.txtSearch, 
    ' ', 
    config.cboCategories, 
    { 
      text: '',
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }
  ];
  
  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnAdd,
        iconCls:'add',
        handler: function() {
          thisObj.onAdd();
        }
      },
      {
        text: TocLanguage.btnDelete,
        iconCls:'remove',
        handler: function() {
          thisObj.onBatchDelete();
        }
      }
    ],
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
  
  Toc.articles.ArticlesGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.articles.ArticlesGrid, Ext.grid.GridPanel, {
  
  onAdd: function() {
    var dlg = this.owner.createArticlesDialog();
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(null, this.cboCategories.getValue());
  },
  
  onEdit: function(record) {
    var dlg = this.owner.createArticlesDialog();
    dlg.setTitle(record.get("articles_name"));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get("articles_id"));
  },
  
  onDelete: function(record) {
    var articlesId = record.get('articles_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'articles',
              action: 'delete_article',
              articles_id: articlesId
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                this.getStore().reload();
              } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            }, 
            scope: this
          });
        }
      }, 
      this
    );
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
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'articles',
                action: 'delete_articles',
                batch: batch
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.getStore().reload();
                } else {
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              }, 
              scope: this
            });
          }
        }, 
        this
      );
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
    
  onRefresh: function() {
    this.getStore().reload();
  },
  
  onSearch: function() {
    var categoriesId = this.cboCategories.getValue() || null;
    var filter = this.txtSearch.getValue() || null;
    var store = this.getStore();
    
    store.baseParams['current_category_id'] = categoriesId;
    store.baseParams['search'] = filter;
    store.reload();
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-edit-record':
        this.onEdit(record);
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
        var articlesId = this.getStore().getAt(row).get('articles_id');
        var module = 'setStatus';
        
        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, articlesId, flag);

            break;
        }
      }
    }
  },
  
  onAction: function(action, articlesId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'articles',
        action: action,
        articles_id: articlesId,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(articlesId).set('articles_status', flag);
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