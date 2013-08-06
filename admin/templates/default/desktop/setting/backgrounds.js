/*
 * $Id: backgrounds.js $
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
 
Toc.settings.BackgroundPanel = function(app) {

  var view;
  var desktop;

  desktop = app.getDesktop();

  this.store = new Ext.data.JsonStore ({
    baseParams: {
      module: 'desktop_settings',
      action: 'list_wallpapers'
    },
    fields: ['code', 'name', 'thumbnail', 'path'],
    id: 'code',
    root: 'wallpapers',
    url: Toc.CONF.CONN_URL,
    autoLoad: true
  });
  
  this.store.on('load', function(store, records) {
    if(records){
      defaults.setTitle(TocLanguage.defaultWallpapers + ' (' + records.length + ')');
      
      var w = app.styles.wallpaper;
      if(w){
        view.select(w);
      }
    }
  }, this);

  var tpl = new Ext.XTemplate(
    '<tpl for=".">',
      '<div class="setting-view-thumb-wrap" id="{code}">',
        '<div class="setting-view-thumb"><img src="{thumbnail}" title="{name}" /></div>',
      '<span>{shortName}</span></div>',
    '</tpl>',
    '<div class="x-clear"></div>'
  );

  view = new Ext.DataView({
    autoHeight:true,
    emptyText: TocLanguage.noWallpaperText,
    itemSelector:'div.setting-view-thumb-wrap',
    loadingText: TocLanguage.loadingText,
    singleSelect: true,
    overClass:'x-view-over',
    prepareData: function(data){
      data.shortName = Ext.util.Format.ellipsis(data.name, 17);
      return data;
    },
    store: this.store,
    tpl: tpl
  });
  
  view.on('selectionchange', onSelectionChange, this);
  
  var defaults = new Ext.Panel({
    baseCls:'collapse-group',
    border: false,
    cls: 'setting-thumbnail-viewer',
    hideCollapseTool: true,
    id: 'setting-wallpaper-view',
    items: view,
    title: 'Default Wallpapers',
    titleCollapse: true
  });  
    
  var wallpapers = new Ext.Panel({
    autoScroll: true,
    bodyStyle: 'padding:10px',
    border: true,
    cls: 'setting-card-subpanel',
    id: 'wallpapers',
    items: defaults,
    margins: '10 10 10 10',
    region: 'center'
  });
  
  var wpp = app.styles.wallpaperposition;
  var tileRadio = createRadio('tile', wpp == 'tile' ? true : false, 90, 40);
  var centerRadio = createRadio('center', wpp == 'center' ? true : false, 200, 40);
  
  var position = new Ext.Panel({
    border: false,
    height: 140,
    id: 'position',
    items: [{
        border: false,
        items: {border: false, html:TocLanguage.wallpaperPositionTitle},
        x: 15,
        y: 15
      },{
        border: false,
        items: {border: false, html: '<img class="bg-pos-tile" src="'+Ext.BLANK_IMAGE_URL+'" width="64" height="44" border="0" alt="" />'},
        x: 15,
        y: 40
      },
        tileRadio,
      {
        border: false,
        items: {border: false, html: '<img class="bg-pos-center" src="'+Ext.BLANK_IMAGE_URL+'" width="64" height="44" border="0" alt="" />'},
        x: 125,
        y: 40
      },
        centerRadio,
      {
        border: false,
        items: {border: false, html:TocLanguage.desktopBackgroundTitle},
        x: 245,
        y: 15
      },{
        border: false,
        /* items: new Ext.ColorPalette({
          listeners: {
            'select': {
              fn: onColorSelect
              , scope: this
            }
          }
        }), */
        items: new Ext.Button({
          handler: onChangeBgColor,
          //menu: new Ext.ux.menu.ColorMenu(),
          scope: this,
          text: TocLanguage.btnBackgroundColor
        }),
        x: 245,
        y: 40
      },{
        border: false,
        items: {border: false, html:TocLanguage.fontColorTitle},
        x: 425,
        y: 15
      },{
        border: false,
        /* items: new Ext.ColorPalette({
          listeners: {
            'select': {
              fn: onFontColorSelect
              , scope: this
            }
          }
        }), */
        items: new Ext.Button({
          handler: onChangeFontColor,
          scope: this,
          text: TocLanguage.btnFontColor
        }),
        x: 425,
        y: 40
    }],
    layout: 'absolute',
    region: 'south',
    split: false
  });
  
	Toc.settings.BackgroundPanel.superclass.constructor.call(this, {
		title: TocLanguage.WallpaperSetting,
		layout: 'border',
		border: false,
		items:[wallpapers, position]
	});
	

  function createRadio(value, checked, x, y){
    if(value){
      radio = new Ext.form.Radio({
        name: 'position',
        inputValue: value,
        checked: checked,
        x: x,
        y: y
      });
      
      radio.on('check', togglePosition, radio);
      return radio;
    }
  }

  function onChangeBgColor(){
    var dialog = new Ext.ux.ColorDialog({
      border: false, 
      closeAction: 'close', 
      listeners: {
        'select': { fn: onColorSelect, scope: this, buffer: 350 }
      }, 
      manager: app.getDesktop().getManager(), 
      resizable: false, 
      title: 'Color Picker'
    });
    
    dialog.show(app.styles.backgroundcolor);
  }
    
  function onColorSelect(p, hex){
    desktop.setBackgroundColor(hex);
  }
  
  function onChangeFontColor(){
      var dialog = new Ext.ux.ColorDialog({
      border: false
      , closeAction: 'close'
      , listeners: {
        'select': { fn: onFontColorSelect, scope: this, buffer: 350 }
      }
      , manager: app.getDesktop().getManager()
      , resizable: false
      , title: 'Color Picker'
    });
        
    dialog.show(app.styles.fontcolor);
  }
  
  function onFontColorSelect(p, hex){
    desktop.setFontColor(hex);
  }

  function onSelectionChange(view, sel){
    if(sel.length > 0){
      var record = view.getRecord(sel[0]);
      
      if(record && record.data.code && record.data.path){
        var wallpaper = {
          code: record.data.code,
          path: record.data.path
        };

        if(app.styles.wallpaper != wallpaper.code){
          desktop.setWallpaper(wallpaper);
        }
      }
    }
  };
  
  function togglePosition(field, checked){
    if(checked === true){
      desktop.setWallpaperPosition(field.inputValue);
    }
  }
};

Ext.extend(Toc.settings.BackgroundPanel, Ext.Panel);

/* 
 * Will ensure that the checkchange event is fired on 
 * node double click
 */
Ext.override(Ext.tree.TreeNodeUI, {
  toggleCheck : function(value){    
        var cb = this.checkbox;
        if(cb){
            cb.checked = (value === undefined ? !cb.checked : value);
            this.fireEvent('checkchange', this.node, cb.checked);
        }
    }
});
