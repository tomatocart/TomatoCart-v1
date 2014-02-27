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
    itemsEl: $('popupCartItems'),
    clsCollapsed: 'cartCallpased',
    clsExpanded: 'cartExpanded',
    clsCartText: 'cartText',
    contentEl:  $('pageContent') || $('content-center'),
    
    //flag to represent the ajax shopping cart box is eanbled / disabled
    enableDelete: 'yes',
    
    //enable the flying effect or not
    enableFlyEffect: true,
    
    //the class name of the add to cart button
    clsAddBtn: '.ajaxAddToCart',
    
    //represent the flying image element
    clsImage: '.productImage',
    
    //enable the confirmation dialog
    dlgConfirmStatus: true
  },
  
  //init the popup cart
  initialize: function(options) {
    this.setOptions(options);
    this.registerEvents();
  },
  
  //register the mouseover events for the trigger element
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
    
    //enable the flying effects only when the ajax shopping cart box is disabled
    if (this.options.enableDelete === 'no') {
        this.options.enableFlyEffect = false;
    }
    
    //active the flying effects
    if (this.options.enableFlyEffect == true) {
      this.enableFlyingEffects();
    }
  },
  
  //get the shopping cart content
  getShoppingCart: function() {
    var data = {
      action: 'get_cart_contents',
      enable_delete: this.options.enableDelete
    };
    
    this.sendRequest(data, function(response) {
        this.displayCart(response);
    }.bind(this));
  },
  
  //display the cart with the shopping cart content
  displayCart: function(response) {
    var result = JSON.decode(response),
        pos = this.options.triggerEl.getCoordinates(),
        posContainer,
        cartFx,
        removeBtns;

    if (result.success == true) {
      if (!$defined(this.cartContainer)) {
        this.cartContainer = new Element('div', {
          'html': result.content,
          'id': 'popupCartContent',
          'styles': {
            'position': 'absolute',
            'opacity': 0
          }
        });
        
        this.cartContainer.addEvent('mouseleave', function(e) {
            e.stop();
            
            cartFx.start(1, 0);
            
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
      
      posContainer = this.cartContainer.getCoordinates();
      
      this.cartContainer.setStyles({
          'left': pos.left - (posContainer.width - pos.width),
          'top': pos.top + (pos.height/5)
      });
      
      cartFx = new Fx.Tween(this.cartContainer, {
          duration: 100,
          link: 'cancel',
          property: 'opacity'
      });
      
      cartFx.start(0, 1);
      
      //add the remove button
      if (this.options.enableDelete == 'yes') {
          removeBtns = this.cartContainer.getElements('.removeBtn');
          if (removeBtns.length > 0) {
              removeBtns.each(function(removeBtn) {
                  removeBtn.addEvent('click', function(e) {
                      e.stop();
                      
                      var productIdString = removeBtn.get('data-pid');
                      
                      this.removeProduct(productIdString, removeBtn);
                      
                      return false;
                      
                  }.bind(this));
              }, this);
          }
          
          //update cart total items
          this.options.itemsEl.set('text', result.total);
      }
      
      this.options.isCartExpanded = true;
    }
  },
  
  //enable the flying effects
  enableFlyingEffects: function() {
    //verify whether there is any add to cart button
    if (this.options.contentEl) {
      this.options.contentEl.addEvent('click:relay(' + this.options.clsAddBtn + ')', function(e) {
        var addToCartButton = e.target;
        
        e.stop();
        
        //do nothing if the add to cart button is disabled
        if (addToCartButton.hasClass('disabled')) {
            return false;
        }
        
        //disable the fly trigger
        addToCartButton.set('disabled', 'disabled');
        
        //check parameters
        var errors = [],
            btnId = addToCartButton.get('id'),
            variants = '';
            
        //used to fix bug [#209 - Compare / wishlist variant problem]
        if (btnId.test("^ac_[a-z]+_[0-9]+$", "i")) {
          var pID = btnId.split('_').getLast();
          
          var options = null;
          var selects = $$('.variantCombobox select');
          var listSelects = $$('.variants_' + pID + ' select');
          
          if (selects.length > 0) {
            options = selects;
          }else if (listSelects.length > 0) {
            options = listSelects;
          }
          
          if (options !== null) {
            options.each(function(select) {
              var id = select.id.toString();
              var groups_id = id.substring(9, id.indexOf(']'));
            
              variants += groups_id + ':' + select.value + ';';
            }.bind(this));
          }
        }else if (btnId.test("^ac_[a-z]+_[0-9]+(?:#(?:[0-9]+:?[0-9]+)+(?:;?(?:[0-9]+:?[0-9]+)+)*)*$", "i")) {
          var pIdString = btnId.split('_').getLast(),
              pIdParts = pIdString.split('#'),
              pID = pIdParts[0],
              variants = pIdParts[1];
        }
                
        var params = {action: 'add_product', pID: pID};
        if ( $defined($('quantity')) ) {
          params.pQty = $('quantity').get('value');  
        }
        
        if ($('qty_' + pID) != null) {
            params.pQty = $('qty_' + pID).get('value');  
        }

        //variants
        if (variants) {
          params.variants = variants; 
        }
        
        //gift certificate
        if ($defined($('senders_name')) && $('senders_name').value != '') {
          params.senders_name = $('senders_name').value;
        } else if ($defined($('senders_name')) && $('senders_name').value == '') {
          errors.push(this.options.error_sender_name_empty);
        }
         
        if ($defined($('senders_email')) && $('senders_email').value != '') {
          params.senders_email = $('senders_email').value;
        } else if ($defined($('senders_email')) && $('senders_email').value == '') {
          errors.push(this.options.error_sender_email_empty);
        }
          
        if ($defined($('recipients_name')) && $('recipients_name').value != '') {
          params.recipients_name = $('recipients_name').value;
        } else if ($defined($('recipients_name')) && $('recipients_name').value == '') {
          errors.push(this.options.error_recipient_name_empty);
        }
          
        if ($defined($('recipients_email')) && $('recipients_email').value != '') {
          params.recipients_email = $('recipients_email').value;
        } else if ($defined($('recipients_email')) && $('recipients_email').value == '') {
          errors.push(this.options.error_recipient_email_empty);
        }
            
        if ($defined($('message')) && $('message').value != '') {
          params.message = $('message').value;
        } else if ($defined($('message')) && $('message').value == '') {
          errors.push(this.options.error_message_empty);
        }
            
        if ($defined($('gift_certificate_amount')) && $('gift_certificate_amount').value != '') {
          params.gift_certificate_amount = $('gift_certificate_amount').value;
        } else if ($defined($('gift_certificate_amount')) && $('gift_certificate_amount').value == '') {
          errors.push(this.options.error_message_open_gift_certificate_amount);
        }
        
        if (errors.length > 0) {
          alert(errors.join('\n'));
          addToCartButton.erase('disabled');
          return false;
        }
        
        //send the ajax request to add the product into the shopping cart
        this.sendRequest(params, function(response) {
          var result = JSON.decode(response);
        
          if (result.success == true) {
            //move the product image into the popup cart with flying effects
            this.doFlyingEffects(addToCartButton, result.items);
            
            //show the confirmation dialog
            if (this.options.dlgConfirmStatus == true) {
                this.showConfirmation(result.confirm_dialog);
            }
          }else {
            addToCartButton.erase('disabled');
          }
        }.bind(this));
        
        return false;
      }.bind(this));
    }
  },
  
  /**
   * move the product image into the popup cart with flying effects
   * 
   * @param addToCartButton the add to cart button
   * @param items the count number of items in current shopping cart
   * 
   * return void
   */
  doFlyingEffects: function(addToCartButton, items) {
      var documentBody = $(document.body),
          //find the product image which is closest with current actived add to cart button
          imageEl = addToCartButton.closest(this.options.clsImage),
          
          //get the coordinates of the fly image element
          srcPos = imageEl.getCoordinates(),
          
          //copy the fly image element
          floatImage = imageEl.clone().setStyles({
            'position': 'absolute',
            'width': srcPos.width,
            'height': srcPos.height,
            'left': srcPos.left,
            'top': srcPos.top,
            'z-index': 9999
          }),
          
          //get the destination position
          destPos = this.options.triggerEl.getCoordinates();
          
  
      //add the float image into the document
      documentBody.adopt(floatImage);
      
      //create the flying effects
      floatImage.set('morph', {
        duration: 300,
        onComplete: function() {
          //fade out float image
          floatImage.fade('out');
          
          //destroy the float image
          (function() {floatImage.destroy();}).delay(500);
          
          //enable the fly trigger again
          addToCartButton.erase('disabled');
          
          //update the items count
          this.options.itemsEl.set('text', items);
        }.bind(this)
      }).morph({width: srcPos.width / 4, height: srcPos.height / 4, top: destPos.top, left: destPos.left});
  },
  
  //remove product based on the product id string
  removeProduct: function(productIdString, removeBtn) {
      var result,
          elItem = removeBtn.getParent('tr'),
          tblOrderTotals = removeBtn.getParent('table').getNext('table'),
          data = {
            action: 'remove_product',
            pID: productIdString     
          };
      
      this.sendRequest(data, function(response) {
          result = JSON.decode(response);
          
          if (result.success) {
             elItem.addClass('animated slideOutUp');
              
             //An anonymous function which waits a second and then destroy the item element
             (function(){elItem.destroy()}).delay(300);
             
             //update the items count
             this.options.itemsEl.set('text', result.total);
             
             //update order totals
             tblOrderTotals.set('html', result.order_totals);
          }
      }.bind(this));
  },
  
  /**
   * show confirmation dialog
   * 
   * @param confirm_dialog
   * @param pageY The y position of the mouse, relative to the full window.
   * 
   * return void
   */
  showConfirmation: function(confirm_dialog) {
      if (confirm_dialog != null) {
          if (typeof this.dlg !== 'undefined') {
              this.dlg.update(confirm_dialog);
          }else {
              this.dlg = new popDialog(confirm_dialog);
          }
          
          this.dlg.show();
          
          //set the continue action
          if ($('btnContinue') != null) {
              $('btnContinue').addEvent('click', function(e){
                  e.stop();
                                 
                  this.dlg.doAnimate('slideOutUp');
                  this.dlg.hide();
                  
                  return false;
              }.bind(this));
          }
      }
  },
  
  /**
   * send the ajax request
   * 
   * @param params - an object composed of the params which will be sent in the reqeust
   * @param callback - an function will be called after receiving the respose from the server
   * 
   * return void
   */
  sendRequest: function(params, callback) {
    var data = {
      template: this.options.template,
      module: 'popup_cart',
      method: 'post'
    };
    
    data[this.options.sessionName] = this.options.sessionId;
    
    $extend(data, params);
    
    //send the ajax request
    var loadRequest = new Request({
      url: this.options.remoteUrl,
      data: data,
      onSuccess: callback.bind(this)
    }).send();
  }
});