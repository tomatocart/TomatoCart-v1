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
    if ($messageStack->size('guestbook') > 0) {
        echo $messageStack->output('guestbook');
    }
?>   

<form name="guestbook_edit" action="<?php echo osc_href_link(FILENAME_INFO, 'guestbook=save'); ?>" method="post" class="form-horizontal">

<div class="moduleBox">
    <h6><?php echo $osC_Language->get('guestbook_new_heading'); ?></h6>
    
    <div class="content">
        <div class="control-group">
            <label class="control-label" for="title"><?php echo $osC_Language->get('field_title'); ?><em>*</em></label>
            <div class="controls">
            	<?php echo osc_draw_input_field('title'); ?>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="email"><?php echo $osC_Language->get('field_email'); ?><em>*</em></label>
            <div class="controls">
            	<?php echo osc_draw_input_field('email'); ?>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="url"><?php echo $osC_Language->get('field_url'); ?></label>
            <div class="controls">
            	<?php echo osc_draw_input_field('url'); ?>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="content"><?php echo $osC_Language->get('field_content'); ?><em>*</em></label>
            <div class="controls">
            	<?php echo osc_draw_textarea_field('content', '', 29); ?>
            </div>
        </div>
         
        <?php 
            if( ACTIVATE_CAPTCHA == '1') {
        ?>
        <div class="control-group captcha">
        	<label class="control-label">&nbsp;</label>
            <div class="controls">
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

<div class="submitFormButtons">
    <button type="submit" class="btn btn-small pull-right"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></button>
    
    <a class="btn btn-small" href="<?php echo osc_href_link(FILENAME_INFO, 'guestbook'); ?>"><i class="icon-chevron-left icon-white"></i> <?php echo $osC_Language->get('button_back'); ?></a>
</div>

</form>

<?php if( ACTIVATE_CAPTCHA == '1') {?>
    <script type="text/javascript">
        $('refresh-captcha-code').addEvent('click', function(e) {
            e.stop();
            
            var guestbookController = '<?php echo osc_href_link(FILENAME_INFO, 'guestbook=show_captcha', 'AUTO', true, false); ?>';
            var captchaImgSrc = guestbookController + '&' + Math.random();
                
            $('captcha-code').setProperty('src', captchaImgSrc);
        });
    </script>
<?php } ?>