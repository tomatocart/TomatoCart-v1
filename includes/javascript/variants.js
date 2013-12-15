/*
  $Id: variants.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

var TocVariants = new Class({
  Implements: [Options],
  options: {
    hasSpecial: 0,
    remoteUrl: 'json.php',
    linkCompareProductsCls: '.compare-products',
    linkWishlistCls: '.wishlist',
    combVariants: null,
    variants: null,
    variantsGroups: null,
    lang: {
      txtInStock: 'In Stock',
      txtOutOfStock: 'Out Of Stock',
      txtNotAvailable: 'Not Available',
      txtTaxText: 'incl. tax'
    }
  },
  
  sendRequest: function(data, fnSuccess) {
    data.module = 'products';
    
    var loadRequest = new Request({
      url: this.options.remoteUrl,
      data: data,
      onSuccess: fnSuccess.bind(this)
    }).send();
  },
  
  initialize: function(options) {
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
    
    this.setOptions(options);
    
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
      this.updateView();
    }
  },
  
  initializeComboBox: function() {
    this.options.combVariants.each(function(combobox) {
      combobox.addEvent('change', function() {
        this.updateView();
      }.bind(this));
    }.bind(this));
  },
  
  //Check whether the compare products feature is enabled
  checkCompareProducts: function() {
  	var linkCp = $$(this.options.linkCompareProductsCls);
  	
    if (linkCp.length > 0) {
      this.linkCp = linkCp[0];
      this.linkCpHref = this.linkCp.getProperty('href');
      
      if (this.linkCpHref.search(/cid=/) !== -1) {
        this.linkCpHref = this.linkCpHref.replace(/&cid=\d+/, '');
      }
    }
  },
  
  //Check the wishlist
  checkWishlist: function() {
    var linkWp = $$(this.options.linkWishlistCls);
    
    if (linkWp.length > 0) {
      this.linkWp = linkWp[0];
      this.linkWpHref = this.linkWp.getProperty('href');
      
      if (this.linkWpHref.search(/wid=/) !== -1) {
        this.linkWpHref = this.linkWpHref.replace(/&wid=\d+/, '');
      }
    }
  },
  
  getProductsIdString: function() {
    var groups = [];
    this.options.combVariants.each(function(combobox) {
      var id = combobox.id.toString();
      var groups_id = id.substring(9, id.indexOf(']'));
      
      groups.push(groups_id + ':' + combobox.value);
    }.bind(this));
    
    return this.options.productsId + '#' + groups.join(';');
  },
    
  updateView: function(choice) {
  	var productsIdString = this.getProductsIdString();
  	
  	//if it is in the product info page and the product have any variants, add the variants into the compare products link
  	if (this.linkCp) {
    	var href = this.linkCpHref + '&cid=' + productsIdString.replace(/#/, '_');
    	
    	this.linkCp.setProperty('href', href);
	  }
	  
	  //handler the wishlist
    if (this.linkWp) {
      var href = this.linkWpHref + '&wid=' + productsIdString.replace(/#/, '_');
      
      this.linkWp.setProperty('href', href);
    }
	  
    var product = this.options.variants[productsIdString];
    
    if (product == undefined || (product['status'] == 0)) {
      $('productInfoAvailable').innerHTML = '<font color="red">' + this.options.lang.txtNotAvailable + '</font>';
    } else {
	    if (this.options.hasSpecial == 0) {
	    	// get the formatted price of the variants product by ajax requst
	    	this.sendRequest({action: 'get_variants_formatted_price', products_id_string: productsIdString}, function(response) {
	        var result = JSON.decode(response);
	        
	        if (result.success == true) {
	          $('productInfoPrice').set('html', result.formatted_price + ' ' + this.options.lang.txtTaxText);
	        }else {
	          alert(result.feedback);
	        }
	    	}.bind(this));
	    }
	    
	    $('productInfoSku').set('text', product['sku']);
	    if (this.options.displayQty == true) {
	      $('productInfoQty').set('text', product['quantity'] + ' ' + this.options.unitClass);
	    }
	    
	    if (product['quantity'] > 0) {
	    	$('productInfoAvailable').set('text', this.options.lang.txtInStock);
	    }else {
	    	$('productInfoAvailable').set('text', this.options.lang.txtOutOfStock);
	    }
	    
	    $('shoppingCart').fade('in');
	    $('shoppingAction').fade('in');
	    
	    this.changeImage(product['image']);
    }
  },
  
  changeImage: function(image) {
    $$('.mini').each(function(link) {
      var href = link.getProperty('href');
      if (href.indexOf(image) > -1) {
        link.fireEvent('mouseover');
      }else {
        link.fireEvent('mouseleave');
      }
    });
  }
});