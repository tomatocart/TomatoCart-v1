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

<div id="faqs" class="moduleBox">
    <div class="content">
        <?php
            $Qfaqs = toC_Faqs::getListing();
            
            while ($Qfaqs->next()) {
        ?>
        <dl id="faq<?php echo $Qfaqs->valueInt('faqs_id'); ?>">
            <dt class="question">
                <i class="icon-plus"></i>
                <?php echo $Qfaqs->value('faqs_question'); ?>
            </dt>
            <dd class="answer">
                <?php echo $Qfaqs->value('faqs_answer'); ?>
            </dd>
        </dl>
        <?php
            }
        ?>
    </div>
</div>

<div class="submitFormButtons">
	<a href="<?php echo osc_href_link(FILENAME_DEFAULT); ?>" class="btn btn-small pull-right"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></a>
</div>

<script language="javascript" type="text/javascript">
window.addEvent('domready',function(){
    $$('.question').each( function(question) {
        question.getNext().hide();

        question.addEvent('click', function(e){
            e = new Event(e);
            e.stop();
            
            answer = question.getNext();
            
            display = answer.getStyle('display').toString();
            if (display == 'none') {
                answer.setStyle('display', '');
                question.getElement('i').set('class', 'icon-minus');
            } else {
                answer.setStyle('display', 'none');
                question.getElement('i').set('class', 'icon-plus');
            }
        });

        <?php
            if (isset($_GET['faqs_id']) && !empty($_GET['faqs_id'])) {
        ?>
            if(question.getParent().id == 'faq<?php echo $_GET['faqs_id']; ?>') {
                question.getElement('i').set('class', 'icon-minus');
                question.getNext().setStyle('display', '');
            }
        <?php
            }
        ?>
    });
});
</script>

