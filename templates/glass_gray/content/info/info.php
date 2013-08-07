<?php
/*
  $Id: info.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<h1><?php echo $article['articles_name']; ?></h1>

<?php
    if (!osc_empty($article['articles_image'])) {
      echo '<p style="float: right; padding: 0px 5px 5px 5px">' . $osC_Image->show($article['articles_image'], $article['articles_image'], '', 'product_info', 'articles') . '</p>';
    }
?>
<p><?php echo $article['articles_description']; ?></p>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_link_object(osc_href_link(FILENAME_DEFAULT), osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'))); ?>
</div>
