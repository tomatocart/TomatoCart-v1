/*
 * office.uwd.ch
 * Copyright(c) 2007-2008, Integrated Technologies, Inc.
 * licensing@office.uwd.ch
 * 
 * http://www.office.uwd.ch/license
 */
 
ImageDragZone = function(view, config){
  var config = config || {};
  
  this.view = view;
  ImageDragZone.superclass.constructor.call(this, view.getEl(), config);
};

Ext.extend(ImageDragZone, Ext.dd.DragZone, {
  getDragData : function(e){
    var target = e.getTarget('.thumb-wrap');
    
    if (target) {
      var view = this.view;
      if(!view.isSelected(target)){
          view.onClick(e);
      }
      var selNodes = view.getSelectedNodes();
      var dragData = {
          nodes: selNodes
      };
      if (selNodes.length == 1) {
        dragData.ddel = target.cloneNode(true);
        dragData.ddel.id = Ext.id();
        dragData.single = true;
        
        var records = this.view.getSelectedRecords();
        dragData.record = records.pop();
        
        return dragData;
      }else{
        var div = document.createElement('div'); 
        div.className = 'multi-proxy';
        
        for (var i = 0, len = selNodes.length; i < len; i++) {
          div.appendChild(selNodes[i].firstChild.firstChild.cloneNode(true)); 
          
          if ((i+1) % 3 == 0) {
            div.appendChild(document.createElement('br'));
          }
        }
        var count = document.createElement('div'); 
        count.innerHTML = i + ' images selected';
        div.appendChild(count);
        
        dragData.ddel = div;
        dragData.multi = true;
      }
      return dragData;
    }
      
    return false;
  },
  
  beforeInvalidDrop: function(e) {
  	this.hideProxy();
  }
});