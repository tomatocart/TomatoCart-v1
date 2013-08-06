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
  if ($messageStack->size('products') > 0) {
    echo $messageStack->output('products');
  }
?>

<?php
  if ($messageStack->size('reviews') > 0) {
    echo $messageStack->output('reviews');
  }
?>

<div class="moduleBox">

    <div class="content clearfix product-info btop">
    	<div class="row-fluid">
            <div class="span5 clearfix">
                <script type="text/javascript" src="templates/<?php echo $osC_Template->getCode(); ?>/javascript/milkbox/milkbox.js"></script>
                
                <div id="productImages">
                <?php
                    echo osc_link_object('javascript:void(0)', $osC_Image->show($osC_Product->getImage(), $osC_Product->getTitle(), ' large-img="' . $osC_Image->getImageUrl($osC_Product->getImage(), 'large') . '" id="product_image" style="padding:0px;border:0px;"', 'product_info'),'id="defaultProductImage"');
                    echo '<div style="clear:both"></div>';
                    
                    $images = $osC_Product->getImages();
                    foreach ($images as $image){
                        echo osc_link_object($osC_Image->getImageUrl($image['image'], 'large'), $osC_Image->show($image['image'], $osC_Product->getTitle(), '', 'mini'), 'rel="milkbox:group_products" product-info-img="' . $osC_Image->getImageUrl($image['image'], 'product_info') . '" large-img="' . $osC_Image->getImageUrl($image['image'], 'large') . '" style="float:left" class="mini"') . "\n";
                    }
                ?>
                </div>
            </div>
        
            <form class="form-inline span7" id="cart_quantity" name="cart_quantity" action="<?php echo osc_href_link(FILENAME_PRODUCTS, osc_get_all_get_params(array('action')) . '&action=cart_add'); ?>" method="post">
            	<div id="product-info">
            		<div class="price-info">
            			<span id="productInfoPrice" class="price"><?php echo $osC_Product->getPriceFormated(true); ?></span>
            			<span class="tax"><?php echo ( (DISPLAY_PRICE_WITH_TAX == '1') ? $osC_Language->get('including_tax') : '' ); ?></span>
                        <?php
                            if ($osC_Product->getAverageReviewsRating() > 0) {
                                echo osc_image(DIR_WS_IMAGES . 'stars_' . $osC_Product->getAverageReviewsRating() . '.png', sprintf($osC_Language->get('rating_of_5_stars'), $osC_Product->getAverageReviewsRating()));
                            }
                        ?>
            		</div>
            		<div class="divider"></div>
                    <div>
                      	<label><?php echo $osC_Language->get('field_sku'); ?></label>
                      	<span id="productInfoSku"><?php echo $osC_Product->getSKU(); ?></span>
                    </div>
                    <div>
                      	<label><?php echo $osC_Language->get('field_availability'); ?></label>
                      	<span id="productInfoAvailable"><?php echo ($osC_Product->getQuantity() > 0) ? $osC_Language->get('in_stock') : $osC_Language->get('out_of_stock'); ?></span>
                    </div>
                <?php 
                    if (PRODUCT_INFO_QUANTITY == '1') {
                ?>
                    <div>
                      	<label><?php echo $osC_Language->get('field_quantity'); ?></label>
                    	<span id="productInfoQty"><?php echo $osC_Product->getQuantity() . ' ' . $osC_Product->getUnitClass(); ?></span>
                    </div>
                <?php 
                    }
                ?>
                <?php 
                    if (PRODUCT_INFO_MOQ == '1') {
                ?>
                    <div>
                      	<label><?php echo $osC_Language->get('field_moq'); ?></label>
                    	<span><?php echo $osC_Product->getMOQ() . ' ' . $osC_Product->getUnitClass(); ?></span>
                    </div>
                <?php 
                    }
                ?>
                <?php 
                    if (PRODUCT_INFO_ORDER_INCREMENT == '1') {
                ?>
                    <div>
                      	<label><?php echo $osC_Language->get('field_order_increment'); ?></label>
                    	<span><?php echo $osC_Product->getOrderIncrement() . ' ' . $osC_Product->getUnitClass(); ?></span>
                    </div>
                <?php 
                    }
                ?>
                <?php 
                    if ($osC_Product->isDownloadable() && $osC_Product->hasSampleFile()) {
                ?>
                    <div>
                      	<label><?php echo $osC_Language->get('field_sample_url'); ?></label>
                    	<span><?php echo osc_link_object(osc_href_link(FILENAME_DOWNLOAD, 'type=sample&id=' . $osC_Product->getID()), $osC_Product->getSampleFile()); ?></span>
                    </div>
                <?php 
                    }
                ?>
                <?php 
                    if ($osC_Product->hasURL()) {
                ?>
                    <div>
                      <span><?php echo sprintf($osC_Language->get('go_to_external_products_webpage'), osc_href_link(FILENAME_REDIRECT, 'action=url&goto=' . urlencode($osC_Product->getURL()), 'NONSSL', null, false)); ?></span>
                    </div>
                <?php 
                    }
                ?>
                
                <?php
                    if ($osC_Product->getDateAvailable() > osC_DateTime::getNow()) {
                ?>
                    <div>
                      <span><?php echo sprintf($osC_Language->get('date_availability'), osC_DateTime::getLong($osC_Product->getDateAvailable())); ?></span>
                    </div>
                <?php
                    }
                ?>
              
                <?php
                    if ($osC_Product->hasAttributes()) {
                      $attributes = $osC_Product->getAttributes();
                      
                      foreach($attributes as $attribute) {
                ?>
                    <div>
                      	<label><?php echo $attribute['name']; ?>:</label>
                    	<span><?php echo $attribute['value']; ?></span>
                    </div>
                <?php
                        }
                    }
                ?>  
                
                <?php
                    if ($osC_Product->isGiftCertificate()) {
                        if ($osC_Product->isOpenAmountGiftCertificate()) {
                ?>
                    <div>
                      	<label><?php echo $osC_Language->get('field_gift_certificate_amount'); ?></label>
                    	<span><?php echo osc_draw_input_field('gift_certificate_amount', $osC_Product->getOpenAmountMinValue(), 'size="18"'); ?></span>
                    </div>
                    <?php
                        }
                    ?>
                    <div>
                      	<label><?php echo $osC_Language->get('field_senders_name'); ?></label>
                    	<span><?php echo osc_draw_input_field('senders_name', null, 'size="18"'); ?></span>
                    </div>
                    <?php
                        if ($osC_Product->isEmailGiftCertificate()) {
                    ?>
                    <div>
                      	<label><?php echo $osC_Language->get('field_senders_email'); ?></label>
                    	<span><?php echo osc_draw_input_field('senders_email', null, 'size="18"'); ?></span>
                    </div>
                    <?php
                        }
                    ?>
                    <div>
                      	<label><?php echo $osC_Language->get('field_recipients_name'); ?></label>
                    	<span><?php echo osc_draw_input_field('recipients_name', null, 'size="18"'); ?></span>
                    </div>
                    <?php
                        if ($osC_Product->isEmailGiftCertificate()) {
                    ?>  
                    <div>
                      	<label><?php echo $osC_Language->get('field_recipients_email'); ?></label>
                    	<span><?php echo osc_draw_input_field('recipients_email', null, 'size="18"'); ?></span>
                    </div>
                    <?php
                        }
                    ?>
                    <div>
                      	<label><?php echo $osC_Language->get('fields_gift_certificate_message'); ?></label>
                    	<span><?php echo osc_draw_textarea_field('message', null, 15, 2); ?></span>
                    </div>
                <?php
                    }
                ?>
            
            <?php 
                if ($osC_Product->hasVariants()) {
                    $combobox_array = $osC_Product->getVariantsComboboxArray();
            
                    foreach ($combobox_array as $groups_name => $combobox) {
            ?>
               		<div class="variantCombobox">
                        <label><?php echo $groups_name; ?> :</label>
                        <span><?php echo $combobox; ?></span>
                     </div>
            <?php 
                    }
                }
            ?>
            		<div class="divider"></div>
                    <div id="shoppingCart">
                    	<b><?php echo $osC_Language->get('field_short_quantity'); ?></b>&nbsp;
                    	<?php echo osc_draw_input_field('quantity', $osC_Product->getMOQ(), 'size="3"'); ?>&nbsp;
                    	<button type="submit" id="ac_productsinfo_<?php echo osc_get_product_id($osC_Product->getID()); ?>" class="btn btn-info ajaxAddToCart" title="<?php echo $osC_Language->get('button_add_to_cart'); ?>"><i class="icon-shopping-cart icon-white "></i> <?php echo $osC_Language->get('button_add_to_cart'); ?></button>
                    </div>
                    <div id="shoppingAction">
                      <?php
                          if ($osC_Template->isInstalled('compare_products', 'boxes')) {
                              echo osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params() . '&cid=' . $osC_Product->getID() . '&' . '&action=compare_products_add'), $osC_Language->get('add_to_compare'), 'class="compare-products"') . '&nbsp;<span>|</span>&nbsp;';
                          }
                      ?>
                		<?php echo osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $osC_Product->getID() . '&action=wishlist_add'), $osC_Language->get('add_to_wishlist'), 'class="wishlist"'); ?>
                    </div>
                    <?php 
                        $description = $osC_Product->getShortDescription();
                        if (!empty($description)) {
                    ?>
                    <div class="divider"></div>
                    <div class="description">
                        <p><?php echo $description; ?></p>
                    </div>
                    <?php 
                        }
                    ?>
            	</div>
            </form>
        </div>
    </div>
  
