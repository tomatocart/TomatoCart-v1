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
    global $osC_Template;
    require_once ('templates/' . $osC_Template->getCode() . '/models/guest_books.php');
?>

<!-- box guest_book start //-->

<div id="boxGuestbook" class="boxNew">
    <div class="boxTitle"><?php echo osc_link_object($osC_Box->getTitleLink(), $osC_Box->getTitle()); ?></div>
    
    <div class="boxContents">
    	<?php 
    	    $guest_books = get_guest_book();
    	    
    	    if (!empty($guest_books)) {
    	        foreach ($guest_books as $guest_book) {
        ?>
        <dl>
        	<dt><?php echo $guest_book['title']; ?></dt>
        	<dd><?php echo $guest_book['content']; ?></dd>
        </dl>
        <?php 
    	        }
    	    }
    	?>
      
    </div>
</div>
<!-- box guest_book end //-->
