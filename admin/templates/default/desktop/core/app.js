/*
  $Id: app.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd
  
  NOTE:
  This code is based on code from the open source project qWikiOffice. 
  The qWikiOffice licensing can be viewed here:
  
  qWikiOffice Desktop 0.8.1
  Copyright(c) 2007-2008, Integrated Technologies, Inc.
  licensing@qwikioffice.com
  
  http://www.qwikioffice.com/license

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
  
*/ 

Ext.app.App = function(cfg){
  Ext.apply(this, cfg);
  
  this.addEvents({
      'ready' : true,
      'beforeunload' : true
  }); 
   
  Ext.onReady(this.initApp, this);
};

Ext.extend(Ext.app.App, Ext.util.Observable, {
  /**
   * Read-only. This app's ready state
   * @type boolean
   */
  isReady : false,
  
  /**
   * Read-only. This app's launchers
   * @type object
   */  
  launchers : null,
    
  /**
   * Read-only. This app's modules
   * @type array
   */
  modules : null, 
  
  /**
   * Read-only. This app's styles
   * @type object
   */
  styles : null,
  
  /**
   * Read-only. This app's Start Menu config
   * @type object
   */
  startConfig : null,
  
  /**
   * The sidebar's gadgets
   */
  gadgets: null,
  
  /**
   * The sidebar state
   */
  sidebaropened: false,
  
  /**
   * The sidebar collapse status
   */
  sidebarcollapsed: false,
  
  /**
     * Read-only. The url of this app's loader script
     * @type string
     * 
     * Handles module on demand loading
     */
  loader : 'load.php',
  
  /**
   * Read-only. The queue of requests to run once a module is loaded
   */
  requestQueue : [],
      
  startMenu : null,

  init : Ext.emptyFn,
  getModules : Ext.emptyFn,
  getLaunchers : Ext.emptyFn,
  getStyles : Ext.emptyFn,
  getStartConfig : Ext.emptyFn,
  getGadgets: Ext.emptyFn,
  isSidebarOpen: Ext.emptyFn,
  isWizardComplete: Ext.emptyFn,

  /**
   * Read-only. The queue of requests to run once a module is loaded
   */
  initApp : function(){
    Ext.BLANK_IMAGE_URL = 'templates/default/desktop/images/default/s.gif';
  
    this.preventBackspace();
    
    this.modules = this.modules || this.getModules();
    this.startConfig = this.startConfig || this.getStartConfig();
    this.styles = this.styles || this.getStyles();
    this.launchers = this.launchers || this.getLaunchers();
    this.gadgets = this.gadgets || this.getGadgets();
    this.sidebaropened = this.sidebaropened || this.isSidebarOpen();
    
    this.desktop = new Ext.Desktop(this);
    this.startMenu = this.desktop.taskbar.startMenu;
    
    this.initModules();
    this.initStyles();
    this.initLaunchers();
    this.initGadgets();
    
    this.init();
    
    this.createTrayButton();
    
    Ext.EventManager.on(window, 'beforeunload', this.onUnload, this);
    this.fireEvent('ready', this);
    this.isReady = true;
    
    this.onReady(function(){
      Ext.get('x-loading-mask').remove();
      Ext.get('x-loading-panel').remove();
      
      this.showLiveFeedNotification();
    },this);
  },
  
  showLiveFeedNotification: function() {
  	Ext.Ajax.request({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'system',
        action: 'get_tomatcart_feeds'
      },
      callback: function (options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
	        var liveFeedWin = this.showNotification({
	          id: 'live-feed',
	          title: 'TomatoCart Live Feed',
	          iconCls: 'icon-tomatocart-feeds',
	          hideDelay: 7000,
	          height: 500,
	          html: result.feeds
	        });
	      }
      },
      scope: this
    });
  },
  
  initStyles : function(){
    var s = this.styles;
    if(!s){
      return false;
    }

    this.desktop.setBackgroundColor(s.backgroundcolor);
    this.desktop.setFontColor(s.fontcolor);
    this.desktop.setTheme(s.theme);
    this.desktop.setTransparency(s.transparency);
    this.desktop.setWallpaper(s.wallpaper);
    this.desktop.setWallpaperPosition(s.wallpaperposition);
    
    return true;
  },
  
  /**
   * Read-only. The queue of requests to run once a module is loaded
   */
  initModules : function(){
    var ms = this.modules;
    if(!ms){ return false; }
      
    for (var i = 0, len = ms.length; i < len; i++) {
      var m = ms[i];
      m.app = this;
      
      if (m.appType == 'group') {
	      if(m.loaded === false && Ext.isEmpty(m.launcher.handler)) {
	        m.launcher.handler = this.createWindow.createDelegate(this, [m.id]);
	      }
        
        var items = m.items;        
        for(var j = 0; j < items.length; j++){
          var item = this.getModule(items[j]);
          
          if (item) {
            item.app = this;
            
			      if (item.loaded === false && Ext.isEmpty(item.launcher.handler)) {
			        item.launcher.handler = this.createWindow.createDelegate(this, [item.id]);
			      }
			      
			      if(item.appType == 'subgroup') {
			        var items2 = item.items;
			        
			        for(var k = 0; k < items2.length; k++){
			          var item2 = this.getModule(items2[k]);
			          
			          item2.app = this;
			          
		            if(item2.loaded === false && Ext.isEmpty(item2.launcher.handler)) {
		              item2.launcher.handler = this.createWindow.createDelegate(this, [item2.id]);
		            }
		            
		            item.menu.add(item2.launcher);
			        }
			      }
			      
            m.menu.add(item.launcher);
          }
        }
        
        this.startMenu.add(m.launcher);
      }
    }
  },
  
  initLaunchers : function(){
    var l = this.launchers;
    if(!l){
      return false;
    }
    
    if (l.contextmenu) {
      this.initContextMenu(l.contextmenu);
    }
    
    if (l.quickstart) {
      this.initQuickStart(l.quickstart);
    }

    if (l.shortcut) {
      this.initShortcut(l.shortcut);
    }
    
    if (this.isWizardComplete()!= true) {
      this.onReady(this.initAutoRun.createDelegate(this, [['configuration_wizard-win']]), this);
    } else {
      if(l.autorun) {
        this.onReady(this.initAutoRun.createDelegate(this, [l.autorun]), this);      
      }
    }
    
    return true;
  },
  
  initGadgets: function() {
    if (this.sidebaropened && !Ext.isEmpty(this.gadgets)) {

      if (this.gadgets.length > 0) {
        this.desktop.sidebar.addGadgets(this.gadgets, false);
      }
    }
  },
  
  /**
   * @param {object} The config object used to set the Notification window
  */
  showNotification :function(config){
    var win = new Ext.ux.Notification(Ext.apply({
      animateTarget: Ext.get('ux-taskbar')
      , autoDestroy: true
      , hideDelay: 3000
      , html: ''
      , iconCls: 'x-icon-waiting'
      , title: ''
    }, config));
    win.animShow();

    return win;
  },
    
  /**
   * @param {array} mIds An array of the module ids to run when this app is ready
   */
  initAutoRun : function(mIds){
    if(mIds){
      for(var i = 0, len = mIds.length; i < len; i++){
        var m = this.getModule(mIds[i]);
        if(m){
          m.autorun = true;
          this.createWindow(mIds[i]);
        }
      }
    }
  },
  
  /**
   * @param {array} mIds An array of the module ids to add to the Desktop Context Menu
   */
  initContextMenu : function(mIds){
    if(mIds){
      for(var i = 0, len = mIds.length; i < len; i++){
        var m = this.getModule(mIds[i]);
        if(m){
          this.desktop.cmenu.add({
            id: m.id,
            iconCls: m.launcher.iconCls,
            text: m.launcher.text,
            scope: m.launcher.scope,
            handler: m.launcher.handler
          });
        }
      }
    }
  },
  
  /**
   * @param {array} mIds An array of the module ids to add to the Desktop Shortcuts
   */
  initShortcut : function(mIds){
    if(mIds){
      for(var i = 0, len = mIds.length; i < len; i++){
        this.desktop.addShortcut(mIds[i], false);
      }
    }
  },
  
  /**
   * @param {array} mIds An array of the modulId's to add to the Quick Start panel
   */
  initQuickStart : function(mIds){
    if(mIds){
      for(var i = 0, len = mIds.length; i < len; i++){
        this.desktop.addQuickStartButton(mIds[i], false);
      }
    }
  },
  
  onUnload : function(e){
    if(this.fireEvent('beforeunload', this) === false){
      e.stopEvent();
    }
  },
  
  getDesktop: function() {
    return this.desktop;
  },
  
  getDesktopSettingWindow: function() {
		var desktopSettingWindow = new Toc.settings.SettingsDialog(this);
		
    return desktopSettingWindow;
  },
    
  createTrayButton: function(){
    var desktopSettingBtn = new Ext.Button({
      text: '',
      id: 'x-traypanel-setting-btn',
      tooltip: 'setting your desktop',
      iconCls: 'tray-setting',
      renderTo: 'ux-systemtray-panel',
      handler: function(){
      	var desktopSettingWindow = this.getDesktopSettingWindow();
      	
        desktopSettingWindow.show();
        desktopSettingBtn.setDisabled(true);
      },
      scope: this
    });
  },

  /**
	 * @param {string} moduleId
	 * 
	 * Provides the handler to the placeholder's launcher until the module it is loaded.
	 * Requests the module.  Passes in the callback and scope as params.
	 */
	createWindow : function(moduleId){
      if((moduleId.indexOf('grp') == -1) && (moduleId.indexOf('subgroup') == -1)) {
        var m = this.requestModule(moduleId, function(m){
          if(m) {
            m.createWindow();
            
            if (this.sidebaropened) {
              var win = this.desktop.getWindow(m.getId());
              
              if (win) {
                win.center();
                win.on('maximize', function(win) {
                  win.setWidth(Ext.lib.Dom.getViewWidth() - this.desktop.sidebar.pnlSidebar.getInnerWidth() - this.desktop.sidebar.splitWidth);
                }, this);
              }
            }
          }
        }, this);
	  } else {
	    return false;
	  }
	},
	
  /**
   * @param {string} moduleName
   * @param {string} method
   * @param {function} callback
   * @param {array} params
   * 
   * Provides the handler to create the window of one module in other module.
   * Requests the module.  Passes in the method name of the module and callback and params needed in some situation.
   */
	callModuleFunc: function(moduleName, method, callback, params) {
      var moduleId = moduleName + '-win';

      this.requestModule(moduleId, function(m) {
        var dlg = null;

        if (params && params.length > 0) {
          dlg = m[method].apply(m, params);
        } else {
          dlg = m[method]();
        }

        if (callback) {
          callback(dlg);
        }
      }, this);
	},
	
	/** 
	 * @param {string} v The moduleId or moduleType you want returned
	 * @param {Function} cb The Function to call when the module is ready/loaded
	 * @param {object} scope The scope in which to execute the function
	 */
	requestModule : function(v, cb, scope){
    var m = this.getModule(v);

    if(m){
			if(m.loaded === true){
        cb.call(scope, m);
			}else{
				if(cb && scope){
					this.requestQueue.push({
						id: m.id,
						callback: cb,
						scope: scope
					});
					
					this.loadModule(m.id, m.launcher.text);
				}
			}
    }
	},
	
	loadModule : function(moduleId, moduleName) {
		/*
		var notifyWin = this.desktop.showNotification({
			html: 'Loading ' + moduleName + '...'
			, title: 'Please wait'
		});
		*/
		Ext.Ajax.request({
			url: Toc.CONF.LOAD_URL,
			params: {
        module: moduleId
			},
			success: function(o){
			  /*
				notifyWin.setIconClass('x-icon-done');
				notifyWin.setTitle('Finished');
				notifyWin.setMessage(moduleName + ' loaded.');
				this.desktop.hideNotification(notifyWin);				
				notifyWin = null;
				*/
				
				if(o.responseText !== ''){
					eval(o.responseText);
					this.loadModuleComplete(true, moduleId);
				}else{
					alert('An error occured on the server.');
				}
			},
			failure: function(){
				alert('Connection to the server failed!');
			},
			scope: this
		});
	},
	
	/**
	 * @param {boolean} success
	 * @param {string} moduleId
	 * 
	 * Will be called when a module is loaded.
	 * If a request for this module is waiting in the
	 * queue, it as executed and removed from the queue.
	 */
	loadModuleComplete : function(success, moduleId){   
		if(success === true && moduleId){
		    var m = this.getModule(moduleId);
		    m.loaded = true;
		    m.init();
		    
		    var q = this.requestQueue;
		    var nq = [];
		    for(var i = 0, len = q.length; i < len; i++){
		        if(q[i].id === moduleId){
		           var dlg = q[i].callback.call(q[i].scope, m);
		        }else{
		            nq.push(q[i]);
		        }
		    }
		    this.requestQueue = nq;
		}
		
		return dlg;
		
	},
	
  /**
   * @param {string} v The moduleId or moduleType you want returned
   */
  getModule : function(v){
    var ms = this.modules;
    
    for(var i = 0, len = ms.length; i < len; i++){
      if(ms[i].id == v || ms[i].moduleType == v){
        return ms[i];
      }
    }
    
    return null;
  },
	
  /**
   * @param {Ext.app.Module} m The module to register
   */
  registerModule: function(m){
    if(!m){ return false; }
    this.modules.push(m);
    m.launcher.handler = this.createWindow.createDelegate(this, [m.moduleId]);
    m.app = this;
  },

 /**
  * @param {string} moduleId or moduleType 
  * @param {array} requests An array of request objects
  * 
  * Example:
  * this.app.makeRequest('module-id', {
	*    requests: [
	*       {
	*          action: 'createWindow',
	*          params: '',
	*          callback: this.myCallbackFunction,
	*          scope: this
	*       },
	*       { ... }
	*    ]
	* });
  */
	 makeRequest : function(moduleId, requests){
	   if(moduleId !== '' && requests){
	     var m = this.requestModule(moduleId, function(m){
	       if(m){
	         m.handleRequest(requests);
	       }
	     }, this);
	   }
	 },
	 
  /**
   * @param {Function} fn The function to call after the app is ready
   * @param {object} scope The scope in which to execute the function
   */
  onReady : function(fn, scope){
    if(!this.isReady){
      this.on('ready', fn, scope);
    }else{
      fn.call(scope, this);
    }
  },
 
	onBeforeUnload : function(e){
		if(this.fireEvent('beforeunload', this) === false){
      e.stopEvent();
		}
	},
 
  /**
   * Prevent the backspace (history -1) shortcut
   */
  preventBackspace : function(){
    var map = new Ext.KeyMap(document, [{
      key: Ext.EventObject.BACKSPACE,
      fn: function(key, e){
        var t = e.target.tagName.toUpperCase();
        if(t != "INPUT" && t != "TEXTAREA"){
          e.stopEvent();
        }
      }
    }]);
  },
  
  showNotification : function(config){
    var notifyWin = this.desktop.showNotification(config);
  }
});