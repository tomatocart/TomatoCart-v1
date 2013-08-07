<?php
/*
  $Id: polls_answers_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.polls.PollsAnswersDialog = function(config) {
  config = config || {}; 
  
  config.id = 'polls-answers-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_polls_answer'); ?>';
  config.modal = true;
  config.width = 500;
  config.iconCls = 'icon-polls-win';
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
      handler: function() {
        this.close();
      },
      scope:this
    }
  ];

  this.addEvents({'saveSuccess' : true});  
  
  Toc.polls.PollsAnswersDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.polls.PollsAnswersDialog, Ext.Window, {  
  show: function (pollsId, pollsAnswersId) {
    var pollsId = pollsId || null;
    var pollsAnswersId  = pollsAnswersId || null;

    this.frmPollsAnswers.form.reset();
    this.frmPollsAnswers.form.baseParams['polls_id'] = pollsId;
    this.frmPollsAnswers.form.baseParams['polls_answers_id'] = pollsAnswersId;
   
    if (pollsAnswersId > 0) {
      this.frmPollsAnswers.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'polls',
          action: 'load_poll_answer'
        },
        success: function(form, action) {
          Toc.polls.PollsAnswersDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData)
        },
        scope: this       
      });
    } else {   
      Toc.polls.PollsAnswersDialog.superclass.show.call(this);
    }
  },
    
  buildForm: function() {
    this.frmPollsAnswers = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        'module' : 'polls',
        'action' : 'save_poll_answer'
      }, 
      defaults: {
        anchor: '98%'
      },
      style: 'padding: 8px',
      border: false,
      layoutConfig: {
        labelSeparator: ''
      },
      labelWidth: 150,
      items: [
      <?php
        $i = 1;
          foreach ( $osC_Language->getAll() as $l ) {
            echo "{";
              echo "xtype: 'textfield',"; 
              if($i == 1)
                echo "fieldLabel: '" . $osC_Language->get('field_polls_answers_title') . "',"; 
              else
                echo 'fieldLabel: "&nbsp;",';  
              echo 'name: "answers_title[' . $l['id'] . ']",';
              echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;', "; 
              echo "allowBlank: false";
            echo "},";
            $i++;
          }
        ?>
        {xtype: 'textfield', fieldLabel: '<?php echo $osC_Language->get('field_votes_count'); ?>', name: 'votes_count', readOnly: true, value: 0},
        {xtype: 'numberfield', fieldLabel: '<?php echo $osC_Language->get('table_heading_sort_order'); ?>', name: 'sort_order', value: 0, allowBlank: false}
      ]
    });
    
    return this.frmPollsAnswers;
  },

  submitForm : function() {
    this.frmPollsAnswers.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success:function(form, action) {
        this.fireEvent('saveSuccess', action.result.feedback); 
        this.close();
      },
      failure: function(form, action) {
        if (action.failureType != 'client') {         
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);      
        }         
      },
      scope: this
    });   
  }
});