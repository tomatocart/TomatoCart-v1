<?php
/*
  $Id: desktop.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/ext_config.php');
  
  require('includes/classes/json.php');
  $toC_Json = new toC_Json();
  
  if(!isset($_SESSION['admin'])) {
    require('templates/default/login/login.php');
    exit;
  }
  
  //enable the IE compatible mode
  if (stripos($_SERVER['HTTP_USER_AGENT'], 'msie 9') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'msie 10') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'msie') !== false) {
  	header('X-UA-Compatible: IE=7');
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html dir="<?php echo $osC_Language->getTextDirection();?>" xml:lang="<?php echo $osC_Language->getCode();?>" lang="<?php echo $osC_Language->getCode();?>">
<head>
<title><?php echo $osC_Language->get('administration_title'); ?></title>

<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="-1" />

<link rel="stylesheet" type="text/css" href="external/extjs/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/colorpicker/colorpicker-min.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/form/fileuploadfield-min.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/form/multiselect-min.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/form/AndrieSelect.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/form/statictextfield-min.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/grid/rowactions-min.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/pagetoolbar/pagetoolbar-min.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/portal/portal-min.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/tree/checktreepanel-min.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/uploadpanel/uploadpanel-min.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/wizard/ext-ux-wiz-min.css" />

<link rel="stylesheet" type="text/css" href="templates/default/desktop/css/all.css" />
<link rel="stylesheet" type="text/css" href="templates/default/extensions/portal/portal.css" />

<!--[if gte IE 8.0]>
  <link rel="stylesheet" type="text/css" href="templates/default/desktop/css/ie8.css" />
<![endif]-->

<style type="text/css">
<?php 
  foreach ($osC_Language->getAll() as $l) {
    echo ".icon-" . $l['country_iso'] . "-win {background-image: url(../images/worldflags/" . $l['country_iso'] . ".png) !important;}";
  }
?>
</style>
</head>

<body scroll="no">
  <div id="x-loading-mask" style="width:100%; height:100%; background:#000000; position:absolute; z-index:20000; left:0; top:0;">&#160;</div>
  <div id="x-loading-panel" style="position:absolute;left:40%;top:40%;border:1px solid #9c9f9d;padding:2px;background:#d1d8db;width:300px;text-align:center;z-index:20001;">
    <div class="x-loading-panel-mask-indicator" style="border:1px solid #c1d1d6;color:#666;background:white;padding:10px;margin:0;padding-left: 20px;height:110px;text-align:left;">
      <img class="x-loading-panel-logo" style="display:block;margin-bottom:15px;" src="images/tomatocart.jpg" alt="TomatoCart -- Best Open Source Shopping Cart Software" />
      <img src="images/loading.gif" style="width:16px;height:16px;vertical-align:middle" alt="loading..." />&#160;
      <span id="load-status"><?php echo $osC_Language->get('init_system'); ?></span>
      <div style="font-size:10px; font-weight:normal; margin-top:15px;">Copyright &copy; 2009 Wuxi Elootec Technology Co., Ltd</div>
    </div>
  </div>

  <div id="x-desktop">
    <a id="tomatocart-logo" href="http://www.tomatocart.com" target="_blank" style="margin:5px; float:right;"><img src="images/power_by_button.png" alt="TomatoCart -- Best Open Source Shopping Cart Software" /></a>
  </div>
  
  <div id="ux-sidebar"></div>
  <div id="ux-sidebar-background"></div>
  
  <div id="ux-taskbar">
    <div id="ux-taskbar-start"></div>
    <div id="ux-taskbar-panel-wrap">
      <div id="ux-quickstart-panel"></div>
      <div id="ux-taskbuttons-panel"></div>
    </div>
    <div id="ux-systemtray-panel"></div>
    <div class="x-clear"></div>
  </div>
  
  <!-- EXT JS LIBRARY -->
  <script type="text/javascript" src="external/extjs/adapter/ext/ext-base.js"></script>
  <script type="text/javascript" src="external/extjs/ext-all.js"></script>
  
  <script type="text/javascript" src="external/devAnalogClock/swfobject.js"></script>

<?php if (USE_WYSIWYG_TINYMCE_EDITOR == '1') { ?>  

  <script type="text/javascript" src="external/tinymce/miframe-min.js"></script>
  <script type="text/javascript" src="external/tinymce/tiny_mce/tiny_mce.js"></script>
  <script type="text/javascript" src="external/tinymce/Ext.ux.TinyMCE.min.js"></script>

<?php } ?>
  
  <script type="text/javascript" src="templates/default/locale/ext-lang-<?php echo $osC_Language->getCode(); ?>-min.js"></script>
    
  <script type="text/javascript">
    Ext.namespace("Toc");
    
    Toc.CONF = {
      CONN_URL: '<?php echo osc_href_link_admin(FILENAME_JSON); ?>',
      LOAD_URL: '<?php echo osc_href_link_admin(FILENAME_LOAD); ?>',
      PDF_URL: '<?php echo osc_href_link_admin(FILENAME_PDF); ?>',
    
      GRID_PAGE_SIZE : <?php echo MAX_DISPLAY_SEARCH_RESULTS; ?>,
      GRID_STEPS : <?php echo EXT_GRID_STEPS; ?>,
      JSON_READER_ROOT: '<?php echo EXT_JSON_READER_ROOT; ?>',
      JSON_READER_TOTAL_PROPERTY: '<?php echo EXT_JSON_READER_TOTAL; ?>'
    };
    
    Toc.Languages = [];
    <?php 
      foreach ($osC_Language->getAll() as $l) {
        echo 'Toc.Languages.push(' . $toC_Json->encode($l) . ');';
      }
      
      $desktop_translations = $osC_Language->parseIniFile('modules/ext_desktop.php');
    ?>

    var TocLanguage = {};
    TocLanguage = <?php echo $toC_Json->encode($desktop_translations); ?>;
  </script>
  
  <script type="text/javascript" src="templates/default/desktop/core/all.js"></script>
  <script type="text/javascript" src="templates/default/desktop/setting/all.js"></script>
  <script type="text/javascript" src="templates/default/extensions/extension-all.js"></script>
  <script type="text/javascript" src="<?php echo osc_href_link_admin('tocdesktop.php'); ?>"></script>      
  <script type="text/javascript" src="templates/default/extensions/CategoriesComboBox.js"></script>
  <script type="text/javascript" src="templates/default/extensions/form/AndrieSelect.js"></script>
</body>
</html>