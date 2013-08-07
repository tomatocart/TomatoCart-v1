/**************************************************************

  Script  : Image Menu
  Version : 1.0
  Authors : Zheng Lei
  Desc  :   this version is based on Image Menu from Samuel Birch
  Licence : GNU General Public License v2 (1991) 

**************************************************************/

var ImageMenu = new Class({
  Implements: Options,
  
  options: {
    openWidth: 200,
    closeWidth: 20,
    transition: Fx.Transitions.Quart.easeInOut,
    duration: 300,
    selected: 0,
    border: 0,
    interval: 6000
  },

  initialize: function(elements, options){
    this.setOptions(options);
    this.elements = $$(elements);
    this.selected = this.options.selected;
    this.fx = new Fx.Elements(this.elements, {link: 'cancel', duration: this.options.duration, transition: this.options.transition});
    
    this.elements.each(function(el,i){
      el.addEvent('mouseenter', function(e){
        e.preventDefault();
        this.stop();
        if(i != this.selected) {this.reset(i);}        
      }.bind(this));
      
      el.addEvent('mouseleave', function(e){
        e.preventDefault();
        this.play();        
      }.bind(this));
    }.bind(this));
    
    this.reset(this.selected);
    this.play();
    
  },

  play: function(){
    this.stop();
    this._play = this.next.periodical(this.options.interval, this);
  },
  
  next:function(){
    this.selected = (this.selected + 1) % this.elements.length;
    this.reset(this.selected);
  },
  
  stop: function(){
    $clear(this._play);
  },
  
  reset: function(index){
    var width = this.options.closeWidth;
    if((index + 1) == this.elements.length){
      width += this.options.border;
    }

    var obj = {};
    this.elements.each(function(el,i){
      var w = width;
      if(i == this.elements.length-1){
        w = width+5
      }
      obj[i] = {'width': w};
    }.bind(this));
    
    obj[index] = {'width': this.options.openWidth};
    this.selected = index;    
    this.fx.start(obj);
  }
});