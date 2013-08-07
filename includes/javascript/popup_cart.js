/*
  $Id: popup_cart.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

var PopupCart = new Class({
  Implements: [Options],
  options: {
    remoteUrl: 'json.php',
    sessionName: 'sid',
    sessionId: null,
    isCartExpanded: false,
    triggerEl: $('popupCart'),
    container: $('pageHeader'),
    clsCollapsed: 'cartCallpased',
    clsExpanded: 'cartExpanded',
    clsCartText: 'cartText',
    relativeTop: 20,
    relativeLeft: 222
  },
  
  
  initialize: function(options) {
    this.setOptions(options);
    this.registerEvents();
  },
  
  registerEvents: function() {
    this.options.triggerEl.addEvents({
      'click': function(e) {
        e.stop();
        
        if (this.options.isCartExpanded == false) {
          this.getShoppingCart();
          
          $$('.' + this.options.clsCartText).each(function(text) {
            if (text.hasClass(this.options.clsCollapsed)) {
              text.removeClass(this.options.clsCollapsed);
            }
            
            text.addClass(this.options.clsExpanded);
          }.bind(this));
        }else {
          this.cartContainer.fade('out');
          
          this.options.isCartExpanded = false;
          
          $$('.' + this.options.clsCartText).each(function(text) {
            if (text.hasClass(this.options.clsExpanded)) {
              text.removeClass(this.options.clsExpanded);
            }
            
            text.addClass(this.options.clsCollapsed);
          }.bind(this));
        }
      }.bind(this)
    });
  },
  
  getShoppingCart: function() {
    var scope = this;
    
    var data = {
      template: this.options.template,
      module: 'popup_cart', 
      action: 'get_cart_contents'
    };
    data[this.options.sessionName] = this.options.sessionId;
    
    var loadRequest = new Request({
      url: this.options.remoteUrl,
      data: data,
      onSuccess: this.displayCart.bind(scope)
    }).send();
  },
  
  displayCart: function(response) {
    var result = JSON.decode(response);
    var _this = this;

    if (result.success == true) {
      if (!$defined(this.cartContainer)) {
        var pos = this.options.triggerEl.getCoordinates();
        
        this.cartContainer = new Element('div', {
          'html': result.content,
          'id': 'popupCartContent',
          'class': 'moduleBox',
          'styles': {
            'position': 'absolute',
            'top': pos.top + this.options.relativeTop,
            'left': pos.left - this.options.relativeLeft    
          }
        });
        
        this.cartContainer.addEvent('click', function(e) {
            e.stop();
        });
        
        $(document.body).addEvent('click', function(e) {
            var display = _this.cartContainer.getStyle('display');
            
            if (display == 'block') {
                _this.cartContainer.fade('out');
                
                _this.options.isCartExpanded = false;
            }
        });
      } else {
        this.cartContainer.set('html', result.content);
      }
      
      this.options.container.adopt(this.cartContainer);
      this.cartContainer.setStyle('opacity', 0).fade('in');
      
      this.options.isCartExpanded = true;
    }
  }
});