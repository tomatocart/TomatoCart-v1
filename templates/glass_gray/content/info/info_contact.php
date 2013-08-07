<?php
/*
  $Id: info_contact.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

 $departments =array();
 
 $Qlisting = toC_Departments::getListing();
 while($Qlisting->next()) {
   $departments[] = array('id' => $Qlisting->value('departments_email_address'),
                         'text' => $Qlisting->value('departments_title'));
   
   $departments_description[$Qlisting->value('departments_email_address')] = $Qlisting->value('departments_description');
 }
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  if ($messageStack->size('contact') > 0) {
    echo $messageStack->output('contact');
  }

  if (isset($_GET['contact']) && ($_GET['contact'] == 'success')) {
?>

<p><?php echo $osC_Language->get('contact_email_sent_successfully'); ?></p>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'contact'), osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'))); ?>
</div>

<?php
  } else {
?>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('contact_title'); ?></h6>

  <div class="content">
    <div style="float: right; padding: 0px 0px 10px 20px;">
      <?php echo nl2br(STORE_NAME_ADDRESS); ?>
    </div>

    <div style="float: right; padding: 0px 0px 10px 20px; text-align: center;">
      <?php echo '<b>' . $osC_Language->get('contact_store_address_title') . '</b><br />' . osc_image(DIR_WS_IMAGES . 'arrow_south_east.gif'); ?>
    </div>

    <p style="margin-top: 0px;"><?php echo $osC_Language->get('contact'); ?></p>

    <div style="clear: both;"></div>
  </div>
</div>

<form name="contact" action="<?php echo osc_href_link(FILENAME_INFO, 'contact=process', 'AUTO', true, false); ?>" method="post">

<div class="moduleBox">
  <h6></h6>
  <div class="content contact">
    <ol>
    <?php if (!empty($departments)){ ?>
      <li><?php echo osc_draw_label($osC_Language->get('contact_departments_title'), 'department_email') . osc_draw_pull_down_menu('department_email', $departments); ?></li><span id="departments_description"></span>
    <?php } ?>
      <li><?php echo osc_draw_label($osC_Language->get('contact_name_title'), 'name', null, true) . osc_draw_input_field('name', $osC_Customer->getName(), 'size="30"'); ?></li>
      <li><?php echo osc_draw_label($osC_Language->get('contact_telephone_title'), 'telephone') . osc_draw_input_field('telephone', '', 'size="30"'); ?></li>
      <li><?php echo osc_draw_label($osC_Language->get('contact_email_address_title'), 'email', null, true) . osc_draw_input_field('email', $osC_Customer->getEmailAddress(), 'size="30"'); ?></li>
      <li><?php echo osc_draw_label($osC_Language->get('contact_enquiry_title'), 'enquiry') . osc_draw_textarea_field('enquiry', null, 38, 5); ?></li>

    <?php if( ACTIVATE_CAPTCHA == '1') {?>
      <li class="clearfix captcha">
        <span class="captcha-image"><?php echo osc_image(osc_href_link(FILENAME_INFO, 'contact=show_captcha', 'AUTO', true, false), $osC_Language->get('captcha_image_title'), 215, 80, 'id="captcha-code"'); ?></span>
        <span class="captcha-field">
          <span><?php echo osc_link_object(osc_href_link('#'), osc_image('ext/securimage/images/refresh.png', $osC_Language->get('refresh_captcha_image_title')), 'id="refresh-captcha-code"'); ?></span>
          <span class="clearfix"><?php echo osc_draw_label($osC_Language->get('enter_captcha_code'), 'captcha_code', null, true); ?></span>
          <span><?php echo osc_draw_input_field('captcha_code', '', 'size="22"'); ?></span>
        </span>
      </li>
    <?php } ?>
    
    </ol>
  </div>
</div>

<?php
  echo osc_draw_hidden_session_id_field();
?>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')); ?>
</div>

</form>

<?php if( ACTIVATE_CAPTCHA == '1') {?>
<script type="text/javascript">
  $('refresh-captcha-code').addEvent('click', function(e) {
    e.stop();
    
    var contactController = '<?php echo osc_href_link(FILENAME_INFO, 'contact=show_captcha', 'AUTO', true, false); ?>';
    var captchaImgSrc = contactController + '&' + Math.random();
          
    $('captcha-code').setProperty('src', captchaImgSrc);
  });
</script>
<?php } ?>
  
  <?php if (!empty($departments_description)) { ?>
    <script type="text/javascript">
      window.addEvent("domready", function() {
        var description = {};
      <?php
        foreach($departments_description as $key => $description) {
      ?>
      
        description['<?php echo $key; ?>'] = '<?php echo $description; ?>';
      
      <?php } ?>
          
        $('departments_description').set('html', description[$('department_email').get('value')]);
          
        $('department_email').addEvent('change', function() {
          $('departments_description').set('html', description[this.value]);
        });
      });
    
    </script>
<?php
    }
  }
?>