<?php
/*
  $Id: step_1.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
<script type="text/javascript">
<!--
  function checkLicense() {
    if(document.getElementById('license').checked){
      return true;
    } else {
      alert("<?php echo $osC_Language->get('error_agree_to_license'); ?>");
      return false;
    }
  }
//-->
</script>

<div class="contentBlock">
  <ul style="list-style-type: none; padding: 5px; margin: 0px; display: inline; float: right;">
    <li style="font-weight: bold; display: inline;"><?php echo $osC_Language->get('title_language'); ?></li>
<?php
  foreach ($osC_Language->getAll() as $available_language) {
?>
    <li style="display: inline;"><?php echo '<a href="index.php?language=' . $available_language['code'] . '">' . $osC_Language->showImage($available_language['code']) . '</a>'; ?></li>
<?php
  }
?>
  </ul>

  <h1><?php echo $osC_Language->get('page_title_welcome'); ?></h1>

  <p><?php echo $osC_Language->get('text_welcome'); ?></p>
</div>  

<div class="contentBlock">
  <h2><?php echo $osC_Language->get('box_title_license'); ?></h2>

  <div class="license">
    <?php include('license.txt'); ?>
  </div>
  
  <p align="right">
    <span><?php echo $osC_Language->get('label_agree_to_the_license'); ?></span>&nbsp;<?php echo osc_draw_selection_field('license', 'checkbox', null); ?>
  </p>
</div>

<p align="right">
  <?php echo '<a href="index.php?step=2" onclick="javascript: return checkLicense();"><img src="templates/' . $template . '/languages/' . $osC_Language->getCode() . '/images/buttons/continue.gif" border="0" alt="' . $osC_Language->get('image_button_install') . '" /></a>'; ?>
</p>