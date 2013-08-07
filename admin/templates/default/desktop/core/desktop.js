/*
 * qWikiOffice Desktop 0.8.1
 * Copyright(c) 2007-2008, Integrated Technologies, Inc.
 * licensing@qwikioffice.com
 * 
 * http://www.qwikioffice.com/license
 *
 * NOTE:
 * This code is based on code from the original Ext JS desktop demo.
 * I have made many modifications/additions.
 *
 * The Ext JS licensing can be viewed here:
 *
 * Ext JS Library 2.0 Beta 2
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

Ext.Desktop = function(app){
  var thisObj = this;
	
  this.el = Ext.get('x-desktop');
  var desktopEl = this.el;
  
  //taskbar
  this.taskbar = new Ext.ux.TaskBar(app);
  var taskbar = this.taskbar;
  var taskbarEl = Ext.get('ux-taskbar');

  //shortcuts
  this.shortcuts = new Ext.ux.Shortcuts({
    renderTo: 'x-desktop',
    taskbarEl: taskbarEl
  });

  //sidebar
  if (app.sidebaropened) {
    buildSidebar();
  }
  var sidebar = this.sidebar;
  var sidebarEl = Ext.get('ux-sidebar');
  
  var windows = new Ext.WindowGroup();
  Ext.WindowMgr.zseed = 10000;
  var activeWindow;

  function minimizeWin(win){
    win.minimized = true;
    win.hide();
  }
  
  function maximizeWin(win) {
    win.maximized = true;
    if (!Ext.isEmpty(win.footer)) {
      win.setHeight(Ext.lib.Dom.getViewHeight() - taskbarEl.getHeight() - win.footer.getHeight());
    }else {
      win.setHeight(Ext.lib.Dom.getViewHeight() - taskbarEl.getHeight());
    }
  }

  function markActive(win) {
    if(activeWindow && activeWindow != win){
      markInactive(activeWindow);
    }
    
    taskbar.setActiveButton(win.taskButton);
    activeWindow = win;
    Ext.fly(win.taskButton.el).addClass('active-win');
    win.minimized = false;
  }

  function markInactive(win){
    if(win == activeWindow){
      activeWindow = null;
      Ext.fly(win.taskButton.el).removeClass('active-win');
    }
  }

  function removeWin(win){
    taskbar.taskButtonPanel.remove(win.taskButton);
    layout();
  }

  function buildSidebar() {
    thisObj.sidebar = new Ext.ux.Sidebar({
      desktopEl: Ext.get("x-desktop"), 
      sidebarEl: Ext.get('ux-sidebar'),
      sidebarBgEl: Ext.get('ux-sidebar-background'),
      logoEl: Ext.get('tomatocart-logo'),
      app: app});
  }
  
  function centerWindows() {
    windows.each(function(win) { win.center()});
  }
  
  function layout(){
    desktopEl.setHeight(Ext.lib.Dom.getViewHeight() - taskbarEl.getHeight());
    
    if (app.sidebaropened) {
      if (app.sidebarcollapsed == false) {
        desktopEl.setWidth(Ext.lib.Dom.getViewWidth() - sidebarEl.getWidth());
      }else {
       desktopEl.setWidth(Ext.lib.Dom.getViewWidth() - thisObj.sidebar.splitWidth);
      }
    } else {
      desktopEl.setWidth(Ext.lib.Dom.getViewWidth());
    }
  }
  
  Ext.EventManager.onWindowResize(layout);

  this.layout = layout;
  
  this.addSidebar = function() {
    buildSidebar();
  };

  this.hideSidebar = function() {
    this.sidebar.hide();
    this.layout();
  };
  
  this.showSidebar = function() {
    if (Ext.isEmpty(thisObj.sidebar)) {
      buildSidebar();
    }

    thisObj.sidebar.show();
    this.layout();
  };

  this.createWindow = function(config, cls){
    var win = new (cls||Ext.Window)(
      Ext.applyIf(config||{}, {
        manager: windows,
        minimizable: true,
        maximizable: true
      })
    );

    win.render(desktopEl);
    win.taskButton = taskbar.taskButtonPanel.add(win);
      
    win.on('titlechange', function(p, title){
      win.taskButton.setText(title);
    });

    win.cmenu = new Ext.menu.Menu({
      items: [

      ]
    });

    //disable window open up animation to improve performance under IE and Opera
    //
    if ((Ext.isIE === false) && (Ext.isOpera === false)) {
      //win.animateTarget = win.taskButton.el;
    }
      
    win.on({
      'activate': {
        fn: markActive
      },
      'beforeshow': {
        fn: markActive
      },
      'deactivate': {
        fn: markInactive
      },
      'minimize': {
        fn: minimizeWin
      },
      'maximize': {
        fn: maximizeWin
      },
      'close': {
        fn: removeWin
      }
    });
      
    layout();
    return win;
  };    
    
  this.getManager = function(){
    return windows;
  };

  this.getWindow = function(id){
    return windows.get(id);
  };
  
  this.getViewHeight = function(){
    return (Ext.lib.Dom.getViewHeight()-taskbarEl.getHeight());
  };
    
  this.getViewWidth = function(){
    return Ext.lib.Dom.getViewWidth();
  };
    
  this.getWinWidth = function(){
    var width = Ext.lib.Dom.getViewWidth();
    return width < 200 ? 200 : width;
  };
    
  this.getWinHeight = function(){
    var height = (Ext.lib.Dom.getViewHeight()-taskbarEl.getHeight());
    return height < 100 ? 100 : height;
  };
    
  this.getWinX = function(width){
    return (Ext.lib.Dom.getViewWidth() - width) / 2
  };
    
  this.getWinY = function(height){
    return (Ext.lib.Dom.getViewHeight()-taskbarEl.getHeight() - height) / 2;
  };
  
  this.getTaskbar = function() {
    return this.taskbar;
  }
  
  this.setBackgroundColor = function(hex){
    if(hex){
      Ext.get(document.body).setStyle('background-color', '#'+hex);
      app.styles.backgroundcolor = hex;
    }
  };
  
  this.setFontColor = function(hex){
    if(hex){
      Ext.util.CSS.updateRule('.ux-shortcut-btn-text', 'color', '#'+hex);
      app.styles.fontcolor = hex;
    }
  };
  
  this.setTheme = function(o){
    if(o && o.code && o.path){
      Ext.util.CSS.swapStyleSheet('theme', o.path);
      app.styles.theme = o.code;
    }
  };
  
  this.setTransparency = function(v){
    if(v >= 0 && v <= 100){
      taskbarEl.addClass("transparent");
      Ext.util.CSS.updateRule('.transparent','opacity', v/100);
      Ext.util.CSS.updateRule('.transparent','-moz-opacity', v/100);
      Ext.util.CSS.updateRule('.transparent','filter', 'alpha(opacity='+v+')');
      
      app.styles.transparency = v;
    }
  };
  
  this.setWallpaper = function(o){
    if(o && o.code){
//      var notifyWin = this.showNotification({
//        html: TocLanguage.LoadWallpaper, 
//        title: 'Please wait'
//      });
      
      var wp = new Image();
      wp.src = o.path;
      
      var task = new Ext.util.DelayedTask(verify, this);
      task.delay(200);
      
      app.styles.wallpaper = o.code;
    }
    
    function verify(){
      if(wp.complete){
        task.cancel();
        
//        notifyWin.setIconClass('x-icon-done');
//        notifyWin.setTitle('Finished');
//        notifyWin.setMessage('Wallpaper loaded.');
//        this.hideNotification(notifyWin);
        
        document.body.background = wp.src;
      }else{
        task.delay(200);
      }
    }
  };
  
  this.setWallpaperPosition = function(pos){
    if(pos){
      if(pos === "center"){
        var b = Ext.get(document.body);
        b.removeClass('wallpaper-tile');
        b.addClass('wallpaper-center');
      }else if(pos === "tile"){
        var b = Ext.get(document.body);
        b.removeClass('wallpaper-center');
        b.addClass('wallpaper-tile');
      }     
      app.styles.wallpaperposition = pos;
    }
  };
  
  this.showNotification = function(config){
    var win = new Ext.ux.Notification(Ext.apply({
      animateTarget: taskbarEl
      , autoDestroy: true
      , hideDelay: 3000
      , html: ''
      , iconCls: 'x-icon-waiting'
      , title: ''
    }, config));
    win.show();

    return win;
  };
  
  this.showDesktopSettingWin = function() {
    app.getDesktopSettingWindow().show();
  };
  
  this.hideNotification = function(win, delay){
    if(win){
      (function(){ win.animHide(); }).defer(delay || 3000);
    }
  };
  
  this.addAutoRun = function(id){
    var m = app.getModule(id),
        c = app.launchers.autorun;
      
    if(m && !m.autorun){
      m.autorun = true;
      c.push(id);
    }
  };
  
  this.removeAutoRun = function(id){
    var m = app.getModule(id),
      c = app.launchers.autorun;
    if(m && m.autorun){
      var i = 0;
        
      while(i < c.length){
        if(c[i] == id){
          c.splice(i, 1);
        }else{
          i++;
        }
      }
      
      m.autorun = null;
    }
  };
  
  // Private
  this.addContextMenu = function(id, updateConfig){
    var m = app.getModule(id);
    if(m && !m.contexmenu) {
      /* if(m.moduleType === 'menu'){ // handle menu modules
      var items = m.items;
        for(var i = 0, len = items.length; i < len; i++){
          m.launcher.menu.items.push(app.getModule(items[i]).launcher);
        }
      } */
	    this.cmenu.add(m.launcher);
	            
	    if(updateConfig){
	      app.launchers.contextmenu.push(id);
	    }
    }
  };
    
  this.removeContextMenu = function(id, updateConfig) {
    var m = app.getModule(id);

    if (m) {
      var items = this.cmenu.items.items;
      for(var i = 0; i< items.length; i++) {
        if(items[i].iconCls == m.launcher.iconCls) {
          this.cmenu.remove(items[i]);
        }
      }
      
      if(updateConfig){
        var dc = app.launchers.contextmenu;
        var i = 0;
          
        while(i < dc.length){
          if(dc[i] == id){
             dc.splice(i, 1);
          }else{
            i++;
          }
        }
      }
    }
  };

  this.addShortcut = function(id, updateConfig){
    var m = app.getModule(id);
    
    if(m && !m.shortcut){
      var c = m.launcher;
      
      m.shortcut = this.shortcuts.addShortcut({
        handler: c.handler,
        iconCls: c.shortcutIconCls,
        scope: c.scope,
        text: c.text,
        tooltip: c.tooltip || ''
      });
      
      if(updateConfig){
        app.launchers.shortcut.push(id);
      }
    }
  };

  this.removeShortcut = function(id, updateConfig){
    var m = app.getModule(id);
    
    if(m && m.shortcut){
      this.shortcuts.removeShortcut(m.shortcut);
      m.shortcut = null;
      
      if(updateConfig){
        var sc = app.launchers.shortcut,
        i = 0;
        while(i < sc.length){
          if(sc[i] == id){
            sc.splice(i, 1);
          }else{
            i++;
          }
        }
      }
    }
  };
  
  this.addQuickStartButton = function(id, updateConfig){
    var m = app.getModule(id);
      
    if(m && !m.quickStartButton){
      var c = m.launcher;
      
      m.quickStartButton = this.taskbar.quickStartPanel.add({
        handler: c.handler,
        iconCls: c.iconCls,
        scope: c.scope,
        text: c.text,
        tooltip: c.tooltip || c.text
      });
      
      if(updateConfig){
        app.launchers.quickstart.push(id);
      }
    }
  };
    
  this.removeQuickStartButton = function(id, updateConfig) {
    var m = app.getModule(id);
      
    if(m && m.quickStartButton){
      this.taskbar.quickStartPanel.remove(m.quickStartButton);
      m.quickStartButton = null;
      
      if(updateConfig){
        var qs = app.launchers.quickstart,
          i = 0;
        while(i < qs.length){
          if(qs[i] == id){
            qs.splice(i, 1);
          }else{
            i++;
          }
        }
      }
    }
  };

  layout();
    
  this.cmenu = new Ext.menu.Menu();
    
  desktopEl.on('contextmenu', function(e){
    if(e.target.id === desktopEl.id){
      e.stopEvent();
      
      if(app.launchers.contextmenu.length > 0) {
	      if(!this.cmenu.el){
	        this.cmenu.render();
	      }
	      var xy = e.getXY();
	      xy[1] -= this.cmenu.el.getHeight();
	      this.cmenu.showAt(xy);
	    }
    }
  }, this);
};