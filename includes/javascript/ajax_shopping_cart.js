/*
  $Id: ajax_shipping_cart.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

var AjaxShoppingCart = new Class({
  Implements: [Options, Events],

  options: {
    sessionName: 'sid',
    sessionId: '',
    jsonUrl: 'json.php',
    currentUrl: 'index.php',
    redirect: 'checkout.php',
    dlgConfirmStatus: false,
    movedPicSize: 2
  },

  initialize: function(options) {
    this.setOptions(options);
    
    this.isOpera = Browser.Engine.presto;
    
    this.initializeCart();
  },

  initializeCart: function() {
  	
    this.products = [];
    this.attachAddToCartEvent();

    $('ajaxCartCollapse').addEvent('click', function(e) {
      e.stop();

      this.collapse();
    }.bind(this));

    $('ajaxCartExpand').addEvent('click', function(e) {
      e.stop();

      this.expand();
    }.bind(this));
    
    this.checkCartState();
    this.loadCart();
  },
  
  /**
   * Responsible for checking the shopping cart state: expanded or collapsed
   *
   * @access  private
   * @return void
   */
  checkCartState: function() {
    var cartState = Cookie.read('cartstate');
     
    switch(cartState) {
      case 'collapsed':
        this.collapse();
        break;
      case 'expanded':
        this.expand();
        break;
      default:
        this.expand();
    }
  },
  
  clearCustomizationForm: function() {
		if ($defined($('frmCustomizations'))) {
		  var form = $('frmCustomizations');
		  
		  for (i = 0; i < form.length; i++) {
		    form[i].value = '';
		  }
		  
		  if ($defined(form.getElement('span'))) {
		    form.getElement('span').innerHTML = '';
		  }
		}
  },

  //attach click event for the add to cart buttons
  attachAddToCartEvent: function() {
    if ( $defined($$('.ajaxAddToCart')) ) {
      $$('.ajaxAddToCart').each(function(addToCartButton) {
        addToCartButton.addEvent('click', function(e) {
          e.stop();
          
          if (addToCartButton.hasClass('disabled')) {
            return false;
          }

          var errors = [];

          //send request
          var btnId = addToCartButton.get('id');
          
          //used to fix bug [#209 - Compare / wishlist variant problem]
          var variants = '';
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
            return;
          }
          
          this.sendRequest(params, function(response) {
            var result = JSON.decode(response);
            
            this.clearCustomizationForm();

            //move image
            if (result.success == true) {
              if ( $defined($('defaultProductImage')) ) {
                //in the product info page, copy the product image and move it
                var productLink = $('productImages').getElement('#defaultProductImage');
                var productImg = $('defaultProductImage').getElement('img.productImage');
                var cloneProductImg = productImg.clone();
                var srcPos = productLink.getCoordinates();
                
                cloneProductImg.injectAfter($(document)).setStyles({
                  'position': 'absolute',
                  'left': productImg.getCoordinates().left,
                  'top': productImg.getCoordinates().top-5
                });
                
                var srcImage = cloneProductImg;
              }else if ( $defined($('img_' + btnId)) ) {
                var srcImage = $('img_' + btnId).getElement('img.productImage');
                 var srcPos = srcImage.getCoordinates();
              }

              var destPos = $('ajaxCartContent').getParent().getCoordinates();

              var floatImage = srcImage.clone().setStyles({
                'position': 'absolute',
                'width': srcPos.width,
                'height': srcPos.height,
                'left': srcPos.left,
                'top': srcPos.top
              });

              floatImage.injectAfter($(document.body)).setStyles({position: 'absolute'}).set('morph', {
                duration: 300,
                onComplete: function() {
                  floatImage.fade('out');
                  
                  this.updateCart(result.content);
                  
                  (function() {floatImage.destroy()}).delay(1000);

                  addToCartButton.erase('disabled');
                  
                  if ($defined(cloneProductImg)) {
                    cloneProductImg.destroy();
                  }
                  
                  if (this.options.dlgConfirmStatus = true) {
                	  this.showConfirmation(result.confirm_dialog);
                  }
                }.bind(this)
              }).morph({width: srcPos.width / 2, height: srcPos.height / 2, top: destPos.top + destPos.height / 4, left: destPos.left + destPos.width / 4});
            } else {
              if ($defined(result.feedback)) {
                alert(result.feedback);
              }
            }
          });
        }.bind(this));
      }.bind(this));
    }
  },

  collapse: function() {
    if ($('ajaxCartContentLong').hasClass('expanded')) {
    	Cookie.write('cartstate', 'collapsed');
    	
      $('ajaxCartContentLong').set('tween', {
        duration: 500,
        property: 'height',
        onComplete: function() {
          $('ajaxCartContentLong').addClass('collapsed').removeClass('expanded');
          $('ajaxCartContentProducts').fade('out');
          if ($defined($('ajaxCartOrderTotals'))) {
            $('ajaxCartOrderTotals').fade('out');
          }
          $('ajaxCartButtons').fade('out');
          
          if (this.isOpera) {
            $('ajaxCartContentShort').set('tween', {
              duration: 500,
              property: 'height',
              onComplete: function() {
                $('ajaxCartContentShort').addClass('expanded').removeClass('collapsed');
              }
            }).tween(0, 20);
          }else {
            $('ajaxCartContentShort').set('slide', {
              onComplete: function() {
                $('ajaxCartContentShort').addClass('expanded').removeClass('collapsed').slide('in');
              }.bind(this)
            }).slide('in').fade('in');
          }
        }.bind(this)
      }).tween(this.cartHeight, 0);

      $('ajaxCartCollapse').set('tween' , {
        duration: 500,
        property: 'opacity',
        onComplete: function() {
          $('ajaxCartCollapse').addClass('collapsed');

          $('ajaxCartExpand').removeClass('hidden'). setStyle('opacity', 0).set('tween', {
            duration: 1000,
            property: 'opacity'
          }).tween(0,100);
        }
      }).tween(100, 0);
    }
  },

  expand: function() {
    if ($('ajaxCartContentLong').hasClass('collapsed')) {
    	Cookie.write('cartstate', 'expanded');
    	
    	if (this.isOpera) {
			$('ajaxCartContentShort').set('tween', {
			  duration: 500,
			  property: 'height',
			  onComplete: function() {
          $('ajaxCartContentShort').addClass('collapsed').removeClass('expanded');

          $('ajaxCartContentLong').removeClass('collapsed').addClass('expanded');
          $('ajaxCartContentLong').set('tween', {
            duration: 500,
            property: 'height',
            onComplete: function() {
              $('ajaxCartContentProducts').fade('in');
              $('ajaxCartOrderTotals').fade('in');
              $('ajaxCartButtons').fade('in');
              $('ajaxCartContentLong').setStyle('height', 'auto');
            }
          }).tween(0, this.cartHeight);
			  }.bind(this)
			}).tween(0);
    	}else {
  		  $('ajaxCartContentShort').set('slide', {
          duration: 600,
          onComplete: function() {
            $('ajaxCartContentShort').addClass('collapsed').removeClass('expanded');
  
            $('ajaxCartContentLong').removeClass('collapsed').addClass('expanded');
            $('ajaxCartContentLong').set('tween', {
              duration: 500,
              property: 'height',
              onComplete: function() {
                $('ajaxCartContentProducts').fade('in');
                $('ajaxCartOrderTotals').fade('in');
                $('ajaxCartButtons').fade('in');
                $('ajaxCartContentLong').setStyle('height', 'auto');
              }
            }).tween(0, this.cartHeight);
          }.bind(this)
        }).slide('out');
    	}
     
      $('ajaxCartExpand').set('tween', {
        duration: 800,
        property: 'opacity',
        onComplete: function() {
          $('ajaxCartExpand').addClass('hidden');

          $('ajaxCartCollapse').removeClass('collapsed').setStyle('opacity', 0).set('tween', {
            duration: 10000,
            property: 'opacity'
          }).tween(0, 100);
        }
      }).tween(100, 0);
    }
  },

  loadCart: function() {
    this.sendRequest({action: 'load_cart'}, function(response) {
      var json = JSON.decode(response);

      this.updateCart(json);
    });
  },

  updateCart: function(json) {
  	//popup shopping cart view
  	$('popupCartItems').set('text', json.numberOfItems);
  	
    //shopping cart short view
    $('ajaxCartContentShort').getElement('.quantity').set('html', json.numberOfItems);
    $('ajaxCartContentShort').getElement('.cartTotal').set('html', json.total);
    
    //shopping cart long view
    this.updateProductsContent(json);
    this.updateOrderTotals(json);
    
    this.cartHeight = $('ajaxCartContentLong').getSize().y;
  },

  //if the product has been removed, We must delete the product from the shopping cart
  removeProducts: function(json) {
    if (this.products.length > 0) {
      //get all the products to be removed
	    var products = [];

	    this.products.each(function(id) {
	      var found = false;
	      if ($defined(json.products)) {
	        json.products.each(function(product) {
	          if (product.id == id) {
	            found = true;
	          }
	        });
	      }

	      if (!found) {products.push(id);}
	    });

      //play animation to remove products
      if (products.length > 0) {
        products.each(function(pID, index) {
          $('ajaxCartProduct' + pID).addClass('strike').set('tween', {
            duration: 1000,
            property: 'opacity',
            onComplete: function() {
              $('ajaxCartProduct' + pID).destroy();
              this.products.erase(pID);

              if (this.products.length == 0) {
              	if (this.isOpera) {
              	  $('ajaxCartContentNoProducts').removeClass('collapsed').addClass('expanded').set('tween', { 
              	   duration: 500,
                   property: 'height'
                  }).tween(0, 20);
              	}else {
          	      $('ajaxCartContentNoProducts').removeClass('collapsed').addClass('expanded').slide('in');
              	}
                
                $('ajaxCartContentProducts').removeClass('expanded').addClass('collapsed');
              }
            }.bind(this)
          }).tween(100, 0);
        }.bind(this));
      }
    }
  },

  //update Products Content
  updateProductsContent: function(json) {
  	//remove products
  	if ($defined(json.products)) {
	   this.removeProducts(json);
    	 
      if (json.products.length > 0 ) {
      	if (this.isOpera) {
      	  $('ajaxCartContentNoProducts').removeClass('expanded').addClass('collapsed').set('tween', {
      	    duration: 500,
            property: 'height'
      	  }).tween(0);
      	}else {
      	  $('ajaxCartContentNoProducts').removeClass('expanded').addClass('collapsed').slide('out');
      	}
      	
        //add products
        json.products.each(function(product) {
        	if ( this.products.indexOf(product.id) == -1 ) {
			  this.products.push(product.id);
  
			  var rowEl = new Element('li', {'id': 'ajaxCartProduct' + product.id});
			  var quantityEl = new Element('span', {'class': 'quantity', 'html': product.quantity});
			  var productEl = new Element('a', {'href': product.link, 'title': product.title, 'html': product.name});
			  var priceEl = new Element('span', {'class': 'price', 'html': product.price});
			  var deleteEl = new Element('span', {'class': 'removeProduct'});
	  
			  $('ajaxCartContentProducts').grab(rowEl.grab(quantityEl).grab(productEl).grab(priceEl).grab(deleteEl));

			//variants
			if ( $defined(product.variants) ) {
			  var variants = [];
			  product.variants.each(function(variant) {
			    variants.push(variant.groups_name + ': ' + variant.values_name);
			  });
			
			  var variantsEl = new Element('p', {'class': 'variants', 'html': variants.join('<br />')});
			  rowEl.grab(variantsEl);
			}
            
            //customization fields
            if ( $defined(product.customizations) ) {
              var customizationsEl = new Element('div', {'class': 'customizations', 'html': this.getCustomizations(product.customizations)});
              
              rowEl.grab(customizationsEl);
            }
            
            //gift certificate data
            if ( $defined(product.gc_data) ) {
              var gcEl = new Element('p', {'class': 'gift_certificate', 'html': product.gc_data});
              rowEl.grab(gcEl);
            }
  
    				//delete product
    		    deleteEl.addEvent('click', function(e) {
  			      e.stop();
  
  			      this.sendRequest({action: 'remove_product', pID: product.id}, function(response) {
  				      var result = JSON.decode(response);
  
  					    if (result.success == true) {
  					    	//if on the checkout page
    				    	if (this.options.currentUrl.indexOf('checkout.php?checkout') > 0) {
    				    	  if (result.hasContents == false) {
  				    	      window.location = this.options.currentUrl;
  				    	    }else {
				    	        if ($defined(checkout) && (checkout.steps[checkout.openedForm] >= checkout.steps['shippingMethodForm'])) {
                        checkout.loadPreviousForms('shippingMethodForm');
                        checkout.gotoPanel('shippingMethodForm');
                      }
				    	      }
  					    	}
    				    	
    				    	//shopping cart page
    				    	if (this.options.currentUrl.indexOf('checkout.php?cart') > 0) {
    				    	  window.location = this.options.currentUrl;
    				    	}
      					    	
  			          this.loadCart();
  					    }
  				    });
  			    }.bind(this));
  
			  	$('ajaxCartContentProducts').removeClass('collapsed');
			} else {
				$('ajaxCartProduct' + product.id).getElement('.price').set('text', product.price);
				$('ajaxCartProduct' + product.id).getElement('.quantity').set('html', product.quantity);
			   
				//customization fields
				if ( $defined(product.customizations) ) {
				  $('ajaxCartProduct' + product.id).getElement('.customizations').set('html', this.getCustomizations(product.customizations));
				}  				   
			}
        }.bind(this));
      }else {
      	if (this.isOpera) {
      	  $('ajaxCartContentNoProducts').removeClass('collapsed').addClass('expanded').set('tween', {
      	    duration: 500,
            property: 'height'
      	  }).tween(0, 20);
      	}else {
      	  $('ajaxCartContentNoProducts').removeClass('collapsed').addClass('expanded').slide('in');
      	}
      }
  	}
  },
  
  getCustomizations: function(customizations) {
		var html = '';
		
		customizations.each(function(customization) {
		  var content = '<div style="float: left">' + customization.qty + ' x </div><div style="margin-left: 20px">';
		
		  customization.fields.each(function(field) {
		    content += field.customization_fields_name + ': ' + field.customization_value + '<br />';
		  });
		  
		  html += content + '</div>';
		});
		
		return html;
  },

  updateOrderTotals: function(json) {
    if ( $defined($('ajaxCartOrderTotals')) ) {
      $('ajaxCartOrderTotals').destroy();
    }

    if ($type(json.orderTotals) == 'array') {
      var orderTotalsEl = new Element('ul', {'id': 'ajaxCartOrderTotals'});

      var html = '';
      json.orderTotals.each(function(orderTotal) {
        html += '<li><span class="orderTotalText">' + orderTotal.text + '</span><span>' + orderTotal.title + '</span></li>'
      });

      orderTotalsEl.set('html', html);
      orderTotalsEl.inject($('ajaxCartButtons'), 'before');
    }
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

  sendRequest: function(data, fnSuccess) {
    data.module = 'ajax_shopping_cart';
    data[this.options.sessionName] = this.options.sessionId;
    
    if (this.options.template) {
      data.template = this.options.template;
    }
    
    new Request({
      url: this.options.jsonUrl,
      method: 'post',
      data: data,
      onSuccess: fnSuccess.bind(this)
    }).send();
  }
});