var TocVariants = new Class({
  Implements: [Options],
  options: {
    hasSpecial: 0,
    remoteUrl: 'json.php',
    variants: null,
    linkCompareProductsCls: '.compare-products',
    linkWishlistCls: '.wishlist',
    btnAddCls: '.ajaxAddToCart',
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
    this.setOptions(options);
    
    if (this.options.combVariants !== null && this.options.combVariants.length > 0) {
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
      
      this.linkCp.addEvent('click', function() {
        if (this.hasClass('disabled')) {
          return false;
        }
      });
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
      
      this.linkWp.addEvent('click', function() {
        if (this.hasClass('disabled')) {
          return false;
        }
      });
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
    
  updateView: function() {
    var productsIdString = this.getProductsIdString(),
        btnAddToCart = $$(this.options.btnAddCls);
    
    var product = this.options.variants[productsIdString];
    
    if (product == undefined || (product['status'] == 0)) {
      if ($('productInfoAvailable')) {
        $('productInfoAvailable').innerHTML = '<font color="red">' + this.options.lang.txtNotAvailable + '</font>';
      }
      
      if (btnAddToCart.length > 0) {
        btnAddToCart[0].addClass('disabled');
      }
      
      //disable the compare and wishlist link because the variant product is not available
      if (this.linkCp) {
        this.linkCp.addClass('disabled');
      }
      
      if (this.linkWp) {
        this.linkWp.addClass('disabled');
      }
    } else {
      btnAddToCart[0].removeClass('disabled');
      
      //if it is in the product info page and the product have any variants, add the variants into the compare products link
      if (this.linkCp) {
        var href = this.linkCpHref + '&cid=' + productsIdString.replace(/#/, '_');
        
        this.linkCp.setProperty('href', href);
        
        this.linkCp.removeClass('disabled');
      }
      
      //handler the wishlist
      if (this.linkWp) {
        var href = this.linkWpHref + '&wid=' + productsIdString.replace(/#/, '_');
        
        this.linkWp.setProperty('href', href);
        
        this.linkWp.removeClass('disabled');
      }
      
      if (this.options.hasSpecial == 0) {
        // get the formatted price of the variants product by ajax requst
        this.sendRequest({action: 'get_variants_formatted_price', products_id_string: productsIdString}, function(response) {
          var result = JSON.decode(response);
          
          if (result.success == true) {
            if ($('productInfoPrice')) {
              $('productInfoPrice').set('html', result.formatted_price);
            }
          }else {
            alert(result.feedback);
          }
        }.bind(this));
      }
      
      //product sku maybe missing
      if ($('productInfoSku')) {
        $('productInfoSku').set('text', product['sku']);
      }
     
      if (this.options.displayQty == true) {
        if ($('productInfoQty')) {
          $('productInfoQty').set('text', product['quantity'] + ' ' + this.options.unitClass);
        }
      }
      
      if (product['quantity'] > 0) {
        if ($('productInfoAvailable')) {
          $('productInfoAvailable').set('text', this.options.lang.txtInStock);
        }
      }else {
        if ($('productInfoAvailable')) {
          $('productInfoAvailable').set('text', this.options.lang.txtOutOfStock);
        }
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