<?php
/*
  $Id: dashboard.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.dashboard.Dashboard = function(config) {

  config = config || {};
  
  config.id = 'dashboard-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.layout = 'border';
  config.width = 1000;
  config.height = 500;
  config.iconCls = 'icon-dashboard-win';
  config.items = [this.getPortletsPanel(), this.getPortal()];
  
  config.listeners = {
    afterlayout: this.renderFlashPortlets,
    scope: this
  };
  
  this.portlets = [];  
  this.pnlPortal.on('drop', this.onPortletDrop, this);
  
  Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
      
  Toc.dashboard.Dashboard.superclass.constructor.call(this, config);
};

Ext.extend(Toc.dashboard.Dashboard, Ext.Window, {

  show: function() {
    Toc.dashboard.Dashboard.superclass.show.call(this);
    this.loadPortlets();
  },
  
  onPortletDrop: function(e) {
    for(var i = 0; i < this.portlets.length; i++) {
      if (e.panel.code == this.portlets[i].code) {
        this.portlets[i].col = e.columnIndex;
      }
    }
    
    this.saveDashboardLayout();
  },
  
  saveDashboardLayout: function() {
    var portlets = [];  
    Ext.each(this.portlets, function(portlet) {
      portlets.push(portlet.code + ':' + portlet.col);
    });  
         
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: { 
        module: 'dashboard',
        action: 'save_dashboard_layout',
        portlets: portlets.join(',')                           
      }
    });      
  },
  
  loadPortlets: function() {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'dashboard',
        action: 'load_portlets'
      },
      callback: function(options, success, response) {
        result = Ext.decode(response.responseText);
        
        if (result.success === true) {
          var portlets = result.portlets.split(',');
          
          for (var i = 0; i < portlets.length; i++) {
            var portlet = portlets[i].split(':');
            this.addPortlet(portlet[0], portlet[1], false);
          }
        }
      },
      scope: this
    });
  },
  
  addPortlet: function(code, col, saveLayout) {
    for(var i = 0; i < this.portlets.length; i++) {
      if (code == this.portlets[i].code) {
        return;
      }
    }
    
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: { 
       module: 'dashboard',
       action: 'get_portlet_view',
       portlet: code                                        
      },
      callback: function(options, success, response) {
        result = Ext.decode(response.responseText);
      
        if (result.success == true) {
          result.view.tools = [{
            id:'close',
            handler: function(e, target, panel){
              panel.ownerCt.remove(panel, true);
      
              for(i = 0; i < this.portlets.length; i++) {
                if (code == this.portlets[i].code) {
                  this.portlets.splice(i, 1);
                }
              }
              
              this.saveDashboardLayout();
            },
            scope: this
          }];
                      
          var portlet = new Ext.ux.Portlet(result.view);
          
          if ((col != undefined) && (col >= 0)) {
            this.pnlPortal.items.get(col).add(portlet);
            this.portlets.push({code: code, col: col});
          }else {
            var cols = [];
            cols[0] = this.pnlPortal.items.get(0).items.length;
            cols[1] = this.pnlPortal.items.get(1).items.length;
            cols[2] = this.pnlPortal.items.get(2).items.length;
            
            i = (cols[0] <= cols[1]) ? 0 : 1;
            i = (cols[i] <= cols[2]) ? i : 2;

            var column = this.pnlPortal.items.get(i);
            column.add(portlet);            
            this.portlets.push({code: code, col: i});
          }
            
          if ((saveLayout == undefined) || (saveLayout == true)) {
            this.saveDashboardLayout();
          }
                    
          this.pnlPortal.doLayout();
        } 
      },
      scope: this                     
    });
  },
  
  buildDropTarget: function(t, n, e) {
    var scope = this;
    var DropTargetEl = this.pnlPortal.getEl();
    var DropTarget = new Ext.dd.DropTarget(DropTargetEl, {
	    ddGroup: 'portalPanel',
			copy: true,
			notifyDrop: function(){
			  scope.addPortlet(n.id);
			  return (true);
      }
    });
  },
  
  renderFlashPortlets: function() {
    var items = this.pnlPortal.items;
    
    for (var i = 0; i< items.getCount(); i++) {
      var c = items.get(i);
      c.items.each(function(portlet) {
        if (portlet.renderFlash) {
          portlet.renderFlash();
        }
      });
    }    
  },
    
  getPortletsPanel: function() {
    var pnlPortlets = new Ext.tree.TreePanel({
      region: 'west',
      title: 'Portlets',
      split: true,
      width: 175,
      minSize: 175,
      collapsible: true,
      animCollapse: false, 
      collapsed: true,
      maxSize: 250,
      margins: '0 0 0 0',
      cmargins: '0 5 0 0',    
      ddGroup: 'portalPanel',
      enableDD: true,
      autoScroll: true,
      rootVisible: false,
      anchor:'-24 -60',
      border: false,
      root: {
        nodeType: 'async',
        text: 'root',
        id: 'root',
        expanded: true,
        uiProvider: false
      },
      loader: new Ext.tree.TreeLoader({
        dataUrl: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'dashboard',
          action: 'get_portlet_nodes'
        }
      }),
      listeners: {
        'dblclick': {
          fn: function(n) {
            this.addPortlet(n.id);
          },
          scope: this
        },
        'startdrag': {
          fn: this.buildDropTarget,
          scope: this
        }        
      }   
    });  
    
    return pnlPortlets;    
  },
  
  getPortal: function() {
    this.pnlPortal = new Ext.ux.Portal({
      region: 'center',
      margins: '5 5 5 0',
      border: false,
      items:[
        {
          columnWidth: .33,
          style: 'padding: 10px 0 10px 10px'
        },
        {
          columnWidth: .33,
          style: 'padding: 10px 0 10px 10px'
        },
        {
          columnWidth: .33,
          style: 'padding: 10px'
        }
      ]
    }); 
    
    return this.pnlPortal;
  }
  
});