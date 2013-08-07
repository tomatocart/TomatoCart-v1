/*
  $Id: customizations.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

var Customizations = new Class({
  Implements: Options,

  initialize: function (options) {
    this.setOptions(options);
    
    if (!$defined(this.options.frmCustomizations)) {
      return;
    }

    this.options.frmCustomizations.addEvent('submit', function(e) {
      var error = false;
      var message = '';
  
      var inputs = this.getElements("input");
      var i = 0, count = 0;
      
      inputs.each( function(input, index) {
        if (input.getProperty('is_required')) {
          if (input.value == null && error == false) {
            message = options.customizationNecessaryErrMsg + '\n';
            error = true;
          }
        }
      });
      
      if (error == true) {
        alert(message);
        e.preventDefault();
      }
    });
  }
});