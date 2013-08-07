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

    $departments = array();
    
    $Qlisting = toC_Departments::getListing();
    while($Qlisting->next()) {
        $departments[] = array('id' => $Qlisting->value('departments_email_address'),
                             'text' => $Qlisting->value('departments_title'));
        
        $departments_description[$Qlisting->value('departments_email_address')] = $Qlisting->value('departments_description');
    }
?>

<?php
    if ($messageStack->size('contact') > 0) {
        echo $messageStack->output('contact');
    }
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php 
    if (isset($_GET['contact']) && ($_GET['contact'] == 'success')) {
?>
<div class="moduleBox">
	<div class="content btop">
		<p><?php echo $osC_Language->get('contact_email_sent_successfully'); ?></p>
	</div>
</div>

<div class="submitFormButtons" style="text-align: right;">
  	<a class="btn btn-small pull-right" href="<?php echo osc_href_link(FILENAME_INFO, 'contact'); ?>"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></a>
</div>
<?php
    } else {
?>
<div class="moduleBox">
    <h6><?php echo $osC_Language->get('contact_title'); ?></h6>
    
    <div class="content">
    	<div class="row-fluid">
            <div class="span8">
    	        <b><?php echo $osC_Language->get('contact_store_address_title') . '</b><br />' . osc_image(DIR_WS_IMAGES . 'arrow_south_east.gif'); ?></b>
            </div>
            
            <div class="span4">
    	        <?php echo nl2br(STORE_NAME_ADDRESS); ?>
            </div>
    	</div>
    
    	<p><?php echo $osC_Language->get('contact'); ?></p>
    </div>
</div>

<form name="contact" action="<?php echo osc_href_link(FILENAME_INFO, 'contact=process', 'AUTO', true, false); ?>" method="post" class="form-horizontal">

    <div class="moduleBox">
    	<h6><?php echo $osC_Template->getPageTitle(); ?></h6>
    	
        <div class="content contact">
            <?php 
                if (!empty($departments)) : 
            ?>
            <div class="control-group">
                <label class="control-label" for="department_email"><?php echo $osC_Language->get('contact_departments_title');?><em>*</em></label>
                <div class="controls">
                    <?php echo osc_draw_pull_down_menu('department_email', $departments); ?>
                </div>
            </div>
            <?php
                endif;
            ?>
            <div class="control-group">
                <label class="control-label" for="name"><?php echo $osC_Language->get('contact_name_title'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_input_field('name', $osC_Customer->getName()); ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="telephone"><?php echo $osC_Language->get('contact_telephone_title'); ?></label>
                <div class="controls">
                	<?php echo osc_draw_input_field('telephone'); ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="email"><?php echo $osC_Language->get('contact_email_address_title'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_input_field('email', $osC_Customer->getEmailAddress()); ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="enquiry"><?php echo $osC_Language->get('contact_enquiry_title'); ?><em>*</em></label>
                <div class="controls">
                	<?php echo osc_draw_textarea_field('enquiry'); ?>
                </div>
            </div>
        
            <?php 
                if( ACTIVATE_CAPTCHA == '1') {
            ?>
            <div class="control-group">
                <label class="control-label">&nbsp;</label>
                <div class="controls captcha">
                    <span class="captcha-image"><?php echo osc_image(osc_href_link(FILENAME_INFO, 'contact=show_captcha', 'AUTO', true, false), $osC_Language->get('captcha_image_title'), 215, 80, 'id="captcha-code"'); ?></span>
                    <span class="captcha-field">
                        <span><?php echo osc_link_object(osc_href_link('#'), osc_image('ext/securimage/images/refresh.png', $osC_Language->get('refresh_captcha_image_title')), 'id="refresh-captcha-code"'); ?></span>
                        <span class="clearfix"><?php echo osc_draw_label($osC_Language->get('enter_captcha_code'), 'captcha_code', null, true); ?></span>
                        <span><?php echo osc_draw_input_field('captcha_code', '', 'size="22"'); ?></span>
                    </span>
                </div>
            </div>
            <?php 
                } 
            ?>
        </div>
    </div>

    <?php
      echo osc_draw_hidden_session_id_field();
    ?>

    <div class="submitFormButtons">
        <button type="submit" class="btn btn-small pull-right"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
    </div>

</form>

<?php 
    if( ACTIVATE_CAPTCHA == '1') {
?>
<script type="text/javascript">
    $('refresh-captcha-code').addEvent('click', function(e) {
        e.stop();
        
        var contactController = '<?php echo osc_href_link(FILENAME_INFO, 'contact=show_captcha', 'AUTO', true, false); ?>';
        var captchaImgSrc = contactController + '&' + Math.random();
        $('captcha-code').setProperty('src', captchaImgSrc);
    });
</script>
<?php 
    } 
?>
  
<?php 
    if (!empty($departments_description)) { 
?>
<script type="text/javascript">
    window.addEvent("domready", function() {
        var description = {};
        <?php
            foreach($departments_description as $key => $description) {
        ?>
        description['<?php echo $key; ?>'] = '<?php echo $description; ?>';
        <?php 
            } 
        ?>
          
        $('departments_description').set('html', description[$('department_email').get('value')]);
          
        $('department_email').addEvent('change', function() {
          $('departments_description').set('html', description[this.value]);
        });
    });
</script>
<?php
    }
?>

<?php 
    }
?>