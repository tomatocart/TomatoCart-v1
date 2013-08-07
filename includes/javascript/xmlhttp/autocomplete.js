/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

var returnUsed = false;

function autoComplete(autoCompleteField, divBlock) {
  var my = this;

  this.autoCompleteField = autoCompleteField;
  this.autoCompleteListing = new Array();
  this.autoCompleteDivBlock = document.getElementById(divBlock);
  this.inputText = null;
  this.selected = -1;

  var KEY_TAB = 9;
  var KEY_RETURN = 13;
  var KEY_ESC = 27;
  var KEY_UP = 38;
  var KEY_DOWN = 40;

  autoCompleteField.setAttribute("autocomplete", "off");

  autoCompleteField.onkeydown = function(evt) {
    var keyPressed = getKeyCode(evt);

    switch (keyPressed) {
      case KEY_TAB:
      case KEY_RETURN:
        if (isDivVisible(my.autoCompleteDivBlock) && (my.selected > -1)) {
          my.useSelection();

          if (keyPressed == KEY_RETURN) {
            returnUsed = true;
          }
        }

        break;

      case KEY_ESC:
        my.hideDiv();
        break;

      case KEY_UP:
        if (my.selected > 0) {
          my.selected--;
          my.changeHighlight();
        }

        break;

      case KEY_DOWN:
        if (my.selected < (my.autoCompleteListing.length - 1)) {
          my.selected++;
          my.changeHighlight();
        }

        break;
    }
  };

  autoCompleteField.onkeyup = function(evt) {
    var keyPressed = getKeyCode(evt);

    switch (keyPressed) {
      case KEY_TAB:
      case KEY_RETURN:
      case KEY_ESC:
      case KEY_UP:
      case KEY_DOWN:
        break;
      default:
        if ( (this.value != my.inputText) && (this.value.length > 0) ) {
          my.inputText = this.value;
          my.getListing();
        } else {
          my.hideDiv();
        }
    }
  };

  autoCompleteField.onblur = function() {
    if (isDivVisible(my.autoCompleteDivBlock)) {
      setTimeout("hideDiv(document.getElementById('" + my.autoCompleteDivBlock.id + "'))", 500);
    }
  };

  this.useSelection = function() {
    if (this.selected > -1) {
      this.autoCompleteField.value = this.autoCompleteListing[this.selected];

      my.hideDiv();

	    setTimeout("document.getElementById('" + this.autoCompleteField.id + "').focus()", 0);
    }
  };

  this.changeHighlight = function() {
    var list = this.autoCompleteDivBlock.getElementsByTagName('LI');

    for (i in list) {
      var entry = list[i];

      if (this.selected == i) {
        entry.className = "selected";
      } else {
        entry.className = "";
      }
    }
  };

  this.positionDiv = function() {
    var tmpElement = this.autoCompleteField;
    var x = 0;
    var y = tmpElement.offsetHeight;

    while (tmpElement.offsetParent && (tmpElement.tagName.toUpperCase() != 'BODY')) {
      x += tmpElement.offsetLeft;
      y += tmpElement.offsetTop;
      tmpElement = tmpElement.offsetParent;
    }

    x += tmpElement.offsetLeft;
    y += tmpElement.offsetTop;

    this.autoCompleteDivBlock.style.left = x + 'px';
    this.autoCompleteDivBlock.style.top = y + 'px';
    this.autoCompleteDivBlock.style.minWidth = this.autoCompleteField.offsetWidth + "px";
  };

  this.createDiv = function() {
    this.autoCompleteDivBlock.innerHTML = "<ul></ul>";

    var ul = document.createElement('ul');

    for (i in this.autoCompleteListing) {
      var word = this.autoCompleteListing[i];

      var li = document.createElement('li');
      var a = document.createElement('a');
      a.href = "javascript:return false;";
      a.innerHTML = word;
      li.appendChild(a);

      if (my.selected == i) {
        li.className = "selected";
      }

      ul.appendChild(li);
    }

    this.autoCompleteDivBlock.replaceChild(ul, this.autoCompleteDivBlock.childNodes[0]);

    ul.onmouseover = function(evt) {
      var target = getEventSource(evt);

      while (target.parentNode && (target.tagName.toUpperCase() != 'LI')) {
        target = target.parentNode;
      }

      var list = my.autoCompleteDivBlock.getElementsByTagName('LI');

      for (i in list) {
        var entry = list[i];

        if (entry == target) {
          my.selected = i;

          break;
        }
      }

      my.changeHighlight();
    };

    ul.onclick = function(evt) {
      my.useSelection();

      cancelEvent(evt);

      return false;
    };

    showDiv(my.autoCompleteDivBlock);
  };

  this.hideDiv = function() {
    hideDiv(my.autoCompleteDivBlock);
    this.selected = -1;
  }

  this.getListing = function() {
    if (document.getElementById(my.autoCompleteField.id + "_icon")) {
      document.getElementById(my.autoCompleteField.id + "_icon").src = "images/progress.gif";
    }

    loadXMLDoc("rpc.php?action=getDirectoryPath&dir=" + urlEncode(my.inputText), this.handleHttpResponse_getListing)
  };

  this.handleHttpResponse_getListing = function() {
    if (http.readyState == 4) {
      if (http.status == 200) {
        my.autoCompleteListing = new Array();

        var response = http.responseText.split(/\[{2}([^|]*?)(?:\|([^|]*?)){0,1}\]{2}/);
        if (response[1] == '0') {
          my.autoCompleteListing = response[2].split(';');

          my.createDiv();
          my.positionDiv();
          showDiv(my.autoCompleteDivBlock);

          if (document.getElementById(my.autoCompleteField.id + "_icon")) {
            document.getElementById(my.autoCompleteField.id + "_icon").src = "images/progress_pending.gif";
          }
        } else if (response[1] == '1') {
          if (document.getElementById(my.autoCompleteField.id + "_icon")) {
            document.getElementById(my.autoCompleteField.id + "_icon").src = "images/progress_pending.gif";
          }

          if (isDivVisible(my.autoCompleteDivBlock)) {
            my.hideDiv();
          }
        } else if (response[1] == '-1') {
          if (document.getElementById(my.autoCompleteField.id + "_icon")) {
            document.getElementById(my.autoCompleteField.id + "_icon").src = "images/failed.gif";
          }
        }
      }
    }
  }
}