</div>

<?php if ($osC_Product->hasCustomizations()) { ?>
  <div class="moduleBox">
    <h3><?php echo $osC_Language->get('section_heading_customizations'); ?></h3>
  
    <div class="content">
      <?php
        if ($messageStack->size('products_customizations') > 0) {
          echo $messageStack->output('products_customizations');
        }
      ?>
        
      <form name="frmCustomizations" id="frmCustomizations" action="<?php echo osc_href_link(FILENAME_PRODUCTS, $osC_Product->getID() . '&action=save_customization_fields', 'AUTO', true, false); ?>" method="post" enctype="multipart/form-data">
      
        <?php echo $osC_Product->renderCustomizationFieldsList(); ?>
      
        <div class="submitFormButtons" style="text-align: right;">
          <?php echo osc_draw_image_submit_button('button_continue.gif', $osC_Language->get('button_continue')); ?>
        </div>
      </form>
    </div>
  </div>
<?php } ?>

<div class="clearfix">
    <ul id="productInfoTab" class="nav nav-tabs">
    <?php
        if ($osC_Product->getDescription()) {
    ?>
        <li class="active"><a href="#tabDescription" data-toggle="tab"><?php echo $osC_Language->get('section_heading_products_description'); ?></a></li>
    <?php 
        }
      
        if ($osC_Services->isStarted('reviews')) {
    ?>
        <li><a href="#tabReviews" data-toggle="tab"><?php echo $osC_Language->get('section_heading_reviews') . '(' . $osC_Reviews->getTotal($osC_Product->getID()) . ')'; ?></a></li>
    <?php 
        }
      
        if ($osC_Product->hasQuantityDiscount()) {
    ?>
        <li><a href="#tabQuantityDiscount" data-toggle="tab"><?php echo $osC_Language->get('section_heading_quantity_discount'); ?></a></li>
    <?php 
        }
        
        if ($osC_Product->hasAttachments()) {
    ?>
        <li><a href="#tabAttachments" data-toggle="tab"><?php echo $osC_Language->get('section_heading_products_attachments'); ?></a></li>
    <?php 
        }
      
        if ($osC_Product->hasAccessories()) {
    ?>
        <li><a href="#tabAccessories" data-toggle="tab"><?php echo $osC_Language->get('section_heading_products_accessories'); ?></a></li>
    <?php 
        }
    ?>
    </ul> 
      
    <div id="product-info-tab-content" class="tab-content">
        <?php 
            if ($osC_Product->getDescription()) {
        ?>
        <div class="tab-pane active" id="tabDescription">
        	<?php echo $osC_Product->getDescription(); ?>
        </div>
        <?php  } ?>
        
        <?php 
            if ($osC_Services->isStarted('reviews')) { 
        ?>
        <div class="tab-pane" id="tabReviews">
            <?php
                if ($osC_Reviews->getTotal($osC_Product->getID())==0) {
                    echo '<p>' . $osC_Language->get('no_review') . '</p>';
                } else {
                    $Qreviews = osC_Reviews::getListing($osC_Product->getID());
        
                    while ($Qreviews->next()) {
            ?>
            <dl class="review">
                <?php
                    echo '<dt>' . osc_image(DIR_WS_IMAGES . 'stars_' . $Qreviews->valueInt('reviews_rating') . '.png', sprintf($osC_Language->get('rating_of_5_stars'), $Qreviews->valueInt('reviews_rating'))).'&nbsp;&nbsp;&nbsp;&nbsp;'.sprintf($osC_Language->get('reviewed_by'), '&nbsp; <b>' . $Qreviews->valueProtected('customers_name')) . '</b>' . '&nbsp;&nbsp;(' . $osC_Language->get('field_posted_on').'&nbsp;' . osC_DateTime::getLong($Qreviews->value('date_added')) . ')' . '</dt>';
                     
                    echo '<dd>';
                    $ratings = osC_Reviews::getCustomersRatings($Qreviews->valueInt('reviews_id'));
                    
                    if (sizeof($ratings) > 0) {
                        echo '<table class="ratingsResult">';
                        foreach ($ratings as $rating) {
                            echo '<tr>
                                   <td class="name">' . $rating['name'] . '</td><td>' . osc_image(DIR_WS_IMAGES . 'stars_' . $rating['value'] . '.png', sprintf($osC_Language->get('rating_of_5_stars'), $rating['value'])) . '</td>
                                  </tr>';
                        }
                        echo '</table>';
                    }
                    
                    echo '<p>' . $Qreviews->valueProtected('reviews_text') . '</p>';
                    echo '</dd>'; 
                ?>
            </dl>
            <?php
                    }
                }
            ?>
                
            <hr />
            
            <h3><?php echo $osC_Language->get('heading_write_review'); ?></h3>
            
            <?php
              if ($osC_Reviews->is_enabled == false) {
            ?>
                <p><?php echo sprintf($osC_Language->get('login_to_write_review'), osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL')); ?></p>
            <?php
              } else {
            ?>
                <form id="frmReviews" name="newReview" action="<?php echo osc_href_link(FILENAME_PRODUCTS, 'reviews=new&' . $osC_Product->getID() . '&action=process', 'SSL'); ?>" method="post">
                    <p>
                    	<label for="author_name"><strong><?php echo $osC_Language->get('field_review_author'); ?></strong></label>
                    	<input type="text" name="author_name" id="author_name" value="<?php echo $osC_Customer->isLoggedOn() ? $osC_Customer->getName() : (isset($_SESSION['review_author_name']) ? $_SESSION['review_author_name'] : ''); ?>" />
                    </p>
                    
                <?php
                    $ratings = osC_Reviews::getCategoryRatings($osC_Product->getCategoryID());
                    if (sizeof($ratings) == 0) {
                ?>
                    <p>
                    	<label><strong><?php echo $osC_Language->get('field_review_rating'); ?></strong></label>
                    	<?php echo $osC_Language->get('review_lowest_rating_title') . ' ' . osc_draw_radio_field('rating', array('1', '2', '3', '4', '5')) . ' ' . $osC_Language->get('review_highest_rating_title'); ?>
                    </p>
                    <input type="hidden" id="rat_flag" name="rat_flag" value="0" />
                <?php 
                    } else {
                ?>
                    <table class="ratings" border="1" cellspacing="0" cellpadding="0">
                        <thead>
                            <tr>
                                <td width="45%">&nbsp;</td>
                                <td><?php echo $osC_Language->get('1_star'); ?></td>
                                <td><?php echo $osC_Language->get('2_stars'); ?></td>
                                <td><?php echo $osC_Language->get('3_stars'); ?></td>
                                <td><?php echo $osC_Language->get('4_stars'); ?></td>
                                <td><?php echo $osC_Language->get('5_stars'); ?></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $i = 0;
                                foreach ( $ratings as $key => $value ) {
                            ?>
                            <tr>
                                <td><?php echo $value;?></td>
                                <td><?php echo osc_draw_radio_field('rating_' . $key, 1, null, ' title="radio' . $i . '" ');?></td>
                                <td><?php echo osc_draw_radio_field('rating_' . $key, 2, null, ' title="radio' . $i . '" ');?></td>
                                <td><?php echo osc_draw_radio_field('rating_' . $key, 3, null, ' title="radio' . $i . '" ');?></td>
                                <td><?php echo osc_draw_radio_field('rating_' . $key, 4, null, ' title="radio' . $i . '" ');?></td>
                                <td><?php echo osc_draw_radio_field('rating_' . $key, 5, null, ' title="radio' . $i . '" ');?></td>
                            </tr>
                          <?php 
                                  $i++;
                              }
                          ?>
                        </tbody>
                    </table>
                <?php
                  }
                ?>
                    
                    <h6><?php echo $osC_Language->get('field_review'); ?></h6>
                    
                    <?php echo osc_draw_textarea_field('review', isset($_SESSION['review']) ? $_SESSION['review'] : null, 45, 5); ?>
                    <p><?php echo $osC_Language->get('review_note_message'); ?></p>
                    
                    <?php
                        if ((ACTIVATE_CAPTCHA === '1') && ($osC_Customer->isLoggedOn() === false) ) {
                    ?>
                    <div class="clearfix captcha">
                        <div class="captcha-image"><?php echo osc_image(osc_href_link(FILENAME_PRODUCTS, 'reviews=show_captcha'), $osC_Language->get('captcha_image_title'), 215, 80, 'id="captcha-code"'); ?></div>
                        <div class="captcha-field clearfix">
                            <div><?php echo osc_link_object(osc_href_link('#'), osc_image('ext/securimage/images/refresh.png', $osC_Language->get('refresh_captcha_image_title')), 'id="refresh-captcha-code"'); ?></div>
                            <p><?php echo $osC_Language->get('enter_captcha_code'); ?></p>
                            <div><?php echo osc_draw_input_field('captcha_code', '', 'size="22"'); ?></div>
                        </div>
                        <script type="text/javascript">
                            $('refresh-captcha-code').addEvent('click', function(e) {
                                e.stop();
                              
                                var reviewsController = '<?php echo osc_href_link(FILENAME_PRODUCTS, 'reviews=show_captcha', 'AUTO', true, false); ?>';
                                var captchaImgSrc = reviewsController + '&' + Math.random();
                                    
                                $('captcha-code').setProperty('src', captchaImgSrc);
                            });
                        </script>
                    </div>
                    <?php
                        }
                    ?>
                    
                    <div class="submitFormButtons">
                        <input type="hidden" id="radio_lines" name="radio_lines" value="<?php echo $i; ?>"/>

                        <button type="submit" title="<?php echo $osC_Language->get('submit_reviews'); ?>" class="btn btn-small"><?php echo $osC_Language->get('submit_reviews'); ?></button>
                    </div>
                
                </form>
            <?php 
                } 
            ?>
        </div>
        <?php
            }
        ?>
        
        <?php  
            if ($osC_Product->hasQuantityDiscount()) { 
        ?>
        <div class="tab-pane" id="tabQuantityDiscount">
        	<?php echo $osC_Product->renderQuantityDiscountTable(); ?>
        </div>
        <?php } ?>
        
        <?php 
        if ($osC_Product->hasAttachments()) {
          $attachments = $osC_Product->getAttachments();
        ?>
        <div class="tab-pane" id="tabAttachments">
            <dl>
            <?php
                foreach($attachments as $key => $attachment) {
                    echo '<dt>' . 
                            osc_link_object(osc_href_link(FILENAME_DOWNLOAD, 'type=attachment&aid=' . $attachment['attachments_id']), $attachment['attachment_name']) . 
                         '</dt>' . 
                         '<dd>' . $attachment['description'] . '</dd>';
                } 
            ?>
            <dl>
        </div>
        <?php 
            }
        ?>
        
        <?php 
            if ($osC_Product->hasAccessories()) {
                $accessories = $osC_Product->getAccessories();
         ?>
        <div class="tab-pane" id="tabAccessories">
            <?php
                foreach ($accessories as $accessory) {
                    $product = new osC_Product($accessory); 
            ?>
            <div class="accessories clearfix">
            	<div class="image"><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $accessory), $osC_Image->show($product->getImage(), $product->getTitle())); ?></div>
                <div class="desc">
                    <h6><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $accessory), $product->getTitle()); ?></h6>
                    <p><?php echo $product->getShortDescription(); ?></p>
                </div>
            </div>
            <?php 
                } 
            ?>
        </div>
        <?php 
            } 
        ?>
    </div>
