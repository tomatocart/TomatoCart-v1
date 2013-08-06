<?php
/*
  $Id: search_terms_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

  Toc.search_terms.SearchTermsGrid = function(config) {
    
    config = config || {};
    
    config.border = false;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
    
    config.ds = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'search_terms',
        action: 'list_search_terms'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        id: 'search_terms_id'
      }, [
        'search_terms_id',
        'text',
        'products_count',
        'search_count',
        'synonym',
        'show_in_terms'
      ]),
      autoLoad: true
    }); 

    config.rowActions = new Ext.ux.grid.RowActions({
      actions:[
        {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}],
      widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);    
    config.plugins = config.rowActions;
    
    renderStatus = function(status) {
      if(status == 1) {
        return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
      }else {
        return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
      }
    };
  
    config.cm = new Ext.grid.ColumnModel([
      {id: 'search_text',header: '<?php echo $osC_Language->get('table_heading_search_text'); ?>', dataIndex: 'text', align: 'left'},
      {header: '<?php echo $osC_Language->get('table_heading_products_count'); ?>', dataIndex: 'products_count', width: 100, align: 'center'},
      {header: '<?php echo $osC_Language->get('table_heading_search_count'); ?>', dataIndex: 'search_count', width: 100, align: 'center'},
      {header: '<?php echo $osC_Language->get('table_heading_synonym'); ?>', dataIndex: 'synonym', width: 120, align: 'left'},
      {header: '<?php echo $osC_Language->get('table_heading_show_in_terms'); ?>', dataIndex: 'show_in_terms', width: 120, align: 'center', renderer: renderStatus},
      config.rowActions
    ]);
    config.autoExpandColumn = 'search_text';
    
    config.search = new Ext.form.TextField({name: 'search', width: 130});
    config.tbar = [
      { 
        text: TocLanguage.btnRefresh,
        iconCls: 'refresh',
        handler: this.onRefresh,
        scope: this
      },
      '->',
      config.search,
      '',
      {
        iconCls: 'search',
        handler: this.onSearch,
        scope: this
     }];  
    
    config.bbar = new Ext.PageToolbar({
      pageSize: Toc.CONF.GRID_PAGE_SIZE,
      store: config.ds,
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
    });
    
    Toc.search_terms.SearchTermsGrid.superclass.constructor.call(this, config);
  };
  
  Ext.extend(Toc.search_terms.SearchTermsGrid, Ext.grid.GridPanel, {
    onRefresh: function() {
      this.getStore().reload();
    },
    
    onSearch: function () {
      var store = this.getStore();
  
      store.baseParams['search'] = this.search.getValue() || null;
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
          var searchTermsId = this.getStore().getAt(row).get('search_terms_id');
          
          switch(action) {
            case 'status-off':
            case 'status-on':
              flag = (action == 'status-on') ? 1 : 0;
              this.setStatus(searchTermsId, flag);
              break;
          }
        }  
      }
    },
  
    setStatus: function(searchTermsId, flag) {
      Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'search_terms',
          action: 'set_status',
          search_terms_id: searchTermsId,
          flag: flag
        },
        callback: function(options, success, response) {
          result = Ext.decode(response.responseText);
          
          if (result.success == true) {
            var store = this.getStore();
            store.getById(searchTermsId).set('show_in_terms', flag);
            store.commitChanges();
          }
  
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        },
        scope: this
      });
    },
  
    onEdit: function(record) {
      var dlg = this.owner.createSearchTermsEditDialog();
      dlg.setTitle(record.get('text'));

      dlg.on('saveSuccess', function(){
        this.onRefresh();
      }, this);
      
      dlg.show(record.get('search_terms_id'));
    },
    
    onRowAction: function(grid, record, action, row, col) {
      this.onEdit(record);
    }
  });