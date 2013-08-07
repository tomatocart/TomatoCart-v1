<?php
/*
  $Id: faqs_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.faqs.FaqsDialog = function(config) {
  
  config = config || {};
  
  config.id = 'faqs-dialog-win';
  config.title = '<?php echo $osC_Language->get('heading_title_new_faq'); ?>';
  config.layout = 'fit';
  config.width = 680;
  config.height = 450;
  config.modal = true;
  config.iconCls = 'icon-faqs-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
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

  Toc.faqs.FaqsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.faqs.FaqsDialog, Ext.Window, {

  show: function(id) {
    var faqsId = id || null;
    
    this.frmFaq.form.reset();
    this.frmFaq.form.baseParams['faqs_id'] = faqsId;  
   
    if (faqsId > 0) { 
      this.frmFaq.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_faq'
        },
        success: function(form, action) {
          Toc.faqs.FaqsDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.faqs.FaqsDialog.superclass.show.call(this);
    }
  },

  getContentPanel: function() {
    this.tabLanguage = new Ext.TabPanel({
      region: 'center',
      title:'<?php echo $osC_Language->get('heading_title_data'); ?>',
      activeTab: 0,
      deferredRender: false
    });  
    
    <?php
      foreach ($osC_Language->getAll() as $l) {
      
        echo 'var pnlLang' . $l['code'] . ' = new Ext.Panel({
          labelWidth: 100,
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          labelSeparator: \' \',
          style: \'padding: 8px\',
          defaults: {
            anchor: \'97%\'
          },
          items: [
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_faq_question') . '\', name: \'faqs_question[' . $l['id'] . ']\', allowBlank: false},
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_faq_url') . '\', name: \'faqs_url[' . $l['id'] . ']\'},
            {xtype: \'htmleditor\', fieldLabel: \'' . $osC_Language->get('filed_faq_answer') . '\', name: \'faqs_answer[' . $l['id'] . ']\', height: \'auto\'}
            ]
        });
        
        this.tabLanguage.add(pnlLang' . $l['code'] . ');
        ';
      }
    ?>
    
    return this.tabLanguage;
  },
  
  getDataPanel: function() {
  
    this.pnlData = new Ext.Panel({
      region: 'north',
      layout: 'form', 
      autoHeight: true,
      labelSeparator: ' ',
      border: false,
      labelWidth: 100,
      defaults: {
        anchor: '97%'
      },
      items:[
        {
          layout: 'column',
          border: false,
          width: 250,
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
                  name: 'faqs_status',
                  inputValue: '1',
                  checked: true,
                  boxLabel: '<?php echo $osC_Language->get('field_publish_yes'); ?>'
                }
              ]
            }, 
            {
              width: 100,
              layout: 'form',
              border: false,
              items: [
                {
                  hideLabel: true,
                  xtype:'radio',
                  inputValue: '0', 
                  name: 'faqs_status',
                  boxLabel: '<?php echo $osC_Language->get('field_publish_no'); ?>'
                }
              ]
            }
          ]
        },
        {
          fieldLabel: '<?php echo $osC_Language->get('field_order'); ?>', 
          xtype:'numberfield', 
          name: 'faqs_order',
          minValue: 1,
          allowBlank: false
        }
      ] 
    });
    
    return this.pnlData;
  },
  
  buildForm: function() {
    this.frmFaq = new Ext.form.FormPanel({
      title: '<?php echo $osC_Language->get('heading_title_data'); ?>',
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'faqs',
        action : 'save_faq'
      },
      deferredRender: false,
      items: [this.getDataPanel(), this.getContentPanel()]
    });  
    
    return this.frmFaq;
  },
  
  submitForm : function() {
    this.frmFaq.form.submit({
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