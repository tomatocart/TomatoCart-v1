/** 
 * $Id: tocHeader.js $
 * TomatoCart Open Source Shopping Cart Solutions
 * http://www.tomatocart.com

 * Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v2 (1991)
 * as published by the Free Software Foundation.
 */

Ext.namespace("Toc.ux.Wiz");

Toc.ux.Wiz.Header = Ext.extend(Ext.ux.Wiz.Header, {
  descriptionEl: null,
  autoEl: {
    tag: 'div',
    cls: 'ext-ux-wiz-Header',
    children : [{
      tag: 'div',
      cls: 'ext-ux-wiz-Header-title'
    }, {
      tag: 'div',
      children: [{
        tag: 'div',
        cls: 'ext-ux-wiz-Header-step'
      }, {
        tag: 'div',
        cls: 'ext-ux-wiz-step-description'
      }, {
        tag: 'div',
        cls: 'ext-ux-wiz-Header-stepIndicator-container'
      }]
    }]
  },
        
  updateDescription: function(description) {
    this.descriptionEl.update(description);
  },
        
  onRender: function(ct, position){
    Toc.ux.Wiz.Header.superclass.onRender.call(this, ct, position);

    var el = this.el.dom.firstChild;
    var ns = el.nextSibling;

    this.descriptionEl = new Ext.Element(ns.firstChild.nextSibling);
  }
});