<?php
/*
  $Id:search_terms_edit_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.search_terms.SearchTermEditDialog = function(config) {
 
  config = config || {};

  config.id = 'search_terms_edit_dialog-win';
  config.width = 460;
  config.modal = true;
  config.iconCls = 'icon-search_terms-win';
  config.items = this.buildForm();

  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnClose,
      handler: function(){
        this.close();
      },
      scope:this
    }
  ];

  this.addEvents({'saveSuccess' : true});  
  
  Toc.search_terms.SearchTermEditDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.search_terms.SearchTermEditDialog, Ext.Window, {
  show: function(searchTermsId) {
    var searchTermsId = searchTermsId || null;
        
    this.frmSearchTerm.form.reset();
    this.frmSearchTerm.form.baseParams['search_terms_id'] = searchTermsId;
    
    if (searchTermsId > 0) {
      this.frmSearchTerm.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'search_terms',
          action: 'load_search_term'
        },
        success: function() {
          Toc.search_terms.SearchTermEditDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this
      });
    } else {
      Toc.search_terms.SearchTermEditDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function() {
    this.frmSearchTerm = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'search_terms',
        action: 'save'
      },
      layout: 'form',
      defaults: {
        anchor: '97%'
      }, 
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 160,
      style: 'padding: 8px',
      border: false,
      items: [
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_search_term'); ?>', 
          name: 'text' 
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_products_count'); ?>', 
          name: 'products_count' 
        },
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_search_count'); ?>', 
          name: 'search_count' 
        },
        this.txtSynonym = new Ext.form.TextField({
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_synonym'); ?>', 
          name: 'synonym',
          emptyText: '<?php echo $osC_Language->get('field_synonym_empty_text'); ?>'
        }),
        {
          layout: 'column',
          border: false,                        
          items: [
            {
              layout: 'form',
              width: 250,
              labelSeparator: ' ',
              border: false,            
              items: [
                {
                  xtype: 'radio',
                  name: 'show_in_terms',
                  fieldLabel: '<?php echo $osC_Language->get('field_show_in_terms'); ?>',
                  boxLabel: '<?php echo $osC_Language->get('option_yes'); ?>',
                  inputValue: '1'
                }
              ]
            },
            {
              layout: 'form',
              width: 50,     
              border: false,
              items: [
                {
                  xtype: 'radio',
                  name: 'show_in_terms',
                  hideLabel: true,
                  boxLabel: '<?php echo $osC_Language->get('option_no'); ?>',
                  inputValue: '0'                                    
                }
              ]
            }
          ]            
        }            
      ]
    });  
    
    return this.frmSearchTerm;
  },

  submitForm : function() {
    if (Ext.isEmpty(this.txtSynonym.getValue())) {
      this.txtSynonym.setValue(' ');
    }
    
    this.frmSearchTerm.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if(action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });   
  }
});