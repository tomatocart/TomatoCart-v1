/* 
 * Ext JS Library 2.0
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 * 
 * $Id: sidebar.js $
 * TomatoCart Open Source Shopping Cart Solutions
 * http://www.tomatocart.com
 * 
 * Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v2 (1991)
 * as published by the Free Software Foundation.
 */
 
Ext.ux.Sidebar = function(config){
	var taskbarEl = Ext.get('ux-taskbar');
	
  config = config || {};
  Ext.apply(this, config);
  
  this.codes = [];
  this.gadgets = [];
  
  this.gMargin = config.gMargin || 20;
  this.gHeight = config.gHeight || 163;
  this.topAndBottomMargin = config.topAndBottomMargin || 90;
  this.splitWidth = config.splitWidth || 5;
  this.sidebarWidth = config.sidebarWidth || 180;
  
  this.start = 0;
  this.pageSize = parseInt((Ext.lib.Dom.getViewHeight() - taskbarEl.getHeight() - this.topAndBottomMargin) / (this.gHeight + this.gMargin));
  
  this.buildSidebar();
  this.updatePageToolbar();

  this.desktopEl.setWidth(Ext.lib.Dom.getViewWidth() - this.pnlSidebar.getInnerWidth() - this.splitWidth);
  
  this.logoEl.hide();
  this.app.sidebaropened = true;
  
  this.setBackgroundTransparency(this.app.styles.sidebartransparency);
  this.setBackgroundColor(this.app.styles.sidebarbackgroundcolor);
  
  Ext.EventManager.onWindowResize(this.onWindowResize, this);
  
  this.addEvents({'gadgetload': true});
};

