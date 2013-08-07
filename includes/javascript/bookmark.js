/*
  $Id: bookmark.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

var TocBookmark = new Class({
  Implements: [Options],
  options: {
    title: document.title,
    url: window.location.href
  },
  
  initialize: function (options) {
    this.setOptions(options);
    
    var bookmarkEl = $(this.options.bookmark)
    var anchorEl = new Element('a');
    var imgEl = new Element('img',{'title': this.options.text, 'src': this.options.img});
    
    if (window.opera && window.print) {
      anchorEl.setProperties({
        'href': this.options.url,
        'title': this.options.title,
        'rel': 'sidebar'
      });
    } else {
      anchorEl.setStyle('cursor', 'pointer');
    }
    
    anchorEl.appendChild(imgEl);
    anchorEl.appendText(this.options.text);
    bookmarkEl.appendChild(anchorEl);
    
    anchorEl.addEvent('click', function() {
      this.onBookmarkClicked();
    }.bind(this));
  },
  
  onBookmarkClicked: function() {
    if (window.sidebar) {
      window.sidebar.addPanel(this.options.title, this.options.url,"");
    } else if(document.all) {
      window.external.AddFavorite( this.options.url, this.options.title); 
    }  if (window.opera && window.print) {
      alert('Press ctrl+D to bookmark (Command+D for macs) after you click Ok');
    } else if (window.chrome) {
      alert('Press ctrl+D to bookmark (Command+D for macs) after you click Ok');
    }
  }
});