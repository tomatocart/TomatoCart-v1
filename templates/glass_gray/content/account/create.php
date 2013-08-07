<?php
/*
  $Id: create.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  if ($messageStack->size('create') > 0) {
    echo $messageStack->output('create');
  }
?>

<form name="create" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'create=save', 'SSL'); ?>" method="post" onsubmit="return check_form(create);">

<div class="moduleBox">
  
  <h6><em><?php echo $osC_Language->get('form_required_information'); ?></em><?php echo $osC_Language->get('my_account_title'); ?></h6>

  <div class="content">
    <ol>

<?php
  if (ACCOUNT_GENDER > -1) {
    $gender_array = array(array('id' => 'm', 'text' => $osC_Language->get('gender_male')),
                          array('id' => 'f', 'text' => $osC_Language->get('gender_female')));
?>

      <li><?php echo osc_draw_label($osC_Language->get('field_customer_gender'), 'gender1', null, (ACCOUNT_GENDER > 0)) . osc_draw_radio_field('gender', $gender_array); ?></li>

<?php
  }
?>

      <li><?php echo osc_draw_label($osC_Language->get('field_customer_first_name'), 'firstname', null, true) . osc_draw_input_field('firstname',null); ?></li>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_last_name'), 'lastname', null, true) . osc_draw_input_field('lastname', null); ?></li>

<?php
  if (ACCOUNT_DATE_OF_BIRTH == '1') {
?>

      <li><?php echo osc_draw_label($osC_Language->get('field_customer_date_of_birth'), 'dob_days', null, true) . osc_draw_date_pull_down_menu('dob', null, false, null, null, date('Y')-1901, -5); ?></li>

<?php
  }
?>

      <li><?php echo osc_draw_label($osC_Language->get('field_customer_email_address'), 'email_address', null, true) . osc_draw_input_field('email_address'); ?></li>

<?php
  if (ACCOUNT_NEWSLETTER == '1') {
?>

      <li><?php echo osc_draw_label($osC_Language->get('field_customer_newsletter'), 'newsletter') . osc_draw_checkbox_field('newsletter', '1'); ?></li>

<?php
  }
?>

      <li><?php echo osc_draw_label($osC_Language->get('field_customer_password'), 'password', null, true) . osc_draw_password_field('password', 'AUTOCOMPLETE="off"'); ?></li>
      <li><?php echo osc_draw_label($osC_Language->get('field_customer_password_confirmation'), 'confirmation', null, true) . osc_draw_password_field('confirmation', 'AUTOCOMPLETE="off"'); ?></li>
      
      <?php if( ACTIVATE_CAPTCHA == '1') {?>
        <li class="clearfix captcha">
          <span class="captcha-image"><?php echo osc_image(osc_href_link(FILENAME_ACCOUNT, 'create=show_captcha', 'AUTO', true, false), $osC_Language->get('captcha_image_title'), 215, 80, 'id="captcha-code"'); ?></span>
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
  if (DISPLAY_PRIVACY_CONDITIONS == '1') {
?>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('create_account_terms_heading'); ?></h6>

  <div class="content">
    <?php 
    $privacy = str_replace('<a href="%s">', '<a href="' . osc_href_link(FILENAME_JSON, 'module=account&action=display_privacy')  . '" class="multibox" rel="width:800,height:400,ajax:true">', $osC_Language->get('create_account_terms_description'));
    echo $privacy . '<br /><br /><ol><li>' . osc_draw_checkbox_field('privacy_conditions', array(array('id' => 1, 'text' => $osC_Language->get('create_account_terms_confirm')))) . '</li></ol>'; 
    ?>
  
  </div>
</div>

<?php
  }
?>

<div class="submitFormButtons">
  <span style="float: right"><?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')); ?></span>
    
  <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
</div>

</form>

<?php if( ACTIVATE_CAPTCHA == '1') {?>
<script type="text/javascript">
  $('refresh-captcha-code').addEvent('click', function(e) {
    e.stop();
    
    var contactController = '<?php echo osc_href_link(FILENAME_ACCOUNT, 'create=show_captcha', 'AUTO', true, false); ?>';
    var captchaImgSrc = contactController + '&' + Math.random();
          
    $('captcha-code').setProperty('src', captchaImgSrc);
  });
</script>
<?php } ?>
  
<script type="text/javascript">
  window.addEvent("domready",function() {
    var overlay = new Overlay(); 
    var box = new MultiBox('multibox', { 
        overlay: overlay
    });
  });
</script>