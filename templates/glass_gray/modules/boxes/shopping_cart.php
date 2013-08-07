<?php
/*
  $Id: shopping_cart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<!-- box shopping_cart start //-->

<div class="boxNew">
  <div class="boxTitle">
    <?php echo osc_link_object($osC_Box->getTitleLink(), $osC_Box->getTitle()); ?>
    <?php echo osc_draw_image_button('button_ajax_cart_up.png', null, 'id="ajaxCartCollapse"'); ?>
    <?php echo osc_draw_image_button('button_ajax_cart_down.png', null, 'id="ajaxCartExpand" class="hidden"');?>
  </div>

  <div class="boxContents"><?php echo $osC_Box->getContent(); ?></div>
</div>

<!-- box shopping_cart end //-->
