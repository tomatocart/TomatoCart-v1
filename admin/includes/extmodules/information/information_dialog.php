<?php
/*
  $Id: information_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.information.InformationDialog = function(config) {
  
  config = config || {};
  
  config.id = 'information-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_information'); ?>';
  config.layout = 'fit';
  config.width = 850;
  config.height = 530;
  config.modal = true;
  config.iconCls = 'icon-information-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text:TocLanguage.btnSave,
      handler: function(){
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
  
  this.addEvents({'saveSuccess' : true})

  Toc.information.InformationDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.information.InformationDialog, Ext.Window, {

  show: function(id) {
    var articlesId = id || null;
    
    this.frmArticle.form.reset();  
    this.frmArticle.form.baseParams['articles_id'] = articlesId;
    
    if(articlesId > 0) {
      this.frmArticle.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_article',
          articles_id: articlesId
        },
        success: function(form, action) {
          Toc.information.InformationDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        }, 
        scope: this
      });
    }
    Toc.information.InformationDialog.superclass.show.call(this);
  },

  getContentPanel: function() {
    this.pnlGeneral = new Toc.information.GeneralPanel();
    this.pnlMetaInfo = new Toc.information.MetaInfoPanel();
    
    tabInformation = new Ext.TabPanel({
      activeTab: 0,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlGeneral,
        this.pnlMetaInfo  
      ]
    });
    
    return tabInformation;
  },
  
  getDataPanel: function() {
    this.pnlData = new Ext.Panel({
      layout: 'column',
      region: 'north',
      border: false,
      autoHeight: true,
      style: 'padding: 6px',
      items: [
        {
          layout: 'form',
          border: false,
          labelSeparator: ' ',
          columnWidth: .7,
          autoHeight: true,
          defaults: {
            anchor: '97%'
          },
          items: [
            {
              layout: 'column',
              border: false,
              items: [
                {
                  layout: 'form',
                  border: false,
                  labelSeparator: ' ',
                  width: 200,
                  items: [
                    {
                      fieldLabel: '<?php echo $osC_Language->get('field_publish'); ?>', 
                      xtype:'radio', 
                      name: 'articles_status',
                      inputValue: '1',
                      checked: true,
                      boxLabel: '<?php echo $osC_Language->get('field_publish_yes'); ?>'
                    }
                  ]
                },
                {
                  layout: 'form',
                  border: false,
                  width: 200,
                  items: [
                    {
                      hideLabel: true,
                      xtype:'radio',
                      inputValue: '0', 
                      name: 'articles_status',
                      boxLabel: '<?php echo $osC_Language->get('field_publish_no'); ?>'
                    }
                  ]
                }
              ]
            },
            {xtype:'numberfield', fieldLabel: '<?php echo $osC_Language->get('field_order'); ?>', name: 'articles_order', id: 'articles_order'}
          ]
        }
      ]
    });
    
    return this.pnlData;
  },
  
  buildForm: function() {
    this.frmArticle = new Ext.form.FormPanel({
      fileUpload: true,
      layout: 'border',
      title:'<?php echo $osC_Language->get('heading_title_data'); ?>',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'information',
        action : 'save_article'
      },
      deferredRender: false,
      items: [this.getContentPanel(), this.getDataPanel()]
    });  
    
    return this.frmArticle;
  },
  
  submitForm : function() {
    this.frmArticle.form.submit({
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