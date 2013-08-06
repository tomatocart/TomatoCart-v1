/*
  $Id: polls.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

	var Polls = new Class({
	  Implements: Options,
	  options: {
	    frmPolls: 'frmPolls',
	    btnPollVote: 'btnPollVote',
	    btnPollResult: 'btnPollResult'
	  },
	  
	  initialize: function(options){
	    this.setOptions(options);
	    
      var frmPolls = $(this.options.frmPolls);
      var btnPollVote =  $(this.options.btnPollVote);
      var btnPollResult = $(this.options.btnPollResult);
      
      if(btnPollVote){
      	btnPollVote.addEvent('click', function(e){
          var choice = false;
          var votes = $$('input.poll_votes');
          $each(votes, function(vote) {
            if (vote.checked == true) {
              choice = true;
            }
          }); 
          
          if (choice == true) {
            var query = frmPolls.toQueryString() + '&action=vote';
            $("polls").empty().addClass('loading');
            
            this.sendRequest(query);
          }
        }.bind(this));
      }
      
      if(btnPollResult){
      	btnPollResult.addEvent('click', function(e){
          var query = frmPolls.toQueryString() + '&action=poll_result';
          $("polls").empty().addClass('loading');
          this.sendRequest(query);
        }.bind(this));
      }
	  },
	  
	  sendRequest: function(data) {
	    var loadRequest = new Request({
	      url: 'json.php?module=polls',
	      data: data,
	      method : 'get',
	      onSuccess: function(responseText) {
	        var result = JSON.decode(responseText);
	        
	        $("polls").removeClass('loading');
	        if(result.success) {
	          $("polls").set("html", result.content);
	        }
	      },
	      onFailure: function() {
	        $("polls").removeClass('loading');
	      }
	    }).send();
	  }
	});