</div>

<script type="text/javascript" src="includes/javascript/reviews.js"></script>
<script type="text/javascript" src="ext/mojozoom/mojozoom.js"></script>

<?php if ($osC_Product->hasVariants()) { ?>
  <script type="text/javascript" src="templates/<?php echo $osC_Template->getCode(); ?>/javascript/variants.js"></script>
<?php } ?>

<script type="text/javascript">
window.addEvent('domready', function(){
  //zoom image
  MojoZoom.makeZoomable(  
    document.getElementById("product_image"),   
    $('product_image').get('large-img'),
    null, 
    270, 
    210, 
    false,
    function(e) {
      if (e.preventDefault) {
        e.preventDefault();
      } else {
        e.returnValue = false;
      }

      var miniImages = $$(".mini");
      var img = $$('.mojozoom_imgctr').getElement('img').get('src');
      var index = 0;
  
      for (i = 0; i < miniImages.length; i++) {
        if (miniImages[i].get("large-img") == img) {
          index = i;
          break;
        }
      }
    
      Milkbox.openMilkbox(Milkbox.galleries[0], index); 
    }
  );
  
  //variants
  <?php 
  if ($osC_Product->hasVariants()) {   
    ?>
  new TocVariants({
    remoteUrl: '<?php echo osc_href_link('json.php', null, 'SSL', false, false, true); ?>',
    combVariants: $$('.variantCombobox select'),
    variants: <?php echo $toC_Json->encode($osC_Product->getVariants()); ?>,
    productsId: <?php echo $osC_Product->getID(); ?>,
    displayQty:  <?php echo (PRODUCT_INFO_QUANTITY == '1') ? 'true' : 'false'; ?>,
    hasSpecial: <?php echo $osC_Product->hasSpecial() ? 1 : 0; ?>,
    unitClass: '<?php echo $osC_Product->getUnitClass(); ?>',
    lang: {
      txtInStock: '<?php echo addslashes($osC_Language->get('in_stock'));?>',
      txtOutOfStock: '<?php echo addslashes($osC_Language->get('out_of_stock')); ?>',
      txtNotAvailable: '<?php echo addslashes($osC_Language->get('not_available')); ?>',
      txtTaxText: '<?php echo addslashes(( (DISPLAY_PRICE_WITH_TAX == '1') ? $osC_Language->get('including_tax') : '' )); ?>'
    }
  });
  
 <?php } ?>
  
  //add mouse over events to mini images
  var imgElem = $$('.mojozoom_imgctr').getElement('img');
  var miniImages = $$(".mini");
  if (miniImages.length > 0) {
    miniImages.each(function(img) {
      img.addEvent('mouseleave', function(e) {
        if (this.hasClass('mouseover')) {
          this.removeClass('mouseover');
        }
      });
      
      img.addEvent('mouseover', function(e) {
        if (!this.hasClass('mouseover')) {
          this.addClass('mouseover');
          
          if ($defined(e)) {e.preventDefault();}
  
          var oldImg = imgElem.get('src');
          var largeImg = this.get("large-img");
          
          if (oldImg != largeImg) {
            img.set('src', largeImg);
            
            new Fx.Tween($('product_image'), {
               duration: 10,
               property: 'opacity'
            }).start(0).chain(function() {
              $('product_image').src = this.get("product-info-img");
              
              $$('.mojozoom_imgctr img').each(function(imgCtr) {
                imgCtr.setProperty('src', largeImg);
              });                          
              
              $('product_image').fade('in');
            }.bind(this));
          }
        }
      });
    }, this);
  }
  
  //reviews
  new Reviews({
    flag: <?php echo (sizeof($ratings) == 0) ? '0' : '1' ?>,
    ratingsCount: <?php echo sizeof($ratings); ?>,
    reviewMinLength: <?php echo REVIEW_TEXT_MIN_LENGTH; ?>,
    ratingsErrMsg: '<?php echo $osC_Language->get('js_review_rating'); ?>',
    reviewErrMsg: '<?php echo sprintf($osC_Language->get('js_review_text'), REVIEW_TEXT_MIN_LENGTH); ?>',
    frmReviews: $('frmReviews')
  });
  
  
  //gift certificate
  <?php 
  if ($osC_Product->isGiftCertificate()) {
  ?>
    $('cart_quantity').addEvent('submit', function(e) {
      e.preventDefault();
      
      var errors = [];
      
    <?php 
    if ($osC_Product->isOpenAmountGiftCertificate()) {
      $min = $osC_Product->getOpenAmountMinValue();
      $max = $osC_Product->getOpenAmountMaxValue();
    ?>
      var amount = $('gift_certificate_amount').value;
      
      if (amount < <?php echo $min; ?> || amount > <?php echo $max; ?>) {
        errors.push('<?php echo $osC_Language->get('error_message_open_gift_certificate_amount'); ?>');
      }
    <?php 
    } 
    ?>
    
    <?php 
    if ($osC_Product->isEmailGiftCertificate()) {
    ?>
    
      if ($('senders_name').value == '') {
        errors.push('<?php echo $osC_Language->get('error_sender_name_empty'); ?>');
      }
      
      if ($('senders_email').value == '') {
        errors.push('<?php echo $osC_Language->get('error_sender_email_empty'); ?>');
      }
      
      if ($('recipients_name').value == '') {
        errors.push('<?php echo $osC_Language->get('error_recipient_name_empty'); ?>');
      }
      
      if ($('recipients_email').value == '') {
        errors.push('<?php echo $osC_Language->get('error_recipient_email_empty'); ?>');
      }
      
      if ($('message').value == '') {
        errors.push('<?php echo $osC_Language->get('error_message_empty'); ?>');
      }
      
    <?php 
    } 
    ?>
      
      if (errors.length > 0) {
        alert(errors.join('\n'));
        return false;
      } else {
        $('cart_quantity').submit();
      }
    });
  <?php 
  } 
  ?>
});
</script>
