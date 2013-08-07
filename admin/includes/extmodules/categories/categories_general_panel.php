<?php
/*
  $Id: categories_general_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.categories.GeneralPanel = function(config) {
  config = config || {};    
  
  config.title = '<?php echo $osC_Language->get('section_general'); ?>';
  config.layout = 'form';
  config.layoutConfig = {labelSeparator: ''};
  config.defaults = {anchor: '97%'};
  config.labelWidth = 160;
  config.items = this.buildForm();
    
  Toc.categories.GeneralPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.categories.GeneralPanel, Ext.Panel, {

  buildForm: function() {
    var items = [];
    
    dsParentCategories = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'categories',
        action: 'list_parent_category'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
        fields: [
          'id', 
          'text'
        ]
      }),
      autoLoad: true
    });
    
    this.cboParentCategories = new Toc.CategoriesComboBox({
      store: dsParentCategories,
      displayField: 'text',
      fieldLabel: '<?php echo $osC_Language->get("field_parent_category"); ?>',
      valueField: 'id',
      name: 'parent_category',
      hiddenName: 'parent_category_id',
      triggerAction: 'all'
    });        
    
    items.push(this.cboParentCategories);
    
    <?php
      $i = 1; 

      foreach ( $osC_Language->getAll() as $l ) {
        echo 'var lang' . $l['id'] . ' = new Ext.form.TextField({name: "categories_name[' . $l['id'] . ']",';       
        if ($i != 1 ) 
          echo ' fieldLabel:"&nbsp;", ';
        else
          echo ' fieldLabel:"' . $osC_Language->get('field_name') . '", ';
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;',";
        echo 'allowBlank: false});';
        echo 'items.push(lang' . $l['id'] . ');';
        $i++;
      }     
    ?>
        
    items.push({xtype: 'fileuploadfield', fieldLabel: '&nbsp;<?php echo $osC_Language->get("field_image"); ?>', name: 'image'});
    items.push({xtype: 'panel', name: 'categories_image', id: 'categories_image_panel', border: false});
    items.push({
      layout: 'column',
      border: false,
      items:[{
        id: 'status',
        layout: 'form',
        labelSeparator: ' ',
        border: false,
        items:[{fieldLabel: '&nbsp;<?php echo $osC_Language->get('field_status'); ?>', xtype:'radio', name: 'categories_status', boxLabel: '<?php echo $osC_Language->get('status_enabled'); ?>', xtype:'radio', inputValue: '1', checked: true}]
      },{
        layout: 'form',
        border: false,
        items: [{fieldLabel: '&nbsp;<?php echo $osC_Language->get('status_disabled'); ?>', boxLabel: '<?php echo $osC_Language->get('status_disabled'); ?>', xtype:'radio', name: 'categories_status', hideLabel: true, inputValue: '0'}]
      }]});
    
    items.push({xtype: 'numberfield', fieldLabel: '&nbsp;<?php echo $osC_Language->get("field_sort_order"); ?>', name: 'sort_order'});
    
    return items;    
  } 
});