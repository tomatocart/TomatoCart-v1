<?php
/*
  $Id: faqs.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
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
        <?php echo $Qfaqs->value('faqs_question'); ?>
      </dt>
      <dd class="answer">
        <?php echo $Qfaqs->value('faqs_answer'); ?>
      </dd>
    </dl>
  <?php
  }
  ?>
  
  <div style="clear: both">&nbsp;</div>
  </div>
</div>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_link_object(osc_href_link(FILENAME_DEFAULT), osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'))); ?>
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
      } else {
        answer.setStyle('display', 'none');
      }
      

    });

    <?php
      if (isset($_GET['faqs_id']) && !empty($_GET['faqs_id'])) {
    ?>
      if(question.getParent().id == 'faq<?php echo $_GET['faqs_id']; ?>')
        question.getNext().setStyle('display', '');
    <?php
      }
    ?>
  });
});
</script>

