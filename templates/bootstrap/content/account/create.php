<?php
/**
 * TomatoCart Open Source Shopping Cart Solution
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 (2007)
 * as published by the Free Software Foundation.
 *
 * @package      TomatoCart
 * @author       TomatoCart Dev Team
 * @copyright    Copyright (c) 2009 - 2012, TomatoCart. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html
 * @link         http://tomatocart.com
 * @since        Version 1.1.8
 * @filesource
*/
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  if ($messageStack->size('create') > 0) {
    echo $messageStack->output('create');
  }
?>

<form name="create" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'create=save', 'SSL'); ?>" method="post" onsubmit="return check_form(create);" class="form-horizontal">

<div class="moduleBox">
  
	<h6><em class="pull-right"><?php echo $osC_Language->get('form_required_information'); ?></em><?php echo $osC_Language->get('my_account_title'); ?></h6>

	<div class="content">

<?php
  if (ACCOUNT_GENDER > -1) {
    $gender_array = array(array('id' => 'm', 'text' => $osC_Language->get('gender_male')),
                          array('id' => 'f', 'text' => $osC_Language->get('gender_female')));
                          
    if (!isset($gender)) {
        $gender = isset($_POST['gender']) ? $_POST['gender'] : 'f';
    }
?>
        <div class="control-group">
            <label class="control-label" for="gender1"><?php echo $osC_Language->get('field_customer_gender') . ((ACCOUNT_GENDER > 0) ? '<em>*</em>' : ''); ?></label>
            <div class="controls">
            	<label class="radio inline" for="gender1"><input type="radio" value="m" id="gender1" name="gender" <?php echo (isset($gender) && $gender == 'm') ? 'checked="checked"' : ''; ?> /><?php echo $osC_Language->get('gender_male'); ?></label>
            	<label class="radio inline" for="gender2"><input type="radio" value="f" id="gender2" name="gender" <?php echo (isset($gender) && $gender == 'f') ? 'checked="checked"' : ''; ?> /><?php echo $osC_Language->get('gender_female'); ?></label>
            </div>
        </div>
<?php
  }
?>
        <div class="control-group">
            <label class="control-label" for="firstname"><?php echo $osC_Language->get('field_customer_first_name'); ?><em>*</em></label>
            <div class="controls">
            	<?php echo osc_draw_input_field('firstname',null); ?>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="firstname"><?php echo $osC_Language->get('field_customer_last_name'); ?><em>*</em></label>
            <div class="controls">
            	<?php echo osc_draw_input_field('lastname',null); ?>
            </div>
        </div>
<?php
  if (ACCOUNT_DATE_OF_BIRTH == '1') {
?>
        <div class="control-group">
            <label class="control-label"><?php echo $osC_Language->get('field_customer_date_of_birth'); ?><em>*</em></label>
            <div class="controls">
            	<?php echo osc_draw_date_pull_down_menu('dob', null, false, null, null, date('Y')-1901, -5); ?>
            </div>
        </div>
<?php
  }
?>
        <div class="control-group">
            <label class="control-label" for="email_address"><?php echo $osC_Language->get('field_customer_email_address'); ?><em>*</em></label>
            <div class="controls">
            	<?php echo osc_draw_input_field('email_address'); ?>
            </div>
        </div>

<?php
  if (ACCOUNT_NEWSLETTER == '1') {
?>
        <div class="control-group">
            <label class="control-label" for="newsletter"><?php echo $osC_Language->get('field_customer_newsletter'); ?><em>*</em></label>
            <div class="controls">
            	<?php echo osc_draw_checkbox_field('newsletter', '1'); ?>
            </div>
        </div>
<?php
  }
?>
        <div class="control-group">
            <label class="control-label" for="password"><?php echo $osC_Language->get('field_customer_password'); ?><em>*</em></label>
            <div class="controls">
            	<?php echo osc_draw_password_field('password', 'AUTOCOMPLETE="off"'); ?>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="confirmation"><?php echo $osC_Language->get('field_customer_password_confirmation'); ?><em>*</em></label>
            <div class="controls">
            	<?php echo osc_draw_password_field('confirmation', 'AUTOCOMPLETE="off"'); ?>
            </div>
        </div>
      <?php if( ACTIVATE_CAPTCHA == '1') {?>
        <div class="control-group">
            <label class="control-label" for="confirmation">&nbsp;</label>
            <div class="controls">
                <span class="captcha-image"><?php echo osc_image(osc_href_link(FILENAME_ACCOUNT, 'create=show_captcha', 'AUTO', true, false), $osC_Language->get('captcha_image_title'), 215, 80, 'id="captcha-code"'); ?></span>
                <span class="captcha-field">
                    <span><?php echo osc_link_object(osc_href_link('#'), osc_image('ext/securimage/images/refresh.png', $osC_Language->get('refresh_captcha_image_title')), 'id="refresh-captcha-code"'); ?></span>
                    <span class="clearfix"><?php echo osc_draw_label($osC_Language->get('enter_captcha_code'), 'captcha_code', null, true); ?></span>
                    <span><?php echo osc_draw_input_field('captcha_code', '', 'size="22"'); ?></span>
                </span>
            </div>
        </div>
      <?php } ?>
	</div>
</div>

<?php
    if (DISPLAY_PRIVACY_CONDITIONS == '1') {
?>

<div class="moduleBox">
	<h6><?php echo $osC_Language->get('create_account_terms_heading'); ?></h6>

    <div class="control-group">
    	<p>
            <?php 
                $privacy = str_replace('<a href="%s">', '<a href="' . osc_href_link(FILENAME_JSON, 'module=account&action=display_privacy')  . '" class="multibox" rel="width:800,height:400,ajax:true">', $osC_Language->get('create_account_terms_description'));
                echo $privacy; 
            ?>
    	</p>
    	<label class="checkbox"  for="privacy_conditions">
        	<?php echo osc_draw_checkbox_field('privacy_conditions', array(array('id' => 1, 'text' => $osC_Language->get('create_account_terms_confirm')))); ?>
        </label>
    </div>
</div>

<?php
    }
?>

<div class="submitFormButtons">
    <a href="<?php echo osc_href_link(FILENAME_ACCOUNT, null, 'SSL'); ?>" class="btn btn-small pull-left"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
    
    <button type="submit" class="btn btn-small pull-right"><i class="icon-ok-sign icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
</div>

</form>

<?php if( ACTIVATE_CAPTCHA == '1') { ?>
<script type="text/javascript">
window.addEvent("domready",function() {
    $('refresh-captcha-code').addEvent('click', function(e) {
        e.stop();
        
        var contactController = '<?php echo osc_href_link(FILENAME_ACCOUNT, 'create=show_captcha', 'AUTO', true, false); ?>';
        var captchaImgSrc = contactController + '&' + Math.random();
              
        $('captcha-code').setProperty('src', captchaImgSrc);
    });
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