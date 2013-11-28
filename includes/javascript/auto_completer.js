/*
  $Id: auto_completer.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

Autocompleter.implement({
   /**
   * override addChoiceEvents
   *
   * Appends the needed event handlers for a choice-entry to the given element.
   *
   * @param   {Element} Choice entry
   * @return    {Element} Choice entry
   */
  addChoiceEvents: function(el) {
    return el.addEvents({
      'mouseover': this.choiceOver.bind(this, el),
      'click': this.choiceSelect.bind(this, el)
    });
  },
  
  showChoices: function() {
    var match = this.options.choicesMatch, first = this.choices.getFirst(match);
    this.selected = this.selectedValue = null;
    if (this.fix) {
      var pos = this.element.getCoordinates(this.relative), width = this.options.width || 'auto';
      this.choices.setStyles({
        'left': pos.left - 101,
        'top': pos.bottom,
        'width': (width === true || width == 'inherit') ? pos.width : width
      });
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
  }
});

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
    delay: 250,
    width: 235,
    parentId: 'navigationInner',
    selectionLength: 23
  },
  
  initialize: function(el, options) {
    this.options.postVar = el;
    this.parent(el, this.options.remoteUrl, options);
    this.options.postData[this.options.sessionName] = this.options.sessionId;
    
    if (options.template) {
      this.options.postData['template'] = options.template;  
    }
    
    this.setSelectionValueLength(this.options.selectionLength);
  },
  
  //override setSelection method to get the text in the link and enter it into the search field
  setSelection: function(finish) {
      var input = this.selected.inputValue, value = input;
      var start = this.queryValue.length, end = input.length;
      if (input.substr(0, start).toLowerCase() != this.queryValue.toLowerCase()) start = 0;
      if (this.options.multiple) {
          var split = this.options.separatorSplit;
          value = this.element.value;
          start += this.queryIndex;
          end += this.queryIndex;
          var old = value.substr(this.queryIndex).split(split, 1)[0];
          value = value.substr(0, this.queryIndex) + input + value.substr(this.queryIndex + old.length);
          if (finish) {
              var tokens = value.split(this.options.separatorSplit).filter(function(entry) {
                  return this.test(entry);
              }, /[^\s,]+/);
              if (!this.options.allowDupes) tokens = [].combine(tokens);
              var sep = this.options.separator;
              value = tokens.join(sep) + sep;
              end = value.length;
          }
      }
      
      //filter the <a> tag in the selection
      value = value.replace(/<a\s(?:\s*\w*?\s*=\s*".+?")*(?:\s*href\s*=\s*".+?")(?:\s*\w*?\s*=\s*".+?")*\s*>([\s\S]*?)<\/a>/,'$1'); 
      this.observer.setValue(value);
      this.opted = value;
      if (finish || this.selectMode == 'pick') start = end;
      this.element.selectRange(start, end);
      this.fireEvent('onSelection', [this.element, this.selected, value, input]);
  },
  
  setSelectionValueLength: function(length) {
    this.observer.setValue = function(value) {
      value = value.substr(0, length);
      
      this.value = value;
      this.element.set('value', value);
      
      return this.clear();
    };
  }
});