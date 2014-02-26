/*
  $Id: list_variants.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
 */
var TocListVariants = new Class({
    Implements : [ Options ],
    options : {
        hasSpecial: 0,
        remoteUrl: 'json.php',
        combVariants: null,
        productsId: null,
        variants: null,
        linkCompareProductsCls: '.compare',
        linkWishlistCls : '.wishlist',
        priceCls: '.price',
        buyElCls: '.options',
        btnAddCls: '.ajaxAddToCart',
        lang : {
            txtInStock : 'In Stock',
            txtOutOfStock : 'Out Of Stock',
            txtNotAvailable : 'Not Available'
        }
    },

    /**
     * Initialize
     * 
     * @access public
     * @params object the options for this class
     * 
     * return void
     */
    initialize : function(options) {
        this.setOptions(options);
        
        if (this.options.combVariants !== null && this.options.combVariants.length > 0) {
            this.checkCompareProducts();
            this.checkWishlist();
            this.initializeComboBox();
        }
        
        if (this.options.productsId != null) {
            this.imgEl = $('img_ac_productlisting_' + this.options.productsId).getElement('img.productImage');
        }
    },

    /**
     * Initialize the combobox
     * 
     * @access private
     * 
     * return void
     */
    initializeComboBox : function() {
        this.options.combVariants.each(function(combobox) {
            combobox.addEvent('change', function() {
                this.updateView(combobox);
            }.bind(this));
        }.bind(this));
    },

    /**
     * Check the compare products links
     * 
     * @access private
     * 
     * return void
     */
    checkCompareProducts : function() {
        var combVariant = this.options.combVariants[0];
        
        this.linkCp = combVariant.closest(this.options.linkCompareProductsCls);

        this.linkCpHref = this.linkCp.getProperty('href');

        if (this.linkCpHref.search(/cid=/) !== -1) {
            this.linkCpHref = this.linkCpHref.replace(/&?cid=[0-9]+(_[0-9]+:[0-9]+(;[0-9]+:[0-9]+)*)*/, '');
        }
        
        this.linkCp.addEvent('click', function() {
          if (this.hasClass('disabled')) {
            return false;
          }
        });
    },

    /**
     * Check the wishlist link
     * 
     * @access pricate
     * 
     * return void
     */
    checkWishlist : function() {
      var combVariant = this.options.combVariants[0];
      
      this.linkWp = combVariant.closest(this.options.linkWishlistCls);
      
      this.linkWpHref = this.linkWp.getProperty('href');

      if (this.linkWpHref.search(/wid=/) !== -1) {
          this.linkWpHref = this.linkWpHref.replace(/&?wid=[0-9]+(_[0-9]+:[0-9]+(;[0-9]+:[0-9]+)*)*/, '');
      }
      
      this.linkWp.addEvent('click', function() {
        if (this.hasClass('disabled')) {
          return false;
        }
      });
    },

    /**
     * Update the product row
     * 
     * @access priate
     * @param element
     * 
     * return void
     */
    updateView : function(combobox) {
        var productsIdString = this.getProductsIdString(),
            product = this.options.variants[productsIdString],
            buyEl = combobox.getParent(this.options.buyElCls),
            buyBtn = buyEl.closest(this.options.btnAddCls),
            selects = buyEl.getElements('select'),
            error,
            href;
        
        //destroy the error element
        if (buyEl.getElement('.warning')) {
            buyEl.getElement('.warning').destroy();
        }
        
        //check whether the variant product is available
        if (product == undefined || (product['status'] == 0)) {
            //create error element and inject into the top of the buy element
            error = new Element('div', {
                'html': '<em>' + this.options.lang.txtNotAvailable + '</em>',
                'class': 'warning'
            });
            
            error.inject(buyEl, 'top');
            
            //add the alert mark for the selects
            selects.addClass('alert');
            
            //remove the animated class for the product image
            if (this.imgEl) {
                if (this.imgEl.hasClass('animated')) {
                    this.imgEl.removeClass('animated flash');
                }
            }
            
            //disable the buy btn
            buyBtn.addClass('disabled');
            
            //disable the compare and wishlist link because the variant product is not available
            if (this.linkCp) {
              this.linkCp.addClass('disabled');
            }
            
            if (this.linkWp) {
              this.linkWp.addClass('disabled');
            }
        } else {
            //enable the buy btn
            buyBtn.removeClass('disabled');
            
            //remove the alert mark for the selects
            selects.removeClass('alert');
            
            //destroy the error element
            if (buyEl.getElement('.warning')) {
                buyEl.getElement('.warning').destroy();
            }
            
            if (this.options.hasSpecial == 0) {
                // get the formatted price of the variants product by ajax request
                this.sendRequest({
                    action : 'get_variants_formatted_price',
                    products_id_string : productsIdString
                }, function(response) {
                    var result = JSON.decode(response);

                    if (result.success == true) {
                        combobox.closest(this.options.priceCls).set('html', '<span class="animated flash">' + result.formatted_price + '</span>');
                    } else {
                        alert(result.feedback);
                    }
                }.bind(this));
            }

            this.changeImage(product['image'], combobox);
            
            // if it is in the product info page and the product have any variants, add the variants into the compare products link
            if (this.linkCp) {
                href = this.linkCpHref + '&cid='
                        + productsIdString.replace(/#/, '_');

                this.linkCp.setProperty('href', href);
                
                this.linkCp.removeClass('disabled');
            }

            // handler the wishlist
            if (this.linkWp) {
                href = this.linkWpHref + '&wid='
                        + productsIdString.replace(/#/, '_');

                this.linkWp.setProperty('href', href);
                
                this.linkWp.removeClass('disabled');
            }
        }
    },

    /**
     * Change the product image
     * 
     * @access private
     * @param string new image name need to be changed
     * 
     * return boolean
     */
    changeImage: function(image) {
        var imgsrc, 
            path = [];
        
        if (this.options.productsId != null) {
            //change the src path
            if (this.imgEl) {
                imgSrc = this.imgEl.getProperty('src');
                if (imgSrc) {
                    path = imgSrc.split('/');
                    if (path.length > 0) {
                        path[path.length - 1] = image;
                        
                        imgSrc = path.join('/');
                        
                        this.imgEl.setProperty('src', imgSrc).addClass('animated flash');
                        
                        return true;
                    }
                }
            }
        }
        
        return false;
    },
    
    /**
     * Get the prodcut id string based on the product id and variants
     * 
     * @access private
     * 
     * return string
     */
    getProductsIdString: function() {
        var groups = [],
            id,
            groupsID;
        
        this.options.combVariants.each(function(combobox) {
            id = combobox.id.toString();
            groupsID = id.substring(9, id.indexOf(']'));
          
            groups.push(groupsID + ':' + combobox.value);
        }.bind(this));
        
        return this.options.productsId + '#' + groups.join(';');
    },
    
    /**
     * Send the ajax request
     * 
     * @access private
     * @param object request params to be passed to the server
     * @param function callback for the successful request
     * 
     * return void
     */
    sendRequest : function(data, fnSuccess) {
        data.module = 'products';

        var loadRequest = new Request({
            url : this.options.remoteUrl,
            data : data,
            onSuccess : fnSuccess.bind(this)
        }).send();
    }
});