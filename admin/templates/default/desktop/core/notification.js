/*
 * qWikiOffice Desktop 0.8.1
 * Copyright(c) 2007-2008, Integrated Technologies, Inc.
 * licensing@qwikioffice.com
 * 
 * http://www.qwikioffice.com/license
 *
 * Ext.ux.Notification is based on code from the Ext JS forum.
 * I have made some minor modifications.
 */

Ext.ux.NotificationMgr = {
    positions: []
};

Ext.ux.Notification = Ext.extend(Ext.Window, {
  initComponent : function(){
    Ext.apply(this, {
      iconCls: this.iconCls || 'x-icon-information'
      , width: 200
      , autoHeight: true
      , closable: true
      , plain: false
      , draggable: false
      , bodyStyle: 'text-align:left;padding:10px;'
      , resizable: false
    });
    if(this.autoDestroy){
      this.task = new Ext.util.DelayedTask(this.animHide, this);
    }else{
      this.closable = true;
    }
    Ext.ux.Notification.superclass.initComponent.call(this);
    }

  , setMessage : function(msg){
    this.body.update(msg);
  }
  
  , setTitle : function(title, iconCls){
        Ext.ux.Notification.superclass.setTitle.call(this, title, iconCls||this.iconCls);
    }

  , onRender : function(ct, position) {
    Ext.ux.Notification.superclass.onRender.call(this, ct, position);
  }

  , onDestroy : function(){
    Ext.ux.NotificationMgr.positions.remove(this.pos);
    Ext.ux.Notification.superclass.onDestroy.call(this);
  }

  , afterShow : function(){
    Ext.ux.Notification.superclass.afterShow.call(this);
    this.on('move', function(){
      Ext.ux.NotificationMgr.positions.remove(this.pos);
      if(this.autoDestroy){
        this.task.cancel();
      }
    }, this);
    if(this.autoDestroy){
      this.task.delay(this.hideDelay || 5000);
    }
  }

  , animShow : function(){
    this.pos = 0;
    while(Ext.ux.NotificationMgr.positions.indexOf(this.pos)>-1){
      this.pos++;
    }
    Ext.ux.NotificationMgr.positions.push(this.pos);
    this.setSize(200,100);
    //change the x offset from -1 to -5
    this.el.alignTo(this.animateTarget || document, "br-tr", [ -5, -1-((this.getSize().height+10)*this.pos) ]);
//    this.el.show();
    
    this.el.slideIn('b', {
      duration: .7
      , callback: function() {this.afterShow()}
      , scope: this
    });
  }

  , animHide : function(){
    Ext.ux.NotificationMgr.positions.remove(this.pos);
    //if is IE, then directly desktop window instead of animation
    if (Ext.isIE === false) {
      this.el.ghost("b", {
        duration: 1
        , remove: true
      });
    } else {
      Ext.ux.Notification.superclass.close.call(this);
    }
  }
});