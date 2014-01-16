/*
  $Id: auto_completer.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

var TocAutoCompleter = new Class({
  Extends: Autocompleter.Request.JSON,
  
  options: {
    remoteUrl: 'json.php',
    sessionName: 'sid',
    sessionId: null,
    postData: {module: 'auto_completer', action: 'get_products'},
    minLength: 3,
    filterSubset: true,
    cache: true,
    delay: 0,
    imageGroup: 'thumbnail',
    moreBtnText: 'Get More'
  },
  
  initialize: function(el, options) {
    this.options.postVar = el;
    this.parent(el, this.options.remoteUrl, options);
    this.options.postData[this.options.sessionName] = this.options.sessionId;
    
    if (options.template) {
      this.options.postData['template'] = options.template;  
    }
  },
  
 //override showChoices method to remove the hard coded style
  showChoices: function() {
      if (this.options.imageGroup == 'thumbnail') {
          this.choices.addClass('useThumbnail');
      }
      
      var match = this.options.choicesMatch, first = this.choices.getFirst(match);
      this.selected = this.selectedValue = null;
      if (this.fix) {
        //get the correct position for the autocompleter list
        var pos = this.element.getCoordinates(this.relative),
            sizeTrigger = this.element.getSize(),
            sizeChoices;
           
        if (this.options.width) {
          this.choices.setStyle('width', this.options.width + 'px');
        }
        
        sizeChoices = this.choices.getSize();
        if (!this.relative) {
            this.relative = sizeChoices.x - sizeTrigger.x || sizeTrigger.x - sizeChoices.x;
        }
        
        this.choices.setStyles({
            'left': pos.left - this.relative,
            'top': pos.bottom
        });
  
        
        //hide the choice automatically when the mouse leave out
        this.choices.addEvent('mouseleave',function() {
            this.hideChoices();
        }.bind(this));
      }
      if (!first) return;
      if (!this.visible) {
          this.visible = true;
          this.choices.setStyle('display', '');
          if (this.fx) this.fx.start(1);
          this.fireEvent('onShow', [this.element, this.choices]);
      }
      if (this.options.selectFirst || this.typeAhead || first.inputValue == this.queryValue) this.choiceOver(first, this.typeAhead);
      var items = this.choices.getChildren(match), max = this.options.maxChoices;
      var styles = {'overflowY': 'hidden', 'height': ''};
      this.overflown = false;
      if (items.length > max) {
          var item = items[max - 1];
          styles.overflowY = 'scroll';
          styles.height = item.getCoordinates(this.choices).bottom;
          this.overflown = true;
      };
      this.choices.setStyles(styles);
      this.fix.show();
      if (this.options.visibleChoices) {
          var scroll = document.getScroll(),
          size = document.getSize(),
          coords = this.choices.getCoordinates();
          if (coords.right > scroll.x + size.x) scroll.x = coords.right - size.x;
          if (coords.bottom > scroll.y + size.y) scroll.y = coords.bottom - size.y;
          window.scrollTo(Math.min(scroll.x, coords.left), Math.min(scroll.y, coords.top));
      }
      
      if (this.choices.getElement('div.more') === null) {
          var moreContainer = new Element('div', {
              'class': 'more'
          }),
          moreBtn = new Element('a', {
              'href': '#',
              'class': 'button squre medium btn',
              'html': this.options.moreBtnText
          });
          
          this.choices.adopt(moreContainer);
          
          moreContainer.adopt(moreBtn);
          
          moreBtn.addEvent('click', function(e) {
              e.stop();
              
              this.element.getParent('form').submit();
              
              return false;
          }.bind(this));
      }
  },
  
  update: function(tokens) {
      this.choices.empty();
      this.cached = tokens;
      var type = tokens && $type(tokens);
      if (!type || (type == 'array' && !tokens.length) || (type == 'hash' && !tokens.getLength())) {
          (this.options.emptyChoices || this.hideChoices).call(this);
      } else {
          if (this.options.maxChoices < tokens.length && !this.options.overflow) tokens.length = this.options.maxChoices;
          
          tokens.each(this.options.injectChoice || function(token){
              var choice = new Element('li', {'html': token, 'class': 'clearfix'}),
                  choiceLink = choice.getElement('a'),
                  choiceText = choiceLink.get('text'),
                  choiceMarkedValue = this.markQueryValue(choiceText);
              
              choice.inputValue = choiceText;
              
              choiceLink.set('html', choiceMarkedValue);
              
              this.addChoiceEvents(choice).inject(this.choices);
          }, this);
          this.showChoices();
      }
  },
  
  /**
   * markQueryValue
   *
   * Marks the queried word in the given string with <span class="autocompleter-queried">*</span>
   * Call this i.e. from your custom parseChoices, same for addChoiceEvents
   *
   * @param   {String} Text
   * @return    {String} Text
   */
  markQueryValue: function(str) {
    var markValue = (!this.options.markQuery || !this.queryValue) ? str
      : str.replace(new RegExp('(' + ((this.options.filterSubset) ? '' : '^') + this.queryValue.escapeRegExp() + ')', (this.options.filterCase) ? '' : 'i'), '<span class="autocompleter-queried">$1</span>');
    
    return markValue;
  },
  
  query: function(){
      this.element.removeClass('auto-loaded').addClass('auto-loading');
      this.parent();
  },
  
  queryResponse: function(response) {
      this.parent(response);
      this.element.removeClass('auto-loading').addClass('auto-loaded');
  },
  
  choiceSelect: function(choice) {
      var link = choice.getElement('a');
      
     this.parent(choice);
      
      window.location = link.getProperty('href');
  }  
});