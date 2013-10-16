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

  <div class="content">
    <div style="float: left;">
      <script type="text/javascript" src="templates/<?php echo $osC_Template->getCode(); ?>/javascript/milkbox/milkbox.js"></script>
    
      <div id="productImages">
      <?php
        echo osc_link_object('javascript:void(0)', $osC_Image->show($osC_Product->getImage(), $osC_Product->getTitle(), ' large-img="' . $osC_Image->getImageUrl($osC_Product->getImage(), 'large') . '" id="product_image" style="padding:0px;border:0px;"', 'product_info'),'id="defaultProductImage"');
        echo '<div style="clear:both"></div>';
    
        $images = $osC_Product->getImages();
        foreach ($images as $image){
          echo osc_link_object($osC_Image->getImageUrl($image['image'], 'originals'), $osC_Image->show($image['image'], $osC_Product->getTitle(), '', 'mini'), 'rel="milkbox:group_products" product-info-img="' . $osC_Image->getImageUrl($image['image'], 'product_info') . '" large-img="' . $osC_Image->getImageUrl($image['image'], 'large') . '" style="float:left" class="mini"') . "\n";
        }
      ?>
      </div>
    </div>


    <form id="cart_quantity" name="cart_quantity" action="<?php echo osc_href_link(FILENAME_PRODUCTS, osc_get_all_get_params(array('action')) . '&action=cart_add'); ?>" method="post">
   
    <table id="productInfo" border="0" cellspacing="0" cellpadding="2" style="float: right; width: 270px">
    
      <tr>
        <td colspan="2" id="productInfoPrice"><?php echo $osC_Product->getPriceFormated(true) . '&nbsp;' . ( (DISPLAY_PRICE_WITH_TAX == '1') ? $osC_Language->get('including_tax') : '' ); ?></td>
      </tr>
      
  <?php
    if ($osC_Product->getSKU()) {
  ?>
      <tr>
        <td class="label" width="45%"><?php echo $osC_Language->get('field_sku'); ?></td>
        <td id="productInfoSku"><?php echo $osC_Product->getSKU(); ?>&nbsp;</td>
      </tr>
  <?php
    }
  ?>

      <tr>
        <td class="label"><?php echo $osC_Language->get('field_availability'); ?></td>
        <td id="productInfoAvailable"><?php echo ($osC_Product->getQuantity() > 0) ? $osC_Language->get('in_stock') : $osC_Language->get('out_of_stock'); ?></td>
      </tr>
      
  <?php
    if (PRODUCT_INFO_QUANTITY == '1') {
  ?>
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_quantity'); ?></td>
        <td id="productInfoQty"><?php echo $osC_Product->getQuantity() . ' ' . $osC_Product->getUnitClass(); ?></td>
      </tr>
  <?php
    }

    if (PRODUCT_INFO_MOQ == '1') {
  ?>
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_moq'); ?></td>
        <td><?php echo $osC_Product->getMOQ() . ' ' . $osC_Product->getUnitClass(); ?></td>
      </tr>
  <?php
    }

    if (PRODUCT_INFO_ORDER_INCREMENT == '1') {
  ?>
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_order_increment'); ?></td>
        <td><?php echo $osC_Product->getOrderIncrement() . ' ' . $osC_Product->getUnitClass(); ?></td>
      </tr>
  <?php
    }
    
    if ($osC_Product->isDownloadable() && $osC_Product->hasSampleFile()) {
  ?>
      <tr>  
        <td class="label"><?php echo $osC_Language->get('field_sample_url'); ?></td>
        <td><?php echo osc_link_object(osc_href_link(FILENAME_DOWNLOAD, 'type=sample&id=' . $osC_Product->getID()), $osC_Product->getSampleFile()); ?></td>
      </tr>     
  <?php
    }

    if ($osC_Product->hasURL()) {
  ?>
      <tr>
        <td colspan="2"><?php echo sprintf($osC_Language->get('go_to_external_products_webpage'), osc_href_link(FILENAME_REDIRECT, 'action=url&goto=' . urlencode($osC_Product->getURL()), 'NONSSL', null, false)); ?></td>
      </tr>
      
  <?php
    }
  
    if ($osC_Product->getDateAvailable() > osC_DateTime::getNow()) {
  ?>
      <tr>  
        <td colspan="2" align="center"><?php echo sprintf($osC_Language->get('date_availability'), osC_DateTime::getLong($osC_Product->getDateAvailable())); ?></td>
      </tr>
  <?php
    }
  ?>
      
  <?php
    if ($osC_Product->hasAttributes()) {
      $attributes = $osC_Product->getAttributes();
      
      foreach($attributes as $attribute) {
  ?>
        <tr>          
          <td class="label" valign="top"><?php echo $attribute['name']; ?>:</td>
          <td><?php echo $attribute['value']; ?></td>
        </tr>
  <?php
    }
  }
  ?>  
  
   <?php
    if ($osC_Product->getAverageReviewsRating() > 0) {
  ?>  
      <tr>      
        <td class="label"><?php echo $osC_Language->get('average_rating'); ?></td>
        <td><?php echo osc_image(DIR_WS_IMAGES . 'stars_' . $osC_Product->getAverageReviewsRating() . '.png', sprintf($osC_Language->get('rating_of_5_stars'), $osC_Product->getAverageReviewsRating())); ?></td>
      </tr>
  <?php
    }
  ?>
        
  <?php
    if ($osC_Product->isGiftCertificate()) {
      if ($osC_Product->isOpenAmountGiftCertificate()) {
  ?>
      <tr>      
        <td class="label"><?php echo $osC_Language->get('field_gift_certificate_amount'); ?></td>
        <td><?php echo osc_draw_input_field('gift_certificate_amount', $osC_Product->getOpenAmountMinValue(), 'size="18"'); ?></td>
      </tr>
  <?php
    }
  ?>
      <tr>      
        <td class="label"><?php echo $osC_Language->get('field_senders_name'); ?></td>
        <td><?php echo osc_draw_input_field('senders_name', null, 'size="18"'); ?></td>
      </tr>
  <?php
    if ($osC_Product->isEmailGiftCertificate()) {
  ?>
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_senders_email'); ?></td>
        <td><?php echo osc_draw_input_field('senders_email', null, 'size="18"'); ?></td>
      </tr>
  <?php
    }
  ?>        
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_recipients_name'); ?></td>
        <td><?php echo osc_draw_input_field('recipients_name', null, 'size="18"'); ?></td>
      </tr>
  <?php
    if ($osC_Product->isEmailGiftCertificate()) {
  ?>  
      <tr>      
        <td class="label"><?php echo $osC_Language->get('field_recipients_email'); ?></td>
        <td><?php echo osc_draw_input_field('recipients_email', null, 'size="18"'); ?></td>
      </tr>
  <?php
    }
  ?>
  
      <tr>          
        <td class="label" valign="top"><?php echo $osC_Language->get('fields_gift_certificate_message'); ?></td>
        <td><?php echo osc_draw_textarea_field('message', null, 15, 2); ?></td>
      </tr>
  <?php
  }
  ?>

  <?php 
      if ($osC_Product->hasVariants()) {
        $combobox_array = $osC_Product->getVariantsComboboxArray();

        foreach ($combobox_array as $groups_name => $combobox) {
          echo '<tr class="variantCombobox">
                 <td class="label" valign="top">' . $groups_name . ':</td>
                 <td>' . $combobox . '</td>
               </tr>';          
        }
      }
   ?>
      
      <tr>
        <td colspan="2" align="left" valign="top" style="padding-top: 15px" id="shoppingCart">
          <?php
            echo '<b>' . $osC_Language->get('field_short_quantity') . '</b>&nbsp;' . osc_draw_input_field('quantity', $osC_Product->getMOQ(), 'size="3"') . '&nbsp;' . osc_draw_image_submit_button('button_in_cart.gif', $osC_Language->get('button_add_to_cart'), 'style="vertical-align:middle;" class="ajaxAddToCart" id="ac_productsinfo_' . osc_get_product_id($osC_Product->getID()) . '"');
          ?>
        </td>
      </tr>
      
      <tr>
        <td colspan="2" align="center" id = "shoppingAction">
          <?php
            if ($osC_Template->isInstalled('compare_products', 'boxes')) {
              echo osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params() . '&cid=' . $osC_Product->getID() . '&' . '&action=compare_products_add'), $osC_Language->get('add_to_compare'), 'class="compare-products"') . '&nbsp;<span>|</span>&nbsp;';
            }
            
            echo osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $osC_Product->getID() . '&action=wishlist_add'), $osC_Language->get('add_to_wishlist'), 'class="wishlist"');
          ?>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <p class="shortDescription"><?php echo $osC_Product->getShortDescription(); ?></p>
        </td>
      </tr>
    </table>
    </form>
    <div style="clear: both;"></div>
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

  <div id="productInfoTab" class="clearfix">
    <?php
      if ($osC_Product->getDescription()) {
        echo '<a tab="tabDescription" href="javascript:void(0);">' . $osC_Language->get('section_heading_products_description') . '</a>'; 
      }
      
      if ($osC_Services->isStarted('reviews')) {
        echo '<a tab="tabReviews" href="javascript:void(0);">' . $osC_Language->get('section_heading_reviews') . '(' . $osC_Reviews->getTotal($osC_Product->getID()) . ')</a>';
      }
      
      if ($osC_Product->hasQuantityDiscount()) {
        echo '<a tab="tabQuantityDiscount" href="javascript:void(0);">' . $osC_Language->get('section_heading_quantity_discount') . '</a>';         
      }
      
      if ($osC_Product->hasAttachments()) {
        echo '<a tab="tabAttachments" href="javascript:void(0);">' . $osC_Language->get('section_heading_products_attachments') . '</a>'; 
      }
      
      if ($osC_Product->hasAccessories()) {
        echo '<a tab="tabAccessories" href="javascript:void(0);">' . $osC_Language->get('section_heading_products_accessories') . '</a>'; 
      }
    ?>
  </div> 
      
   <?php if ($osC_Product->getDescription()) {?>
      <div id="tabDescription">
        <div class="moduleBox">
          <div class="content"><?php echo $osC_Product->getDescription(); ?></div>
        </div>
      </div>
    <?php  } ?>
    
    <?php if ($osC_Services->isStarted('reviews')) { ?>
    
      <div id="tabReviews">
        <div class="moduleBox">
          <div class="content">
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
              <p><label for="author_name"><strong><?php echo $osC_Language->get('field_review_author'); ?></strong></label><input type="text" name="author_name" id="author_name" value="<?php echo $osC_Customer->isLoggedOn() ? $osC_Customer->getName() : (isset($_SESSION['review_author_name']) ? $_SESSION['review_author_name'] : ''); ?>" /></p>
              
              <?php
                $ratings = osC_Reviews::getCategoryRatings($osC_Product->getCategoryID());
                if (sizeof($ratings) == 0) {
              ?>
                <p><label><?php echo '<strong>' . $osC_Language->get('field_review_rating') . '</strong></label>' . $osC_Language->get('review_lowest_rating_title') . ' ' . osc_draw_radio_field('rating', array('1', '2', '3', '4', '5')) . ' ' . $osC_Language->get('review_highest_rating_title'); ?></p>
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
                  <?php echo osc_draw_image_submit_button('submit_reviews.gif', $osC_Language->get('submit_reviews')); ?>
                </div>
              
              </form>
            <?php } ?>
          </div>  
        </div>
      </div>
    <?php
      }
    ?>
    
    <?php  if ($osC_Product->hasQuantityDiscount()) { ?>
      <div id="tabQuantityDiscount">
        <div class="moduleBox">
          <div class="content"><?php echo $osC_Product->renderQuantityDiscountTable(); ?></div>
        </div>
      </div>
    <?php } ?>
    
    <?php 
    if ($osC_Product->hasAttachments()) {
      $attachments = $osC_Product->getAttachments();
    ?>
    <div id="tabAttachments">
      <div class="moduleBox">
        <div class="content">
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
      </div>
    </div>
   <?php }?>

