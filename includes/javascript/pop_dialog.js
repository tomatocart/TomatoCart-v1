/*
  $Id: pop_dialog.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

var popDialog = new Class({
    Implements: [Options],
    options: {
        //the class of the dialog
        clsEl:'dialog',
        
        //enable the animation
        enableAnimation: true,
        baseClsAnimated: 'animated',
        clsAnimated: 'bounceInDown',
        clsClose: 'btnClose',
        clsHideAnimation: 'slideOutUp'
    },
    
    //init the dialog
    initialize: function(html, options) {
      this.setOptions(options);
      
      this.dlg = this.createDialog(html);
      
      this.currentClsAnimated = this.options.clsAnimated;
      this.showed = false;
    },
    
    /**
     * Show the dialog
     * 
     * return void
     */
    show: function() {
      //whether the dialog is existed in the document
      if (this.showed) {
          if (this.options.enableAnimation === true) {
              this.dlg.removeClass(this.options.clsAnimated);
              this.dlg.removeClass(this.currentClsAnimated);
          }
          
          this.dlg.setStyle('display', 'block');
          
          this.setPosition();
          
          if (this.options.enableAnimation === true) {
              this.dlg.addClass(this.options.clsAnimated);
          }
         
      }else {
          //add it into the document
          $(document.body).adopt(this.dlg);
          
          this.setPosition();
          
          //animate it
          if (this.options.enableAnimation === true) {
              this.dlg.addClass(this.options.baseClsAnimated);
              this.dlg.addClass(this.options.clsAnimated);
          }
          
          this.showed = true;
      }
    },
    
    /**
     * Set the postion of the dialog
     * 
     * return void
     */
    setPosition: function() {
        var e = window, 
        a = 'inner', 
        viewport,
        dlgSize,
        scrollY;
        
        //calculate the viewport width and height
        if ( ! ('innerWidth' in window )) {
            a = 'client';
            e = document.documentElement || document.body;
        }
        viewport = {width: e[a + 'Width'] , height: e[a + 'Height']};
        
        //calucate scoller height
        scrollY = window.pageYOffset || document.body.scrollTop || document.documentElement.scrollTop;
        
        //set position relative to the document
        dlgSize = this.dlg.getSize();
        this.dlg.setStyles({left: ((viewport.width / 2 - dlgSize.x / 2) + 'px'), top: ((viewport.height / 2 - dlgSize.y / 2 + scrollY) + 'px')});
    },
    
    /**
     * Create the dialog
     * 
     * @param html the content of the dialog
     * 
     * return mixed
     */
    createDialog: function(html) {
        var dlg;
        
        if (typeof html != 'undefined') {
            //build the dlg and add it into the document
            dlg = new Element('div', {
                'class': this.options.clsEl,
                'html': html,
                'styles': {
                    'position': 'absolute'
                }
            });
            
            return dlg;
        }
        
        return null;
    },
    
    /**
     * Animate the dialog
     * 
     * return mixed
     */
    doAnimate: function(cls) {
        if (cls && (cls != this.options.clsAnimated)) {
            this.dlg.removeClass(this.options.clsAnimated);
            this.dlg.addClass(cls);
            this.currentClsAnimated = cls;
        }    
    },
    
    /**
     * Hide the dialog
     * 
     * return mixed
     */
    hide: function() {
        this.dlg.setStyle('display', 'none');
    },
    
    /**
     * Update the content of the dialog
     * 
     * @param html
     * 
     * return mixed
     */
    update: function(html) {
        this.dlg.set('html', html);
    },
    

    /**
     * Destroy dialog
     * 
     * return mixed
     */
    destroy: function() {
        this.dlg.destroy();
    }
});