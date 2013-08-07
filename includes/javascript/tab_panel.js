/*
  $Id: tab_panel.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  var TabPanel = new Class({
	  Implements: Options,
	
	  initialize: function (options) {
	    this.setOptions(options);
	    
	    this.iniTabs();
	  },
	  
	  iniTabs: function() {
	  	this.tabs = this.options.panel.getElements('a');
	  	this.tabs.each(function(tab, index) {
	  	  //attach click event
        tab.addEvent('click', function() {
          if(tab.hasClass('unselect')) {
            //display content
            $(tab.get('tab')).setStyle('display', 'block');
            $(this.current.get('tab')).setStyle('display', 'none');
            
            //switch tab
            tab.removeClass('unselect').addClass('select');
            this.current.removeClass('select').addClass('unselect');
            this.current = tab; 
          }
        }.bind(this));

        //initialize tabs
        var selected = false; 
        if (this.options.activeTab != '') {
          if (this.options.activeTab == tab.get('tab')) {
            selected = true;
          }
        } else {
          selected = (index == 0) ? true : false;
        }
	  		
        if (selected == true) {
          tab.addClass("select");
          $(tab.get('tab')).setStyle('display', 'block');
          this.current = tab;
        } else {
          tab.addClass("unselect");
          $(tab.get('tab')).setStyle('display', 'none');
        }
      }.bind(this));
	  } 
  });