<?php
/*
  $Id: polls_dialog.php 
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.polls.PollsDialog = function(config) {
  config = config || {};
  
  config.id = 'polls-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_poll'); ?>';
  config.modal = true;
  config.width = 500;
  config.iconCls = 'icon-polls-win';
  config.items = this.buildForm();
    
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];

  this.addEvents({'saveSuccess' : true});  
  
  Toc.polls.PollsDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.polls.PollsDialog, Ext.Window, {
  
  show: function (id) {
    var pollsId = id || null;
    
    this.frmPolls.form.reset();
    this.frmPolls.form.baseParams['polls_id'] = pollsId;
    
    if (pollsId > 0) {
    
      this.frmPolls.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'polls',
          action: 'load_poll'
        },
        success: function(form, action) {
          Toc.polls.PollsDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.polls.PollsDialog.superclass.show.call(this);
    }
  },
      
  buildForm: function() {
    var store = new Ext.data.SimpleStore({
      fields: ['id', 'text'],
      data: [
         ['0', '<?php echo $osC_Language->get('field_polls_single_choice'); ?>'],
         ['1', '<?php echo $osC_Language->get('field_polls_multiple_choice'); ?>']
       ]
    });
    
    this.cboPollsType = new Ext.form.ComboBox({
      fieldLabel: '<?php echo $osC_Language->get('field_polls_type'); ?>', 
      store: store, 
      displayField: 'text', 
      valueField: 'id', 
      name: 'polls_type',
      hiddenName: 'polls_type', 
      readOnly: true, 
      forceSelection: true,
      mode: 'local',
      value: '0',
      triggerAction: 'all'
    });
    
    this.frmPolls = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'polls',
        action: 'save_poll'
      }, 
      defaults: {
        anchor: '98%'
      },
      style: 'padding: 8px',
      border: false,
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 120,
      items: [
        <?php
        $i = 1;
          foreach ( $osC_Language->getAll() as $l ) {
            echo "{";
              echo "xtype: 'textfield',"; 
              if($i == 1)
                echo "fieldLabel: '" . $osC_Language->get('field_polls_title') . "',"; 
              else
                echo 'fieldLabel: "&nbsp;",';  
              echo 'name: "question_title[' . $l['id'] . ']",';
              echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;', "; 
              echo "allowBlank: false";
            echo "},";
            $i++;
          }
        ?>
        
        this.cboPollsType,
        {
          layout: 'column',
          border: false,
          items: [
            { 
              width: 220,
              layout: 'form',
              labelSeparator: ' ',
              border: false,
              items:[
                {fieldLabel: '<?php echo $osC_Language->get('field_polls_status'); ?>', boxLabel: '<?php echo $osC_Language->get('polls_status_enabled'); ?>' , name: 'polls_status', xtype:'radio', inputValue: '1', checked: true}
              ]
            },
            { 
              layout: 'form',
              border: false,
              items:[
                { hideLabel: true, boxLabel: '<?php echo $osC_Language->get('polls_status_disabled'); ?>' , name: 'polls_status', xtype:'radio', inputValue: '0'}
              ]
            }
          ]  
        }
      ]
    });
    
    return this.frmPolls;
  },

  submitForm : function() {
    this.frmPolls.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
         this.fireEvent('saveSuccess', action.result.feedback);
         this.close();  
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });   
  }
});