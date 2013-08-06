<?php
/*
  $Id: guestbooks.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
  $Qlisting = toC_Guestbook::getListing();
?>

  <h1><?php echo $osC_Template->getPageTitle(); ?></h1>
   
  <?php 
    if ($messageStack->size('guestbook') > 0) {
      echo $messageStack->output('guestbook');
    }
  ?>   
  
  <div class="moduleBox">
    <h6><?php echo $osC_Template->getPageTitle(); ?></h6>

    <div class="content">
      <?php if ($Qlisting->numberOfRows() > 0) { ?>
        <dl id="guestbook">
        <?php 
           $i = 1;
           $class = '';
           
           while ($Qlisting->next()){
             if ($i == $Qlisting->numberOfRows()) {$class = ' class="last"';} 
        ?>
  
          <dt>
            <span><?php echo osC_DateTime::getLong($Qlisting->value('date_added'));  ?></span>
            <?php echo $Qlisting->value('title'); ?>
          </dt>
          <dd<?php echo $class; ?>><?php echo $Qlisting->value('content'); ?></dd>
        
        <?php 
             $i++;
           }
           
           $Qlisting->freeResult();
        ?>
        </dl>          
      <?php 
        }else {
          echo $osC_Language->get('field_guestbook_no_records');
        }
      ?>
    </div>

    <p align="right">
        <?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'guestbook=new'), osc_draw_image_button('write_message.png', $osC_Language->get('button_write_message'))); ?>
    </p>
  </div>