<?php 
    if ($osC_Product->hasAccessories()) {
      $accessories = $osC_Product->getAccessories();
   ?>
    <div id="tabAccessories">
      <div class="moduleBox">
        <div class="content">
          <?php
            foreach ($accessories as $accessory) {
              $product = new osC_Product($accessory); 
          ?>
          <div class="accessories">
            <div class="image"><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $accessory), $osC_Image->show($product->getImage(), $product->getTitle())); ?></div>
            <div class="desc">
              <h6><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $accessory), $product->getTitle()); ?></h6>
              <p><?php echo $product->getShortDescription(); ?></p>
            </div>
          </div>
          <div style="clear: both"></div>
          <?php } ?>
        </div>
      </div>
    </div>
   <?php } ?>

<div style="clear: both;"></div>

<script type="text/javascript" src="includes/javascript/tab_panel.js"></script>
<script type="text/javascript" src="includes/javascript/reviews.js"></script>
<script type="text/javascript" src="ext/mojozoom/mojozoom.js"></script>

<?php if ($osC_Product->hasVariants()) { ?>
  <script type="text/javascript" src="includes/javascript/variants.js"></script>
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
    combVariants: $$('tr.variantCombobox select'),
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
  
  //tab panel
  new TabPanel({panel: $('productInfoTab'), activeTab: '<?php echo (isset($_GET['tab']) && !empty($_GET['tab']) ) ? $_GET['tab'] : ''; ?>'});
  
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
