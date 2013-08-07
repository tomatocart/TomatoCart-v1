<?php
/*
  $Id: step_5.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<script language="javascript" type="text/javascript" src="../includes/javascript/xmlhttp/xmlhttp.js"></script>
<script language="javascript" type="text/javascript">
<!--

  var dbServer = "<?php echo $_POST['DB_SERVER']; ?>";
  var dbUsername = "<?php echo $_POST['DB_SERVER_USERNAME']; ?>";
  var dbPassword = "<?php echo $_POST['DB_SERVER_PASSWORD']; ?>";
  var dbName = "<?php echo $_POST['DB_DATABASE']; ?>";
  var dbClass = "<?php echo $_POST['DB_DATABASE_CLASS']; ?>";
  var dbPrefix = "<?php echo $_POST['DB_TABLE_PREFIX']; ?>";

  var formSubmited = false;

  function handleHttpResponse() {
    if (http.readyState == 4) {
      if (http.status == 200) {
        var result = /\[\[([^|]*?)(?:\|([^|]*?)){0,1}\]\]/.exec(http.responseText);
        result.shift();

        if (result[0] == '1') {
          document.getElementById('mBoxContents').innerHTML = '<p><img src="images/success.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_database_sample_data_imported'); ?></p>';

          setTimeout("document.getElementById('installForm').submit();", 2000);
        } else {
          document.getElementById('mBoxContents').innerHTML = '<p><img src="images/failed.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_database_sample_data_import_error'); ?></p>'.replace('%s', result[1]);
        }
      }

      formSubmited = false;
    }
  }

  function prepareDB() { 
    var error = false;
    var errorMassage = '';
    if(document.getElementById("CFG_ADMINISTRATOR_USERNAME").value.length == 0) {

      showDiv(document.getElementById('mBox'));
      errorMassage = '<?php echo $osC_Language->get("rpc_store_setting_username_error"); ?>';

      setTimeout(" ", 1000);
      document.getElementById('mBoxContents').innerHTML = '<p style="width:180px;"><img src="images/failed.gif" align="right" hspace="5" vspace="5" border="0" />'+errorMassage+'</p>';
      error = true;
    }
    
    if(error == false && document.getElementById("CFG_ADMINISTRATOR_PASSWORD").value.length == 0) {
      showDiv(document.getElementById('mBox'));
      errorMassage = '<?php echo $osC_Language->get("rpc_store_setting_password_error"); ?>';

      setTimeout(" ", 1000);
      document.getElementById('mBoxContents').innerHTML = '<p style="width:180px;"><img src="images/failed.gif" align="right" hspace="5" vspace="5" border="0" />'+errorMassage+'</p>';
      error = true;
    }
    if(error == false && document.getElementById("CFG_ADMINISTRATOR_PASSWORD").value != document.getElementById("CFG_CONFIRM_PASSWORD").value) {
      showDiv(document.getElementById('mBox'));
      errorMassage = '<?php echo $osC_Language->get("rpc_store_setting_confirm_error"); ?>';

      setTimeout(" ", 1000);
      document.getElementById('mBoxContents').innerHTML = '<p style="width:180px;"><img src="images/failed.gif" align="right" hspace="5" vspace="5" border="0" />'+errorMassage+'</p>';
      error = true;
    }
    
    if(error == false) {
      var reg = /^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
      if( !reg.test(document.getElementById('CFG_STORE_OWNER_EMAIL_ADDRESS').value) && document.getElementById("CFG_STORE_OWNER_EMAIL_ADDRESS").value.length > 0) {
	      showDiv(document.getElementById('mBox'));
	      errorMassage = '<?php echo $osC_Language->get("rpc_store_setting_email_error"); ?>';
	
	      setTimeout(" ", 1000);
	      document.getElementById('mBoxContents').innerHTML = '<p style="width:180px;"><img src="images/failed.gif" align="right" hspace="5" vspace="5" border="0" />'+errorMassage+'</p>';
	      error = true;
      }
    }   

    if(error == false) {
	    if (document.getElementById("DB_INSERT_SAMPLE_DATA").checked) {
	      if (formSubmited == true) {
	        return false;
	      }
	
	      formSubmited = true;
	
	      showDiv(document.getElementById('mBox'));
	
	      document.getElementById('mBoxContents').innerHTML = '<p><img src="images/progress.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_database_sample_data_importing'); ?></p>';
	
	      loadXMLDoc("rpc.php?action=dbImportSample&server=" + urlEncode(dbServer) + "&username=" + urlEncode(dbUsername) + "&password=" + urlEncode(dbPassword) + "&name=" + urlEncode(dbName) + "&class=" + urlEncode(dbClass) + "&prefix=" + urlEncode(dbPrefix), handleHttpResponse);
	    } else {
	      document.getElementById('installForm').submit();
	    }
    } else {
      return false;
    }
  }

//-->
</script>

<form name="install" id="installForm" action="index.php?step=6" method="post" onsubmit="prepareDB(); return false;">

  <div class="contentBlock">
    <div class="contentPane">
      <h1><?php echo $osC_Language->get('page_title_online_store_settings'); ?></h1>
      
      <p><?php echo $osC_Language->get('text_online_store_settings'); ?></p>
      
      <table border="0" width="99%" cellspacing="0" cellpadding="5" class="inputForm">
        <tr>
          <td class="inputField">
            <?php echo $osC_Language->get('param_store_name') . '<br />' . osc_draw_input_field('CFG_STORE_NAME', null, 'class="text"'); ?>
  	        <br>
  	        <span><?php echo $osC_Language->get('param_store_name_description'); ?></span>
          </td>
        </tr>
        <tr>
          <td class="inputField">
            <?php echo $osC_Language->get('param_store_owner_name') . '<br />' . osc_draw_input_field('CFG_STORE_OWNER_NAME', null, 'class="text"'); ?>
            <br>
            <span><?php echo $osC_Language->get('param_store_owner_name_description'); ?></span>        
          </td>
        </tr>
        <tr>
          <td class="inputField">
            <?php echo $osC_Language->get('param_store_owner_email_address') . '<br />' . osc_draw_input_field('CFG_STORE_OWNER_EMAIL_ADDRESS', null, 'class="text"'); ?>
            <br>
            <span><?php echo $osC_Language->get('param_store_owner_email_address_description'); ?></span>         
          </td>
        </tr>
        <tr>
          <td class="inputField">
            <?php echo $osC_Language->get('param_administrator_username') . '<br />' . osc_draw_input_field('CFG_ADMINISTRATOR_USERNAME', null, 'class="text"'); ?>
            <br>
            <span><?php echo $osC_Language->get('param_administrator_username_description'); ?></span>        
          </td>
        </tr>
        <tr>
          <td class="inputField">
            <?php echo $osC_Language->get('param_administrator_password') . '<br />' . osc_draw_password_field('CFG_ADMINISTRATOR_PASSWORD', 'class="text"'); ?>
            <br>
            <span><?php echo $osC_Language->get('param_administrator_password_description'); ?></span>            
          </td>
        </tr>
        <tr>
          <td class="inputField">
            <?php echo $osC_Language->get('param_confirm_password') . '<br />' . osc_draw_password_field('CFG_CONFIRM_PASSWORD', 'class="text"'); ?>
            <br>
            <span><?php echo $osC_Language->get('param_administrator_password_description'); ?></span>            
          </td>
        </tr>      
        <tr>
          <td class="inputField">
            <?php echo osc_draw_checkbox_field('DB_INSERT_SAMPLE_DATA', 'true', true) . '&nbsp;' . $osC_Language->get('param_database_import_sample_data'); ?>
            <br>
            <span><?php echo $osC_Language->get('param_database_import_sample_data_description'); ?></span>          
          </td>
        </tr>
      </table>
    </div>
  </div>

  <p align="right">
    <?php echo '<a href="index.php"><img src="templates/' . $template . '/languages/' . $osC_Language->getCode() . '/images/buttons/cancel.gif" border="0" alt="' . $osC_Language->get('image_button_cancel') . '" /></a>'; ?>
    &nbsp;&nbsp;
    <?php echo '<input type="image" src="templates/' . $template . '/languages/' . $osC_Language->getCode() . '/images/buttons/continue.gif" border="0" alt="' . $osC_Language->get('image_button_continue') . '" />'; ?>
  </p>

  <?php
    foreach ($_POST as $key => $value) {
      if (($key != 'x') && ($key != 'y')) {
        if (is_array($value)) {
          for ($i=0, $n=sizeof($value); $i<$n; $i++) {
            echo osc_draw_hidden_field($key . '[]', $value[$i]);
          }
        } else {
          echo osc_draw_hidden_field($key, $value);
        }
      }
    }
  ?>

</form>