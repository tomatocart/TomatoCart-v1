<?php
/*
  $Id: google_sitemap_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.google_sitemap.GoogleSitemapDialog = function(config) {
  
  config = config || {};
  
  config.id = 'google_sitemap-win';
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.width = 600;
  config.height = 500;
  config.iconCls = 'icon-google_sitemap-win';
  config.layout = 'fit';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];
  
  Toc.google_sitemap.GoogleSitemapDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.google_sitemap.GoogleSitemapDialog, Ext.Window, {

	buildForm: function() {
    var store = new Ext.data.SimpleStore({
      fields: ['id', 'text'],
      data: [
         ['daily','<?php echo $osC_Language->get('Daily'); ?>'],
         ['monthly','<?php echo $osC_Language->get('Monthly'); ?>'],
         ['yearly','<?php echo $osC_Language->get('Yearly'); ?>']
       ]
    });
    
    var dsLanguages = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'languages', 
        action: 'get_languages'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        fields: ['id', 'text']
      })                                                                                    
    });
    
		this.fsCreateSitemap = new Ext.form.FieldSet({
			labelWidth: 130,
      title: '<?php echo $osC_Language->get('button_create_sitemaps'); ?>',
      layout: 'form',
      autoHeight: true,
      layoutConfig: {
        labelSeparator: ''
      },
      items: [
        {
          xtype: 'combo', 
          fieldLabel: '<?php echo $osC_Language->get('field_language_selection'); ?>', 
          id: 'languages',
          width: 230,
          name: 'languages',
          mode: 'remote', 
          store: dsLanguages,
          displayField: 'text',
          valueField: 'id',
          triggerAction: 'all',
          hiddenName: 'languages_code',
          readOnly: true,
          allowBlank: false
        },
        {
          layout: 'column',
          border: false,
          items: [
           {
              columnWidth: 0.55,
              layout: 'form',
              border: false,
              defaults: {xtype: 'combo', store: store, mode: 'local', valueField: 'id', displayField: 'text', value: 'daily', allowBlank: false, editable: false, triggerAction: 'all', anchor: '90%'},
              labelSeparator: ' ',   
              items: [
                {fieldLabel: '<?php echo $osC_Language->get("field_categories"); ?>', hiddenName: 'categories_frequency' }, 
                {fieldLabel: '<?php echo $osC_Language->get("field_products"); ?>', hiddenName: 'products_frequency'},
                {fieldLabel: '<?php echo $osC_Language->get("field_articles"); ?>', hiddenName: 'articles_frequency'}
              ] 
            },
            {
              columnWidth: 0.45,
              layout: 'form',
              border: false,
              labelWidth: 70,
              defaults: {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get('field_priority'); ?>', decimalPrecision: 2, allowNegative: false, allowBlank: false, maxValue: 1, minValue: 0, anchor: '90%'},
              labelSeparator: ' ', 
              items: [
                {name: 'categories_priority', value: 0.5}, 
                {name: 'products_priority', value: 0.5}, 
                {name: 'articles_priority', value: 0.25}
              ]
            }
          ]      
        }
       
      ],
      buttons: [
      	new Ext.Button({
      		text: '<?php echo $osC_Language->get('button_create_sitemaps'); ?>',
      		handler: function(){
        		this.createSitemap();
      		},
      		scope:this
      	})
      ]
    });
    
    this.fsSubmitSitemap = new Ext.form.FieldSet({
      title: '<?php echo $osC_Language->get('button_submit_sitemaps'); ?>',
      autoHeight: true,
      items: [{xtype: 'statictextfield', hideLabel: true, encodeHtml:false, value: '<?php echo $osC_Language->get('introduction_google_sitemaps_submission'); ?>'}],
      buttons: [
      	new Ext.Button({
      		text: '<?php echo $osC_Language->get('button_submit_sitemaps'); ?>',
      		handler: function(){
        		this.submitSitemap();
      		},
      		scope:this
      	})
      ]
    });
    
    this.frmGoogleSitemap = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      style: 'padding: 10px',
      border: false,
      items: [this.fsCreateSitemap, this.fsSubmitSitemap]
    });
    
    return this.frmGoogleSitemap;
	},
	
	createSitemap: function() {
		this.frmGoogleSitemap.form.submit({
      params: {
        module: 'google_sitemap',
        action: 'create_google_sitemap'
      },
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });  
	},
	
	submitSitemap: function() {
	  window.open("<?php echo 'http://www.google.com/webmasters/sitemaps/ping?sitemap=' . HTTP_SERVER . DIR_WS_HTTP_CATALOG . 'sitemapsIndex.xml'; ?>", "google","resizable=1,statusbar=5,width=400,height=200,top=0,left=50,scrollbars=yes");
	}
});
