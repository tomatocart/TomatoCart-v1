<?php
/*
  $Id: step_4.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $www_location = 'http://' . $_SERVER['HTTP_HOST'];

  if (isset($_SERVER['REQUEST_URI']) && (empty($_SERVER['REQUEST_URI']) === false)) {
    $www_location .= $_SERVER['REQUEST_URI'];
  } else {
    $www_location .= $_SERVER['SCRIPT_FILENAME'];
  }

  $www_location = substr($www_location, 0, strpos($www_location, 'install'));

  $dir_fs_www_root = osc_realpath(dirname(__FILE__) . '/../../../') . '/';
?>

<script language="javascript" type="text/javascript" src="../includes/javascript/xmlhttp/xmlhttp.js"></script>
<script language="javascript" type="text/javascript" src="../includes/javascript/xmlhttp/autocomplete.js"></script>
<script language="javascript" type="text/javascript">
<!--

  String.prototype.wordWrap = function(m, b, c){
    var i, j, s, r = this.split("\n");
    if(m > 0) for(i in r){
        for(s = r[i], r[i] = ""; s.length > m;
            j = c ? m : (j = s.substr(0, m).match(/\S*$/)).input.length - j[0].length
            || m,
            r[i] += s.substr(0, j) + ((s = s.substr(j)).length ? b : "")
        );
        r[i] += s;
    }
    return r.join("\n");
  };

  var cfgWork;
  var formSubmited = false;

  function handleHttpResponse() {
    if (http.readyState == 4) {
      if (http.status == 200) {
        var result = /\[\[([^|]*?)(?:\|([^|]*?)){0,1}\]\]/.exec(http.responseText);
        result.shift();

        if (result[0] == '1') {
          document.getElementById('mBoxContents').innerHTML = '<p><img src="images/success.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_work_directory_configured'); ?></p>';

          setTimeout("document.getElementById('installForm').submit();", 2000);
        } else if (result[0] == '0') {
          document.getElementById('mBoxContents').innerHTML = '<p><img src="images/failed.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_work_directory_error_not_writeable'); ?></p>'.replace('%s', result[1].wordWrap(30, '<br />', true));
        } else {
          document.getElementById('mBoxContents').innerHTML = '<p><img src="images/failed.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_work_directory_error_non_existent'); ?></p>'.replace('%s', result[1].wordWrap(30, '<br />', true));
        }
      }

      formSubmited = false;
    }
  }

  function prepareWork() {
    if (formSubmited == true) {
      return false;
    }

    if (returnUsed == true) {
      returnUsed = false;

      return false;
    }

    formSubmited = true;

    showDiv(document.getElementById('mBox'));

    document.getElementById('mBoxContents').innerHTML = '<p><img src="images/progress.gif" align="right" hspace="5" vspace="5" border="0" /><?php echo $osC_Language->get('rpc_work_directory_test'); ?></p>';

    cfgWork = document.getElementById("HTTP_WORK_DIRECTORY").value;

    loadXMLDoc("rpc.php?action=checkWorkDir&dir=" + urlEncode(cfgWork), handleHttpResponse);
  }

//-->
</script>

<form name="install" id="installForm" action="index.php?step=5" method="post" onsubmit="prepareWork(); return false;">

  <div class="contentBlock">
    <div class="contentPane">
      <h1><?php echo $osC_Language->get('page_title_web_server'); ?></h1>
      
      <p><?php echo $osC_Language->get('text_web_server'); ?></p>
  
      <table border="0" width="99%" cellspacing="0" cellpadding="5" class="inputForm">
        <tr>
          <td class="inputField">
            <?php echo $osC_Language->get('param_web_address') . '<br />' . osc_draw_input_field('HTTP_WWW_ADDRESS', $www_location, 'class="text"'); ?>
            <br>
            <span><?php echo $osC_Language->get('param_web_address_description'); ?></span>
         </td>
        </tr>
        <tr>
          <td class="inputField">
            <?php echo $osC_Language->get('param_web_root_directory') . '<br />' . osc_draw_input_field('DIR_FS_DOCUMENT_ROOT', $dir_fs_www_root, 'class="text"'); ?>
            <br>
            <span><?php echo $osC_Language->get('param_web_root_directory_description'); ?></span>       
          </td>
        </tr>
        <tr>
          <td class="inputField">
            <?php echo $osC_Language->get('param_web_work_directory') . '<br /><span style="white-space: nowrap;">' . osc_draw_input_field('HTTP_WORK_DIRECTORY', $dir_fs_www_root . 'includes/work', 'class="text"'); ?></span><div class="autoComplete" id="divAutoComplete"></div>
            <br>
            <span><?php echo $osC_Language->get('param_web_work_directory_description'); ?></span>        
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

<script language="javascript" type="text/javascript">
<!--
  new autoComplete(document.getElementById('HTTP_WORK_DIRECTORY'), 'divAutoComplete');
//-->
</script>
