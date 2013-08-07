/*
  $Id: reviews.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

var Reviews = new Class({
  Implements: Options,

  initialize: function (options) {
    this.setOptions(options);
    
    if (!$defined(this.options.frmReviews)) {
      return;
    }

    this.options.frmReviews.addEvent('submit', function(e) {
      var error = false;
      var message = '';
  
      //check ratings
      if (options.flag == 0) {
        if (!((this.rating[0].checked) || (this.rating[1].checked) || (this.rating[2].checked) || (this.rating[3].checked) || (this.rating[4].checked))) {
          error = true;
        }
      } else {
        var inputs = this.getElements("input");
        var i = 0, count = 0;
        
        inputs.each( function(input, index) {
          if (input.type == "radio") {
            if (input.title == "radio" + i) {
              if (input.checked == true) {
                count = count + 1;
                i++;
              }
            }
          }
        });
        
        if (count < options.ratingsCount) {
          error = true;
        }
      }
      
      if (error == true) {
        message = options.ratingsErrMsg;
      }
      
      //check reviews
      if ($('review').value.length < options.reviewMinLength) {
        error = true;
        message = message + '\n' + options.reviewErrMsg;
      }
     
      if (error == true) {
        alert(message);
        e.preventDefault();
      }
    });
  }
});