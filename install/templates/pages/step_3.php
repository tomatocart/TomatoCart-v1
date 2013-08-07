<?php
/*
  $Id: step_3.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $db_table_types = array(array('id' => 'mysql', 'text' => 'MySQL - MyISAM (Default)'),
                          array('id' => 'mysql_innodb', 'text' => 'MySQL - InnoDB (Transaction-Safe)'),
                          array('id' => 'mysqli', 'text' => 'MySQLi (PHP 5 / MySQL 4.1)'));
?>

<script language="javascript" type="text/javascript" src="../includes/javascript/xmlhttp/xmlhttp.js"></script>
<script language="javascript" type="text/javascript">
<!--

  var dbServer;
  var dbUsername;
  var dbPassword;
  var dbName;
  var dbClass;
  var dbPrefix;

  var formSubmited = false;

  function handleHttpResponse_DoImport() {
    if (http.readyState == 4) {
      if (http.status == 200) {
        var result = /\[\[([^|]*?)(?:\|([^|]*?)){0,1}\]\]/.exec(http.responseText);
        result.shift();

        if (result[0] == '1') {
          document.getElementById('mBoxContents').innerHTML = '<p style="width:180px;"><img src="images/success.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_database_imported'); ?></p>';

          setTimeout("document.getElementById('installForm').submit();", 2000);
        } else {
          document.getElementById('mBoxContents').innerHTML = '<p style="width:180px;"><img src="images/failed.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_database_import_error'); ?></p>'.replace('%s', result[1]);
        }
      }

      formSubmited = false;
    }
  }

  function handleHttpResponse() {
    if (http.readyState == 4) {
      if (http.status == 200) {
        var result = /\[\[([^|]*?)(?:\|([^|]*?)){0,1}\]\]/.exec(http.responseText);
        result.shift();

        if (result[0] == '1') {
          document.getElementById('mBoxContents').innerHTML = '<p style="width:180px;"><img src="images/progress.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_database_importing'); ?></p>';

          loadXMLDoc("rpc.php?action=dbImport&server=" + urlEncode(dbServer) + "&username=" + urlEncode(dbUsername) + "&password=" + urlEncode(dbPassword) + "&name=" + urlEncode(dbName) + "&class=" + urlEncode(dbClass) + "&import=0&prefix=" + urlEncode(dbPrefix), handleHttpResponse_DoImport);
        } else {
          document.getElementById('mBoxContents').innerHTML = '<p style="width:180px;"><img src="images/failed.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_database_connection_error'); ?></p>'.replace('%s', result[1]);
          formSubmited = false;
        }
      } else {
        formSubmited = false;
      }
    }
  }

  function prepareDB() {
    if (formSubmited == true) {
      return false;
    }

    formSubmited = true;

    showDiv(document.getElementById('mBox'));

    document.getElementById('mBoxContents').innerHTML = '<p style="width:180px;"><img src="images/progress.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_database_connection_test'); ?></p>';

    dbServer = document.getElementById("DB_SERVER").value;
    dbUsername = document.getElementById("DB_SERVER_USERNAME").value;
    dbPassword = document.getElementById("DB_SERVER_PASSWORD").value;
    dbName = document.getElementById("DB_DATABASE").value;
    dbClass = document.getElementById("DB_DATABASE_CLASS").value;
    dbPrefix = document.getElementById("DB_TABLE_PREFIX").value;

    loadXMLDoc("rpc.php?action=dbCheck&server=" + urlEncode(dbServer) + "&username=" + urlEncode(dbUsername) + "&password=" + urlEncode(dbPassword) + "&name=" + urlEncode(dbName) + "&class=" + urlEncode(dbClass), handleHttpResponse);
  }

//-->
</script>

<form name="install" id="installForm" action="index.php?step=4" method="post" onsubmit="prepareDB(); return false;">

  <div class="contentBlock">  
    <div class="contentPane">
      <h1><?php echo $osC_Language->get('page_title_database_server_setup'); ?></h1>
      
      <p><?php echo $osC_Language->get('text_database_server_setup'); ?></p>
    
	    <table border="0" width="99%" cellspacing="0" cellpadding="0" class="inputForm">
	      <tr>
	        <td class="inputField">
	          <?php echo $osC_Language->get('param_database_server') . '<br />' . osc_draw_input_field('DB_SERVER', null, 'class="text"'); ?>
	          <br />
	          <span><?php echo $osC_Language->get('param_database_server_description'); ?></span>
	        </td>
	      </tr>
	      <tr>
	        <td class="inputField">
	          <?php echo $osC_Language->get('param_database_username') . '<br />' . osc_draw_input_field('DB_SERVER_USERNAME', null, 'class="text"'); ?>
	          <br />
	          <span><?php echo $osC_Language->get('param_database_username_description'); ?></span>
	        </td>
	      </tr>
	      <tr>
	        <td class="inputField">
	          <?php echo $osC_Language->get('param_database_password') . '<br />' . osc_draw_password_field('DB_SERVER_PASSWORD', 'class="text"'); ?>
	          <br />
	          <span><?php echo $osC_Language->get('param_database_password_description'); ?></span>        
	        </td>
	      </tr>
	      <tr>
	        <td class="inputField">
	          <?php echo $osC_Language->get('param_database_name') . '<br />' . osc_draw_input_field('DB_DATABASE', null, 'class="text"'); ?>
	          <br />
	          <span><?php echo $osC_Language->get('param_database_name_description'); ?></span>   
	        </td>         
	      </tr>
	      <tr>
	        <td class="inputField">
	          <?php echo $osC_Language->get('param_database_type') . '<br />' . osc_draw_pull_down_menu('DB_DATABASE_CLASS', $db_table_types); ?>
	          <br />
	          <span><?php echo $osC_Language->get('param_database_type_description'); ?></span>           
	        </td>
	      </tr>
	      <tr>
	        <td class="inputField">
	          <?php echo $osC_Language->get('param_database_prefix') . '<br />' . osc_draw_input_field('DB_TABLE_PREFIX', 'toc_', 'class="text"'); ?>
	          <br />
	          <span><?php echo $osC_Language->get('param_database_prefix_description'); ?></span>             
	        </td>
	      </tr>
	    </table>
    </div>
  </div>
	
	<p align="right">
    <?php echo '<a href="index.php"><img src="templates/' . $template . '/languages/' . $osC_Language->getCode() . '/images/buttons/cancel.gif" border="0" alt="' . $osC_Language->get('image_button_cancel') . '" /></a>'; ?>
    &nbsp;&nbsp;
    <?php echo '<input type="image" src="templates/' . $template . '/languages/' . $osC_Language->getCode() . '/images/buttons/continue.gif" border="0" alt="' . $osC_Language->get('image_button_continue') . '" id="inputButton" />'; ?>
  </p>    
</form>