Ext.extend(Ext.ux.Sidebar, Ext.util.Observable, {
  buildSidebar: function() {
    this.pnlSidebar = new Ext.Panel({
      width: 180,
      minSize: 175,
      maxSize: 175,
      border: false,
      cls: 'sidebar',
      split: true,
      collapsible: true,
      collapseMode: 'mini',
      region: 'east',
      tbar : [{
        xtype: 'panel',
        id: 'sidebar-panel-site-logo',
        border: false,
        height: 50,
        items: {
          border: false, 
          html: '<a href="http://www.tomatocart.com" target="_blank"><img src="images/power_by_button.png" height=50 width=100 /></a>'
        }
      },
      {
        cls: 'sidebar-tbar-add',
        handler: this.configure,
        handleMouseEvents: false,
        scope: this
      }, 
      this.btnPrevious = new Ext.Button({
        cls: 'sidebar-tbar-previous',
        handler: this.previous,
        handleMouseEvents: false,
        scope: this
      }),
      this.btnNext = new Ext.Button({
        cls: 'sidebar-tbar-next',
        handler: this.next,
        handleMouseEvents: false,
        scope: this
      }),
      {
        cls: 'sidebar-tbar-close',
        handler: this.hide,
        handleMouseEvents: false,
        scope: this
      }],
      items: [this.pnlGadgets = new Ext.Panel({baseCls: 'sidebar-items-panel'})],
      listeners: {
	      render: this.onSidebarRender,
	      collapse: function() {this.collapse(true);},        
	      expand: function() {this.collapse(false);},
	      scope: this
      }
    });
    
    this.pnlMain = new Ext.Panel({
      applyTo: this.sidebarEl,
      border: false,
      layout: 'border',
      width: this.sidebarWidth,
      items: [
        this.pnlSidebar,
        {
          xtype: 'panel',
          border: false,
          region: 'center'
        }
      ]
    });
    
    this.sidebarBgEl.setWidth(this.pnlSidebar.getInnerWidth());
    this.sidebarBgEl.setHeight(this.sidebarEl.getHeight());
  },

  onSidebarResized: function() {
    this.app.desktop.getManager().each(function(win) {
      if (win.maximized) {
        win.fireEvent('maximize', win);
      }
      
      win.center();
    });
  },

  hide: function() {
    Ext.TaskMgr.stopAll();
    
    this.pnlMain.hide();
    this.sidebarBgEl.hide();
    this.app.sidebaropened = false;
    
    this.desktopEl.setWidth(Ext.lib.Dom.getViewWidth());   
    this.logoEl.setVisible(true);
    
    this.onSidebarResized();
  },
  
  show: function() {
    this.pnlMain.setVisible(true);
    this.sidebarBgEl.setVisible(true);
    this.app.sidebaropened = true;
    this.logoEl.hide();
    this.desktopEl.setWidth(Ext.lib.Dom.getViewWidth() - this.pnlSidebar.getInnerWidth() - this.splitWidth);

    this.startAllTasks();

    this.onSidebarResized();
  },
  
  collapse: function(collapsed) {
    this.app.sidebarcollapsed = collapsed;

    this.sidebarBgEl.setVisible(!collapsed);
    this.logoEl.setVisible(collapsed);

    if (collapsed == true) {
      this.desktopEl.setWidth(Ext.lib.Dom.getViewWidth() - this.splitWidth);
    } else {
      this.desktopEl.setWidth(Ext.lib.Dom.getViewWidth() - this.pnlSidebar.getInnerWidth() - this.splitWidth);
    }

    this.onSidebarResized();
  },
  
  startAllTasks: function() {
    if (this.gadgets.length > 0) {
      this.pnlGadgets.items.each(function(gadget) {
        if (gadget.autorun == true) {
          Ext.TaskMgr.start(gadget.runner);
        }
      });
    }
  },
  
  onWindowResize: function() {
    if ((this.app.sidebaropened == true) && (this.app.sidebarcollapsed == false)) {
	    var taskbarEl = Ext.get('ux-taskbar');
	    
	    this.sidebarEl.setHeight(Ext.lib.Dom.getViewHeight() - taskbarEl.getHeight());
	    this.sidebarBgEl.setHeight(Ext.lib.Dom.getViewHeight() - taskbarEl.getHeight());
	    this.pnlMain.setHeight(Ext.lib.Dom.getViewHeight() - taskbarEl.getHeight());
	      
	    this.pageSize = parseInt((Ext.lib.Dom.getViewHeight() - taskbarEl.getHeight() - this.topAndBottomMargin) / (this.gHeight + this.gMargin));
	    
	    this.start = 0;
	    this.gotoPage(this.start);
    }
  }, 

  setBackgroundTransparency: function(v) {
    if (v >= 0 && v <= 100) {
      this.sidebarBgEl.addClass("sidebar-transparency");

      Ext.util.CSS.updateRule('.sidebar-transparency','opacity', v/100);
      Ext.util.CSS.updateRule('.sidebar-transparency','-moz-opacity', v/100);
      Ext.util.CSS.updateRule('.sidebar-transparency','filter', 'alpha(opacity='+v+')');

      this.app.styles.sidebartransparency = v;
    }
  },

  setBackgroundColor: function(hex) {
    if (hex) {
      this.sidebarBgEl.setStyle('background-color', '#' + hex);
      this.app.styles.sidebarbackgroundcolor = hex;
    }
  },

  configure: function() {
   if ( Ext.isEmpty(Ext.get('desktop-setting-win')) ) {
      var desktopSettingWindow = this.app.getDesktopSettingWindow();
      desktopSettingWindow.show();
      desktopSettingWindow.activeSidebarPanel();
   }else {
    return false;
   }
  },

  onSidebarRender: function() {
    var ddrow = new Ext.dd.DropTarget(this.pnlSidebar.getEl(), {
      ddGroup : 'GadgetsDD',
      copy: false,
      notifyDrop : function(dd, e, data) {  
        this.addGadget(data.record.get('code'), true);

        return true;
      }.createDelegate(this)
    });
                                  
    this.pnlSidebar.getTopToolbar().addClass('sidebar-top-bar');  
  },

  updatePageToolbar: function() {
    if ( this.gadgets.length > (this.pageSize * (this.start + 1)) ) {
      this.btnNext.enable();
      this.btnNext.removeClass('sidebar-tbar-next-disabled');
    }else {
      this.btnNext.disable();
      this.btnNext.addClass('sidebar-tbar-next-disabled');
    }

    if (this.start > 0) {
      this.btnPrevious.enable();
      this.btnPrevious.removeClass('sidebar-tbar-previous-disabled');
    }else {
      this.btnPrevious.disable();
      this.btnPrevious.addClass('sidebar-tbar-previous-disabled');
    }
  },
  
  hideGadget: function(gadget) {
    if (gadget.isVisible() == true) { 
	    gadget.hide();
	    this.pnlGadgets.doLayout();
	    
	    if (gadget.autorun == true) {
	      Ext.TaskMgr.stop(gadget.runner);
	    }
    }
  },
  
  showGadget: function(gadget) {
    gadget.show();
    this.pnlGadgets.doLayout();
    
    if (gadget.autorun == true) {
      Ext.TaskMgr.start(gadget.runner);
    }
    
    this.addCloseButton(gadget);
    
    if (gadget.type == 'flash') {
      this.renderFlash(gadget);
    }
  },

  gotoPage: function(start) {
    if (this.gadgets.length > 0) {
	    var count = Math.min((this.gadgets.length - this.pageSize * start), this.pageSize);
	    var gadgets = this.gadgets.slice(this.pageSize * start, (this.pageSize * start) + count);
	    
	    this.pnlGadgets.items.each(function(gadget) {
	      this.hideGadget(gadget);
	    }, this);
	
	    Ext.each(gadgets, function(gadget) {
	      this.showGadget(gadget);
	    }, this);
	    
	    this.updatePageToolbar();
	  }
  },

 	previous: function() {
		this.gotoPage(--this.start);
  },

  next: function() {
		this.gotoPage(++this.start);
  },

  contains: function (code) {
    for (i = 0; i < this.codes.length; i++) {
      if (code == this.codes[i]) {
        return true;
      }
    }

    return false;
  },

  loadGadget: function() {
    if (this.queue.length > 0) {
      var code = this.queue.shift();
      alert(code);
      
      var params = {
        action: 'get_gadget',
        gadget: code  
      };
      
      this.sendRequest(params, function(options, success, response) {
        var result = Ext.decode(response.responseText);                       

        if (result.success == true) {
          this.buildGadget(result.data, false);
        }
        
        this.loadGadget();
      }, this);
    }
  },

  addGadgets: function(gadgets, save) {
    var gadgetsQueue = gadgets;
    
    if (gadgetsQueue.length > 0) {
      var code = gadgetsQueue.shift();
      this.addGadget(code, save);
      
      this.on('gadgetload', function (){
        if (gadgetsQueue.length > 0) {
          var g = gadgetsQueue.shift();
          this.addGadget(g, save);
        }
      }, this);
    }
  },

  addGadget: function(code, save) {    
    if ( !this.contains(code) ) {
      var params = {
        action: 'get_gadget',
        gadget: code  
      };
      
      this.sendRequest(params, function(options, success, response) {
        var result = Ext.decode(response.responseText);                       

        if (result.success == true) {
          this.buildGadget(result.data, save);
        }
      }, this);
    }
  },
  
  sendRequest: function(params, fnCallback, scope) {
    params.module = 'desktop_settings';

    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: params,
      callback: fnCallback,
      scope: scope
    });
  },
  
  buildGadget: function (data, save) {
    var params = {
			action: 'get_gadget_view',
			gadget: data.code  
    };
    
    this.sendRequest(params, function(options, success, response) {
		  var result = Ext.decode(response.responseText);                       
		          
		  if (result.success == true) {
		    data.plugins = new Ext.ux.Sidebar.GadgetCloseTool(this);
		    data.app = this.app;
		              
		    if (data.type == 'flash') {
		      var gadget = new Ext.ux.Gadget(
		        Ext.applyIf(data, {
		          id: data.code + '-container',
		          code: data.code,
		          height: 200,
		          title: ' ',
		          layout: 'fit',
		          innerHeight: 148,
		          border: false
		        })
		      );
		      
		      gadget.toolView = result.view;
		    } else if (data.type == 'grid') {
		      var gadget = new Ext.ux.Gadget(Ext.applyIf(data, result.view));
		    }
		    
		    if (gadget.autorun == true) {
		      gadget.runner = {
		        run: function() {
		          gadget.task();
		        }, 
		        interval: gadget.interval
		      };
		    }
		    this.pnlGadgets.add(gadget);
		    this.pnlGadgets.doLayout();
		        
		    //insert at current page
		    if ((this.start + 1) * this.pageSize > this.gadgets.length ) {
		      this.showGadget(gadget);
		    } else {
		      gadget.hide();
		    }
		       
		    this.codes.push(gadget.code);
		    this.gadgets.push(gadget);    
		    this.updatePageToolbar();
		    
		    if (save == true) {
		      this.saveGadgets();
		    }
		    
		    this.fireEvent('gadgetload');
		  }  
		}, this);
  },
  
  renderFlash: function(gadget) {
    gadget.getEl().select('.x-panel-bwrap .x-panel-body').each(function(toolView) {
    	toolView.insertHtml('beforeEnd', '<div id="tool-gadget-' + gadget.code + '" style="border:0;height:145px;" class="too-gadget"></div>');
	    
	    eval(gadget.toolView);
	    
      toolView.select('div').each(function(toolViewContainer) {
        toolViewContainer.remove();
      });
    });
  },
  
  addCloseButton: function(gadget) {
    var btnClose = Ext.select('#' + gadget.getId() + ' .x-panel-header');
    var gadgetEl = Ext.get(gadget.getId());
    
    gadgetEl.on({
      mouseover: {
        fn: function(e) {           
          if ( !e.within(gadgetEl, true) ) {              
            btnClose.setVisible(true, true);
          }          
        }
      },
      mouseout: {
        fn: function(e) {
          if ( !e.within(gadgetEl, true) ) {              
            btnClose.setVisible(false, true);            
          }
        }        
      }
    });      
  },
  
  removeGadget: function(gadget) {
    var pos = 0;
    for(i = 0; i < this.gadgets.length; i++) {
      if (gadget.getId() == this.gadgets[i].getId()) {
        pos = i;
        break;
      }
    }
    
    this.hideGadget(gadget);
    this.pnlGadgets.remove(gadget);

    var nextPos = (parseInt(pos / this.pageSize) + 1) * this.pageSize;
    if (nextPos < this.gadgets.length) {
      var g = this.gadgets[nextPos];
      this.showGadget(g);
    }
    
    this.codes.remove(gadget.code);
    this.gadgets.remove(gadget);
    this.updatePageToolbar();
    this.saveGadgets();
  },
  
  saveGadgets: function() {
  	var params =  {
      action: 'save_gadgets',
      gadgets: Ext.encode(this.codes) 
  	};
  	
  	this.sendRequest(params, function(options, success, response) {
      var result = Ext.decode(response.responseText);
         
      if(result.success == false) {
        Ext.MessageBox.alert(TocLanguage.msgErrTitle, TocLanguage.connServerFailure);
      }
  	}, this);
  }
});

Ext.ux.Sidebar.GadgetCloseTool = function(sideBar) {  
  this.init = function(gadget) {
    gadget.tools = [
      {
        id:'close',
        handler: function(e, target, panel){
          panel.ownerCt.remove(panel, true);
          
          sideBar.removeGadget(gadget); 
        }
      }
    ];        
  };    
};