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
    relativeTop: 0,
    relativeLeft: 215,
    enableDelete: 'yes'
  },
  
  initialize: function(options) {
    this.setOptions(options);
    this.registerEvents();
  },
  
  registerEvents: function() {
    this.options.triggerEl.addEvents({
      'mouseover': function(e) {
        e.stop();
        
        if (this.options.isCartExpanded == false) {
          this.getShoppingCart();
          
          $$('.' + this.options.clsCartText).each(function(text) {
            if (text.hasClass(this.options.clsCollapsed)) {
              text.removeClass(this.options.clsCollapsed);
            }
            
            text.addClass(this.options.clsExpanded);
          }.bind(this));
        }
      }.bind(this)
    });
  },
  
  //get the shopping cart content
  getShoppingCart: function() {
    var data = {
      template: this.options.template,
      module: 'popup_cart', 
      action: 'get_cart_contents'
    };
    data[this.options.sessionName] = this.options.sessionId;
    data['enable_delete'] = this.options.enableDelete;
    
    var loadRequest = new Request({
      url: this.options.remoteUrl,
      data: data,
      onSuccess: this.displayCart.bind(this)
    }).send();
  },
  
  //display the cart with the shopping cart content
  displayCart: function(response) {
    var result = JSON.decode(response),
        pos = this.options.triggerEl.getCoordinates(),
        removeBtns;

    if (result.success == true) {
      if (!$defined(this.cartContainer)) {
        this.cartContainer = new Element('div', {
          'html': result.content,
          'id': 'popupCartContent',
          'styles': {
            'position': 'absolute',
            'top': pos.top + this.options.relativeTop,
            'left': pos.left - this.options.relativeLeft    
          }
        });
        
        this.cartContainer.addEvent('mouseleave', function(e) {
            e.stop();
            
            this.cartContainer.fade('out');
            
            this.options.isCartExpanded = false;
            
            $$('.' + this.options.clsCartText).each(function(text) {
              if (text.hasClass(this.options.clsExpanded)) {
                text.removeClass(this.options.clsExpanded);
              }
              
              text.addClass(this.options.clsCollapsed);
            }.bind(this));
        }.bind(this));
      } else {
        this.cartContainer.set('html', result.content);
      }
      
      this.options.container.adopt(this.cartContainer);
      this.cartContainer.setStyle('opacity', 0).fade('in');
      
      //add the remove button
      if (this.options.enableDelete == 'yes') {
          removeBtns = this.cartContainer.getElements('.removeBtn');
          if (removeBtns.length > 0) {
              removeBtns.each(function(removeBtn) {
                  removeBtn.addEvent('click', function(e) {
                      e.stop();
                      
                      var productIdString = removeBtn.get('data-pid');
                      
                      this.removeProduct(productIdString);
                      
                      return false;
                      
                  }.bind(this));
              }, this);
          }
          
          //update cart total items
          this.options.triggerEl.getElement('#popupCartItems').set('text', result.total);
      }
      
      this.options.isCartExpanded = true;
    }
  },
  
  //remove product based on the product id string
  removeProduct: function(productIdString) {
      var result,
          loadRequest,
          data = {
            template: this.options.template,
            module: 'popup_cart', 
            action: 'remove_product'
          };
      
      data[this.options.sessionName] = this.options.sessionId;
      data['pID'] = productIdString;
      loadRequest = new Request({
        url: this.options.remoteUrl,
        data: data,
        onSuccess: function(response) {
            result = JSON.decode(response);
            
            if (result.success) {
                this.getShoppingCart();
            }
        }.bind(this)
      });
      
      loadRequest.send();
  }
});