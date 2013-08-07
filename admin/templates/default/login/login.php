<?php
/*
  $Id: login.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $osC_Language->loadIniFile('login.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $osC_Language->getTextDirection();?>" xml:lang="<?php echo $osC_Language->getCode();?>" lang="<?php echo $osC_Language->getCode();?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="PRAGMA" content="NO-CACHE">
<meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
<meta http-equiv="EXPIRES" content="-1">
<title><?php echo $osC_Language->get('administration_title'); ?></title>

<!-- EXT JS LIBRARY -->
<link rel="stylesheet" type="text/css" href="external/extjs/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="templates/default/login/login.css" />
</head>

<body scroll="no">
  <div id="x-loading-mask" style="width:100%; height:100%; background:#000000; position:absolute; z-index:20000; left:0; top:0;">&#160;</div>
  <div id="x-loading-panel" style="position:absolute;left:40%;top:40%;border:1px solid #9c9f9d;padding:2px;background:#d1d8db;width:300px;text-align:center;z-index:20001;">
    <div class="x-loading-panel-mask-indicator" style="border:1px solid #c1d1d6;color:#666;background:white;padding:10px;margin:0;padding-left: 20px;height:110px;text-align:left;">
      <img class="x-loading-panel-logo" style="display:block;margin-bottom:15px;" src="images/tomatocart.jpg" />
      <img src="images/loading.gif" style="width:16px;height:16px;vertical-align:middle" />&#160;
      <span id="load-status"><?php echo $osC_Language->get('init_system'); ?></span>
      <div style="font-size:10px; font-weight:normal; margin-top:15px;">Copyright &copy; 2009 Wuxi Elootec Technology Co., Ltd</div>
    </div>
  </div> 
  
  <div id="x-login-panel">
    <img src="templates/default/desktop/images/default/s.gif" class="login-logo abs-position" />
    
    <div class="login-features abs-position">
      <p>The professional and innovative open source online shopping cart solution</p>
      <p align="justify">Equipped with modern technology AJAX and Rich Internet Applications (RIA) Framework ExtJS, TomatoCart offer significant usability improvements and make interacting with the web interfaces faster and more efficient.</p>
    </div>
    
    <img src="templates/default/desktop/images/default/s.gif" class="login-screenshot abs-position" />
    
    <span class="login-supported abs-position">
      <b>Supported Browsers</b><br />
      <a href="http://www.mozilla.org/download.html" target="_blank">Firefox 2+</a><br />
      <a href="http://www.microsoft.com/windows/downloads/ie/getitnow.mspx" target="_blank">Internet Explorer 7+</a><br />
      <a href="http://www.opera.com/download/" target="_blank">Opera 9+</a>
    </span>
  
    <div id="x-login-form" class="x-login-form abs-position"><a id='forget-password' onclick="javascript:forgetPassword();"><?php echo $osC_Language->get("label_forget_password"); ?></a></div>
  </div>

  <script src="external/extjs/adapter/ext/ext-base.js"></script>
  <script src="external/extjs/ext-all.js"></script> 
  <script type="text/javascript">
  Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = 'templates/default/desktop/images/default/s.gif';
    Ext.EventManager.onWindowResize(centerPanel);
    
    var loginPanel = Ext.get("x-login-panel");
    
    centerPanel();
    
    Ext.namespace("Toc");
    Toc.Languages = [];
    <?php 
      foreach ($osC_Language->getAll() as $l) {
        echo 'Toc.Languages.push(["' . $l['code'] . '", "' . $l['name'] . '"]);';
      }
    ?>
    var cboLanguage = new Ext.form.ComboBox({
      store:  new Ext.data.SimpleStore({
        fields: ['id', 'text'],
        data : Toc.Languages
      }),
      fieldLabel: '<?php echo $osC_Language->get("field_language"); ?>',
      name: 'language',
      hiddenName: 'language',
      displayField:'text',
      valueField: 'id',     
      mode:'local',
      triggerAction:'all',      
      forceSelection: true,
      editable: false,
      value: '<?php echo $osC_Language->getCode(); ?>'
    });
    
    cboLanguage.on(
      'select',
      function(){
        document.location = '<?php echo osc_href_link_admin(FILENAME_DEFAULT); ?>?admin_language=' + cboLanguage.getValue();
      },
      this
    );
    
    var frmlogin = new Ext.form.FormPanel({
      url: '<?php echo osc_href_link_admin(FILENAME_JSON); ?>',
      baseParams: {
        module: 'login',
        action: 'login'
      },
      labelWidth: 100,
      width: 335,
      autoHeight: true,
      border: false,
      applyTo: 'x-login-form',
      bodyStyle: 'background: transparent',
      defaults: {anchor: '100%'},
      labelSeparator: ' ',
      items: [
        cboLanguage,
        {xtype: 'textfield', name: 'user_name', fieldLabel: '<?php echo $osC_Language->get("field_username"); ?>', allowBlank:false},
        {xtype: 'textfield', name: 'user_password', fieldLabel: '<?php echo $osC_Language->get("field_password"); ?>', inputType: 'password', allowBlank:false}
      ],
      keys:[{ 
        key: Ext.EventObject.ENTER,  
        fn: login,  
        scope: this  
      }],
      buttonAlign: 'right',
      buttons: [{
        text: '<?php echo $osC_Language->get("button_login"); ?>',
        handler: login, 
        scope: this
      }],
      listeners : {
        'render' : function() {
          this.findByType('textfield')[1].focus(true, true);
        }
      }
    });
    
    function centerPanel(){
      var xy = loginPanel.getAlignToXY(document, 'c-c');
      positionPanel(loginPanel, xy[0], xy[1]);
    }
    
    function login() {
      frmlogin.form.submit({
        success: function (form, action) {
          window.location = '<?php echo osc_href_link_admin(FILENAME_DEFAULT); ?>?admin_language=' + cboLanguage.getValue();
        },
        failure: function (form, action) {
          if (action.failureType != 'client') {
            alert(action.result.feedback);
          }
        },
        scope: this
      });
    }
    
    function positionPanel(el, x, y){
      if(x && typeof x[1] == 'number') {
        y = x[1];
        x = x[0];
      }
      
      el.pageX = x;
      el.pageY = y;
      
      if(x === undefined || y === undefined){ // cannot translate undefined points
        return;
      }
      
      if(y < 0) { 
        y = 10;
      }
      
      var p = el.translatePoints(x, y);
      el.setLocation(p.left, p.top);
      
      return el;
    }
    
    function removeLoadMask() {
      var loading = Ext.get('x-loading-panel');
      var mask = Ext.get('x-loading-mask');
      loading.hide();
      mask.hide();
    }
    removeLoadMask(); 
  });  
  
  function forgetPassword() {
    var email = prompt('<?php echo $osC_Language->get("ms_forget_password_text"); ?>');
      
      if (!Ext.isEmpty(email)) {
        Ext.get('x-login-panel').mask('<?php echo $osC_Language->get("ms_sending_email"); ?>'); 
        
        Ext.Ajax.request({
          url: '<?php echo osc_href_link_admin(FILENAME_JSON); ?>',
          params: {
            module: 'login',
            action: 'get_password',
            email_address: email
          },
          callback: function(options, success, response) {
            Ext.get('x-login-panel').unmask();
          
            result = Ext.decode(response.responseText);
            alert(result.feedback);
          },
          scope: this
        }); 
      }
  }
  </script>
</body>
</html>