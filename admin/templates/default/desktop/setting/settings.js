/*
 * $Id: settings.js $
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
 
Ext.namespace("Toc.settings");

Toc.settings.SettingsDialog = function(app) {
  this.pnlModule = new Toc.settings.ModulePanel(app);
  this.pnlTheme = new Toc.settings.ThemePanel(app);
  this.pnlBackground = new Toc.settings.BackgroundPanel(app);
  this.pnlSidebar= new Toc.settings.SidebarPanel(app);

  this.tabPanel = new Ext.TabPanel({
    plain: true,
    frame: true,
    border: false,
    region: 'center',
    activeTab: 0,
    deferredRender: false, 
    layoutOnTabChange : true, 
    items:[this.pnlModule, this.pnlTheme, this.pnlBackground, this.pnlSidebar]
  });
  
	this.frmPanel = new Ext.FormPanel({
		layout: 'border',
		region: 'center',
		border: false,
		items: this.tabPanel
	});

	Toc.settings.SettingsDialog.superclass.constructor.call(this, {
		title: TocLanguage.DesktopSetting,
		iconCls: 'services',
		id: 'desktop-setting-win',
		width: 600,
		height: 500,
		layout: 'border',
		resizable: false,
		plain:false,
		modal: false,
		shadow: false,
		items: this.frmPanel,
		manager: app.getDesktop().getManager(), 
        buttons:[{
		  text: TocLanguage.btnSave,
		  handler: function(){
		    if (app.isReady) {
		      this.save(app);
		    }
		  },
		  scope:this
		},{
		  text: TocLanguage.btnClose,
		  handler: this.close,
		  scope: this
		}]
	});

  this.on("beforeclose",function(){
    Ext.getCmp('x-traypanel-setting-btn').setDisabled(false);
  });
};

Ext.extend(Toc.settings.SettingsDialog, Ext.Window, {
  
  activeSidebarPanel: function() {
    this.tabPanel.activate(this.pnlSidebar);
  },
  
  save: function (app){
    Ext.MessageBox.show({
      msg: TocLanguage.saveDataMsg, 
      progressText: TocLanguage.saveDataProgressText, 
      width:300, 
      wait:true, 
      waitConfig: {interval:200}, 
      icon:'desktop-download'}
    );
    
    var c = app.launchers;
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'desktop_settings',
        action: 'save_settings',
        autorun: Ext.encode(c.autorun),
        quickstart: Ext.encode(c.quickstart),
        contextmenu: Ext.encode(c.contextmenu),
        shortcut: Ext.encode(c.shortcut),
        theme: app.styles.theme,
        fontcolor: app.styles.fontcolor,
        wallpaper: app.styles.wallpaper,
        transparency: app.styles.transparency,
        backgroundcolor: app.styles.backgroundcolor,
        wallpaperposition: app.styles.wallpaperposition,
        sidebaropen: app.sidebaropened,
        sidebartransparency: app.styles.sidebartransparency,
        sidebarbackgroundcolor: app.styles.sidebarbackgroundcolor
      },
      callback: function(options, success, response){
        if(Ext.decode(response.responseText).success){
          Ext.MessageBox.hide();
          app.getDesktop().showNotification({title: TocLanguage.msgSuccessTitle, html: TocLanguage.saveDataSuccess});
          
          this.close();
        }else{
          Ext.MessageBox.hide();
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, TocLanguage.connServerFailure);
        }
      },
      failure: function(){
        Ext.MessageBox.hide();
        Ext.MessageBox.alert(TocLanguage.msgErrTitle, TocLanguage.lostConnectionToServer);  
      },
      scope: this
    });
  }
});