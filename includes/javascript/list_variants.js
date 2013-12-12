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
        variantsGroups: null,
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
        
        //private funciton to sort the variants groups
        var strcmp = function(str1, str2) {
            return ((str1 == str2) ? 0 : ((str1 > str2) ? 1 : -1));
        }
        
        var strnatcmp = function(f_string1, f_string2, f_version) {
            var i = 0;

            if (f_version == undefined) {
              f_version = false;
            }

            var __strnatcmp_split = function (f_string) {
              var result = [];
              var buffer = '';
              var chr = '';
              var i = 0,
                f_stringl = 0;

              var text = true;

              f_stringl = f_string.length;
              for (i = 0; i < f_stringl; i++) {
                chr = f_string.substring(i, i + 1);
                if (chr.match(/\d/)) {
                  if (text) {
                    if (buffer.length > 0) {
                      result[result.length] = buffer;
                      buffer = '';
                    }

                    text = false;
                  }
                  buffer += chr;
                } else if ((text == false) && (chr === '.') && (i < (f_string.length - 1)) && (f_string.substring(i + 1, i + 2).match(/\d/))) {
                  result[result.length] = buffer;
                  buffer = '';
                } else {
                  if (text == false) {
                    if (buffer.length > 0) {
                      result[result.length] = parseInt(buffer, 10);
                      buffer = '';
                    }
                    text = true;
                  }
                  buffer += chr;
                }
              }

              if (buffer.length > 0) {
                if (text) {
                  result[result.length] = buffer;
                } else {
                  result[result.length] = parseInt(buffer, 10);
                }
              }

              return result;
            };

            var array1 = __strnatcmp_split(f_string1 + '');
            var array2 = __strnatcmp_split(f_string2 + '');

            var len = array1.length;
            var text = true;

            var result = -1;
            var r = 0;

            if (len > array2.length) {
              len = array2.length;
              result = 1;
            }

            for (i = 0; i < len; i++) {
              if (isNaN(array1[i])) {
                if (isNaN(array2[i])) {
                  text = true;

                  if ((r = strcmp(array1[i], array2[i])) != 0) {
                    return r;
                  }
                } else if (text) {
                  return 1;
                } else {
                  return -1;
                }
              } else if (isNaN(array2[i])) {
                if (text) {
                  return -1;
                } else {
                  return 1;
                }
              } else {
                if (text || f_version) {
                  if ((r = (array1[i] - array2[i])) != 0) {
                    return r;
                  }
                } else {
                  if ((r = strcmp(array1[i].toString(), array2[i].toString())) != 0) {
                    return r;
                  }
                }

                text = false;
              }
            }

            return result;
        };
        
        if (this.options.combVariants !== null && this.options.combVariants.length > 0) {
            //sort variants groups
            this.options.combVariants.sort(function(a, b) {
                var idA= a.id.toString(),
                    groupsAID = idA.substring(9, idA.indexOf(']')),
                    idB = b.id.toString(),
                    groupsBID = idB.substring(9, idB.indexOf(']')),
                    groupsA,
                    groupsB;
                
                this.options.variantsGroups.each(function(variantGroup) {
                    if (variantGroup.groups_id == groupsAID) {
                        groupsA = variantGroup;
                    }
                    
                    if (variantGroup.groups_id == groupsBID) {
                        groupsB = variantGroup;
                    }
                });
                
                if (groupsA.sort_order < groupsB.sort_order) {
                    return -1;
                }
                
                if (groupsA.sort_order > groupsB.sort_order) {
                    return 1;
                }
                
                if (groupsA.sort_order == groupsB.sort_order) {
                    return strnatcmp(groupsA.group_name, groupsB.groups_name);
                }
                
            }.bind(this));
            
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
        var linkCp = $$(this.options.linkCompareProductsCls);

        if (linkCp.length > 0) {
            this.linkCp = linkCp[0];
            this.linkCpHref = this.linkCp.getProperty('href');

            if (this.linkCpHref.search(/cid=/) !== -1) {
                this.linkCpHref = this.linkCpHref.replace(/&cid=\d+/, '');
            }
        }
    },

    /**
     * Check the wishlist link
     * 
     * @access pricate
     * 
     * return void
     */
    checkWishlist : function() {
        var linkWp = $$(this.options.linkWishlistCls);

        if (linkWp.length > 0) {
            this.linkWp = linkWp[0];
            this.linkWpHref = this.linkWp.getProperty('href');

            if (this.linkWpHref.search(/wid=/) !== -1) {
                this.linkWpHref = this.linkWpHref.replace(/&wid=\d+/, '');
            }
        }
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
            buyBtn = buyEl.getParent().getElement(this.options.btnAddCls),
            selects = buyEl.getElements('select'),
            error,
            href;
        
        //destroy the error element
        if (buyEl.getElement('.warning')) {
            buyEl.getElement('.warning').destroy();
        }
        
        // if it is in the product info page and the product have any variants,
        // add the variants into the compare products link
        if (this.linkCp) {
            href = this.linkCpHref + '&cid='
                    + productsIdString.replace(/#/, '_');

            this.linkCp.setProperty('href', href);
        }

        // handler the wishlist
        if (this.linkWp) {
            href = this.linkWpHref + '&wid='
                    + productsIdString.replace(/#/, '_');

            this.linkWp.setProperty('href', href);
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
        
        console.dir(this.options.combVariants);
        
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