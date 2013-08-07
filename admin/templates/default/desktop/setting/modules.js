/*
 * $Id: modules.js $
 * TomatoCart Open Source Shopping Cart Solutions
 * http://www.tomatocart.com
 *
 * Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v2 (1991)
 * as published by the Free Software Foundation.
 * 
 * NOTE:
 * This code is based on code from qWikiOffice Desktop 0.8.1
 * http://www.qwikioffice.com
 *
 * Ext JS Library 2.0 Beta 2
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
  
 * http://extjs.com/license
 */
 
Toc.settings.ModulePanel = function(app) {
  var reader = new Ext.data.JsonReader({}, [ 
    'parent',
    'text',
    'id',
    'autorun',
    'contextmenu',
    'quickstart',
    'shortcut'
    ]);

  CheckColumn = function(config){
    Ext.apply(this, config);
      if (!this.id) {
        this.id = Ext.id();
    }
    this.renderer = this.renderer.createDelegate(this);
  };

  CheckColumn.prototype = {
    init : function(grid) {
      this.grid = grid;
      this.grid.on('render', function(){
        var view = this.grid.getView();
        view.mainBody.on('mousedown', this.onMouseDown, this);
      }, this);
    },
    
    onMouseDown : function(e, t){
      if (t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
        e.stopEvent();
        
        var index = this.grid.getView().findRowIndex(t),
            record = this.grid.store.getAt(index),
            records = [];
        
        if(Ext.isEmpty(record.data['id'])) {
          for(var i = 0 ; i < this.grid.getStore().getTotalCount(); i++) {
            var rec = this.grid.getStore().getAt(i);
            if(rec.data['parent'] == record.data['parent']){
              if(t.className.indexOf('x-grid3-check-col-on') != -1){
                rec.set(this.dataIndex, false);
              } else {
                rec.set(this.dataIndex, true);
              }
              records[records.length] = rec;
            }
          }
        } else {
          record.set(this.dataIndex, !record.data[this.dataIndex]);
          records[records.length] = record;
        }
        
        Ext.each(records, function(record) {
          switch (this.dataIndex){
            case 'autorun':
              if(record.data[this.dataIndex]){
                this.items.launchers.autorun.push(record.data['id']);
              } else {
                var ids = this.items.launchers.autorun;
                var id = record.data['id'];
  
                var i = 0;
                
                while(i < ids.length){
                  if(ids[i] == id){
                    ids.splice(i, 1);
                  } else {
                    i++;
                  }
                }
              }
              break;
              
              case 'quickstart':
                if(record.data[this.dataIndex]){
                  this.items.desktop.addQuickStartButton(record.data['id'], true);
                } else {
                  this.items.desktop.removeQuickStartButton(record.data['id'], true);
                }
                break;
              
              case 'shortcut':
                if(record.data[this.dataIndex]){
                  this.items.desktop.addShortcut(record.data['id'], true);
                } else {
                  this.items.desktop.removeShortcut(record.data['id'], true);
                }
                break;
              
              case 'contextmenu':
                if(record.data[this.dataIndex]){
                  this.items.desktop.addContextMenu(record.data['id'], true);
                } else {
                  var ids = this.items.launchers.contextmenu;
                    id = record.data['id'];
                  if (ids.length >= 0 ){
                    this.items.desktop.removeContextMenu(record.data['id'], true);
                  } else {
                    record.set(this.dataIndex, true);
                  }
                }
                break;
              }
           record.commit();
        }, this);
      }
    },
    
    renderer : function(v, p, record){
      p.css += ' x-grid3-check-col-td'; 
      return '<div class="x-grid3-check-col'+(v?'-on':'')+' x-grid3-cc-'+this.id+'">&#160;</div>';
    },
    
    groupRenderer : function(v) {
      return String(v);
    }
  };    
  
  var autorunCheck = new CheckColumn({
    header: TocLanguage.colAutorun,
    dataIndex: 'autorun',
    items:app
  });
    
  var quickstartCheck = new CheckColumn({
    header: TocLanguage.colQuickstart,
    dataIndex: 'quickstart',
    items:app
  });

  var shortcutCheck = new CheckColumn({
    header: TocLanguage.colShortcut,
    dataIndex: 'shortcut',
    items:app
  });

  var contextmenuCheck = new CheckColumn({
    header: TocLanguage.colContextmenu,
    dataIndex: 'contextmenu',
    items:app
  });
  
  var grdModules = new Ext.grid.GridPanel({
      store: new Ext.data.GroupingStore({
      url: Toc.CONF.CONN_URL,
         baseParams: {
           module: 'desktop_settings',
           action: 'load_modules'
         },
        reader:reader,
        autoLoad: true,
        sortInfo:{field: 'id', direction: "ASC"},
        groupField:'parent'
      }),

        columns: [
          {header: "module", dataIndex: 'parent', hidden: true},
          {header: TocLanguage.colModule, dataIndex: 'text'},
          autorunCheck,
          quickstartCheck,
          shortcutCheck,
          contextmenuCheck
        ],

        view: new Ext.grid.GroupingView({
          forceFit:true,
          groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})' 
        }),
        region: 'center',
        plugins: [autorunCheck, quickstartCheck, shortcutCheck, contextmenuCheck],
        iconCls: 'icon-grid'
  });

  Toc.settings.ModulePanel.superclass.constructor.call(this, {
    title: TocLanguage.ModulesSetting,
    layout: 'border',
    region: 'center',
    labelAlign:'left',
    border: false,
    bodyStyle:'background:transparent;',
    items: grdModules
  });
};


Ext.extend(Toc.settings.ModulePanel, Ext.Panel, {
  onClick:  function(e, t) {
    alert("nihao");
  }
});