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

global $osC_Language;
?>

<!-- box search start //-->

<div class="boxNew">
    <div class="boxTitle"><?php echo osc_link_object($osC_Box->getTitleLink(), $osC_Box->getTitle()); ?></div>
    
    <div class="boxContents" style="text-align: center;">
		<form name="search" action="<?php echo osc_href_link(FILENAME_SEARCH, null, 'NONSSL', false); ?>" method="get" style="line-height: 30px">
			<?php echo osc_draw_input_field('keywords', null, 'style="width: 78%;" maxlength="30"') . '&nbsp;' . osc_draw_hidden_session_id_field() . osc_draw_image_submit_button('button_quick_find.gif', $osC_Language->get('box_search_heading')); ?>
			<br />
			<?php sprintf($osC_Language->get('box_search_text'), osc_href_link(FILENAME_SEARCH)); ?>
		</form>
    </div>
</div>

<!-- box search end //-->
