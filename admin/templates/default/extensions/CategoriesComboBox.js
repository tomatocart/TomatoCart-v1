/** 
 * $Id: CategoriesComboBox.js $
 * TomatoCart Open Source Shopping Cart Solutions
 * http://www.tomatocart.com

 * Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v2 (1991)
 * as published by the Free Software Foundation.
 */
 
 Toc.CategoriesComboBox = function (config) {
  config.tpl = new Ext.XTemplate(
    '<tpl for=".">',
    '<div class="x-combo-list-item">',
    '<div style="margin-left: {[ this.getMargin(values) ]}">{values.text}</div>',
    '</div>',
    '</tpl>',
    {
      getMargin: function(values) {
        var category_id = values.id.toString();
        var margin = 0;
  
        if (category_id.indexOf("_") != -1) {
          var n = category_id.split("_").length - 1;
          margin = n * 10;
        }
        
        return margin + 'px';
      }
    }
  );
  
  Toc.CategoriesComboBox.superclass.constructor.call(this, config);
};

Ext.extend(Toc.CategoriesComboBox, Ext.form.ComboBox);