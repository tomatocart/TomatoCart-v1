<?php
/*
  $Id: index.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $osC_Language->getTextDirection(); ?>" xml:lang="<?php echo $osC_Language->getCode(); ?>" lang="<?php echo $osC_Language->getCode(); ?>">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $osC_Language->getCharacterSet(); ?>" />
<link rel="shortcut icon" href="templates/<?php echo $osC_Template->getCode(); ?>/images/tomatocart.ico" type="image/x-icon" />

<title><?php echo ($osC_Template->hasMetaPageTitle() ? $osC_Template->getMetaPageTitle() . ' - ' : '') . STORE_NAME; ?></title>
<base href="<?php echo osc_href_link(null, null, 'AUTO', false); ?>" />

<?php if ($osC_Services->isStarted('debug') && defined('SERVICE_DEBUG_SHOW_CSS_JAVASCRIPT') && SERVICE_DEBUG_SHOW_CSS_JAVASCRIPT == 1) { ?>
<link rel="stylesheet" type="text/css" href="templates/<?php echo $osC_Template->getCode(); ?>/stylesheet.css" />
<link rel="stylesheet" type="text/css" href="ext/autocompleter/Autocompleter.css" />
<?php }else {?>
<link rel="stylesheet" type="text/css" href="templates/<?php echo $osC_Template->getCode(); ?>/all.min.css" />
<?php } ?>

<?php
  if ($osC_Template->hasPageTags()) {
    echo $osC_Template->getPageTags();
  }

  if ($osC_Template->hasHeaderJavascript()) {
    $osC_Template->getHeaderJavascript();
  }
  
  if ($osC_Template->hasStyleSheet()) {
    $osC_Template->getStyleSheet();
  }
  
  /**
   * general the rel_canonical link to remove the duplication content
   * [#123]Two Different SEO link for one product
   */
  if (isset($osC_Template->rel_canonical)) {
    echo $osC_Template->rel_canonical;
  }
?>

<meta name="Generator" content="TomatoCart" />

<?php
  $content_left = $osC_Template->getBoxGroup('left');
  $content_right = $osC_Template->getBoxGroup('right');
  
  if (osc_empty($content_left) && osc_empty($content_right)) {
?>
<style type="text/css">#pageContent, #pageBlockLeft{width:960px;}</style>
<?php
  }else if (osc_empty($content_left)) {
?>
<style type="text/css">#pageContent {width: 745px;}</style>
<?php
  }else if (osc_empty($content_right)) {
?>
<style type="text/css">#pageContent {width: 745px;}#pageBlockLeft{width:960px;}</style>
<?php
  }
?>
</head>
<body>
<?php
  if ($osC_Template->hasPageHeader()) {
?>

<div id="pageHeader">
  <div id="headerBar">
    <ul>
      <li>
        <?php 
	        if ($toC_Wishlist->hasContents()) {
						$wishlists_products = $toC_Wishlist->getProducts();
						
						echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'), $osC_Language->get('my_wishlist') . ' <strong>(' . count($wishlists_products) . ')</strong>');
					}else {
					  echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'), $osC_Language->get('my_wishlist'));
					}
				?>
      </li>
    <?php if ($osC_Customer->isLoggedOn()) { ?>
      <li>
        <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'logoff', 'SSL'), $osC_Language->get('logoff')); ?>
      </li>
    <?php } else { ?>
      <li>
        <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL'), $osC_Language->get('login')); ?>
      </li>
       <li>
        <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'create', 'SSL'), $osC_Language->get('create_account')); ?>
      </li>
    <?php } ?>
    <?php if ((MAINTENANCE_MODE == 1) && isset($_SESSION['admin'])) { ?>
      <li id="admin_logout">
        <?php echo osc_link_object(osc_href_link(FILENAME_DEFAULT, 'maintenance=logoff', 'SSL'), $osC_Language->get('admin_logout')); ?>
      </li>
    <?php } ?>
      <li id="bookmark"></li>    
      <li class="cart">
        <?php echo osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), '<span id="popupCart">' . osc_image('templates/' . $osC_Template->getCode() . '/images/shopping_cart_icon.png') . '<span id="popupCartItems">' . $osC_ShoppingCart->numberOfItems() . '</span>' . '<span class="cartText cartCallpased">' . $osC_Language->get('text_items') . '</span></span>') ; ?>
      </li>

    </ul>
    <?php
      echo osc_link_object(osc_href_link(FILENAME_DEFAULT), osc_image($osC_Template->getLogo(), STORE_NAME), 'id="siteLogo"');
    ?>
  </div>
  
  <div id="navigationBar">
    <div id="navigationInner">
      <ul id="navigation">
        <?php
          echo '<li ' . ($osC_Template->getGroup() == 'index' && $osC_Template->getModule() == 'index' ? 'class="navVisited"' : null) . '><span class="navLeftHook">&nbsp;</span>' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'index'), $osC_Language->get('home')) . '<span class="navHoverDownHook">&nbsp;</span><span class="navRightHook">&nbsp;</span></li>' .
               '<li ' . ($osC_Template->getGroup() == 'products' && $osC_Template->getModule() == 'new' ? 'class="navVisited"' : null) . '><span class="navLeftHook">&nbsp;</span>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'new'), $osC_Language->get('new_products')) . '<span class="navHoverDownHook">&nbsp;</span><span class="navRightHook">&nbsp;</span></li>';
  
          if ($osC_Customer->isLoggedOn()) {
            echo '<li><span class="navLeftHook"></span>' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'logoff', 'SSL'), $osC_Language->get('sign_out')) . '<span class="navHoverDownHook">&nbsp;</span><span class="navRightHook">&nbsp;</span></li>';
          }
  
          echo '<li ' . ($osC_Template->getGroup() == 'account' ? 'class="navVisited"' : null) . '><span class="navLeftHook">&nbsp;</span>' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), $osC_Language->get('my_account')) . '<span class="navHoverDownHook">&nbsp;</span><span class="navRightHook">&nbsp;</span></li>' .
               '<li ' . ($osC_Template->getGroup() == 'checkout' ? 'class="navVisited"' : null) . '><span class="navLeftHook">&nbsp;</span>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'), $osC_Language->get('checkout')) . '<span class="navHoverDownHook">&nbsp;</span><span class="navRightHook">&nbsp;</span></li>' .
               '<li ' . ($osC_Template->getGroup() == 'info' && $osC_Template->getModule() == 'contact' ? 'class="navVisited"' : null) . '><span class="navLeftHook">&nbsp;</span>' . osc_link_object(osc_href_link(FILENAME_INFO, 'contact'), $osC_Language->get('contact_us')) . '<span class="navHoverDownHook">&nbsp;</span><span class="navRightHook">&nbsp;</span></li>';
        ?>
      </ul>
      
      <div style="float: right;width: 206px">
        <form name="search" action="<?php echo osc_href_link(FILENAME_SEARCH, null, 'NONSSL', false);?>" method="get">
          <p class="keywords"><?php echo osc_draw_input_field('keywords', null, 'maxlength="20"') ?></p>
          <p><input type="image" src="<?php echo 'templates/' . $osC_Template->getCode() . '/images/button_quick_find.png'; ?>" alt="<?php echo $osC_Language->get('box_search_heading'); ?>" title="<?php echo $osC_Language->get('box_search_heading'); ?>" id="quickSearch" /><?php echo osc_draw_hidden_session_id_field(); ?></p>
        </form>
      </div>  
    </div>
  </div>
  <?php
      if ($osC_Services->isStarted('breadcrumb')) {
  ?>
      <div id="breadcrumbPath">
      <?php
        echo $breadcrumb->trail(' &raquo; ');
      ?>
        <div id="navLanguages">
          <?php
            foreach ($osC_Language->getAll() as $value) {
              echo osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('language', 'currency')) . '&language=' . $value['code'], 'AUTO'), $osC_Language->showImage($value['code']));
            }
          ?>
        </div>
      </div>
  <?php
    }
  ?>
</div>

<?php
}
?>

<div id="slideShow">
  <?php 
    $slideshow = $osC_Template->getContentGroup('slideshow');
    if ($slideshow !== false) {
      echo $slideshow;
    }
  ?>
</div>

<div id="pageWrapper">
  <!--  Database Connection failed  -->
  <?php 
    if ($messageStack->size('db_error') > 0) {
  ?>
  <div><?php echo  $messageStack->output('db_error'); ?></div>
  <?php
    }
  ?>
  
  <div id="pageBlockLeft">
  <?php
    if (!empty($content_left)) {
  ?>

    <div id="pageColumnLeft">
      <div class="boxGroup">
      <?php
          echo $content_left;
      ?>
      </div>
    </div>

  <?php
    }
  ?>
  
    <div id="pageContent">

      <?php
        if ($messageStack->size('header') > 0) {
          echo $messageStack->output('header');
        }

        if ($osC_Template->hasPageContentModules()) {
          foreach ($osC_Services->getCallBeforePageContent() as $service) {
            $$service[0]->$service[1]();
          }

          $content_before = $osC_Template->getContentGroup('before');
          if (!empty($content_before)) {
            echo $content_before;
          }
        }

        if ($osC_Template->getCode() == DEFAULT_TEMPLATE) {
          include('templates/' . $osC_Template->getCode() . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename());
        } else {
          if (file_exists('templates/' . $osC_Template->getCode() . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename())) {
            include('templates/' . $osC_Template->getCode() . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename());
          } else {
            include('templates/' . DEFAULT_TEMPLATE . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename());
          }
        }
      ?>

      <div style="clear: both;"></div>

      <?php
        if ($osC_Template->hasPageContentModules()) {
          foreach ($osC_Services->getCallAfterPageContent() as $service) {
            $$service[0]->$service[1]();
          }
        
          $content_after = $osC_Template->getContentGroup('after');
          if (!empty($content_after)) {
            echo $content_after;
          }
        }
      ?>

    </div>
  </div>

  <?php
    if (!empty($content_right)) {
  ?>
      <div id="pageColumnRight">
        <div class="boxGroup">
      <?php
          echo $content_right;
      ?>
        </div>
      </div>

  <?php
    }

    unset($content_left);
    unset($content_right);
  ?>
</div>

<?php 
  if ($osC_Template->hasPageFooter()) {
?>

  <div id="pageFooter">
    <ul>
      <?php
        echo '<li>' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'index'), $osC_Language->get('home')) . '<span>|</span></li>' .
             '<li>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'specials'), $osC_Language->get('specials')) . '<span>|</span></li>' .
             '<li>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'new'), $osC_Language->get('new_products')) . '<span>|</span></li>' .
             '<li>' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), $osC_Language->get('my_account')) . '<span>|</span></li>' .
             '<li>' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'), $osC_Language->get('my_wishlist')) . '<span>|</span></li>' .     
             '<li>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $osC_Language->get('cart_contents')) . '<span>|</span></li>' .
             '<li>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'), $osC_Language->get('checkout')) . '<span>|</span></li>' .
             '<li>' . osc_link_object(osc_href_link(FILENAME_INFO, 'contact'), $osC_Language->get('contact_us')) . '<span>|</span></li>'.
             '<li>' . osc_link_object(osc_href_link(FILENAME_INFO, 'guestbook'), $osC_Language->get('guest_book')) . '<span>|</span></li>' .
             '<li>' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'rss'), osc_image(DIR_WS_IMAGES . 'rss16x16.png') . '<span>RSS</span>') . '</li>';
      ?>
    </ul>
    <div style="clear:both"></div>
    <p style="margin: 3px;">
      <?php
        echo sprintf($osC_Language->get('footer'), date('Y'), osc_href_link(FILENAME_DEFAULT), STORE_NAME);
      ?>
    </p>
  </div>
  
<?php 
    if ($osC_Services->isStarted('banner') && $osC_Banner->exists('468x60')) {
      echo '<p align="center">' . $osC_Banner->display() . '</p>';
    }
  }
?>

<?php
  if ($osC_Template->hasJavascript()) {
    $osC_Template->getJavascript();
  }
?>

<?php if ($osC_Services->isStarted('debug') && defined('SERVICE_DEBUG_SHOW_CSS_JAVASCRIPT') && SERVICE_DEBUG_SHOW_CSS_JAVASCRIPT == 1) { ?>
<script type="text/javascript" src="includes/javascript/pop_dialog.js"></script>
<script type="text/javascript" src="ext/autocompleter/Autocompleter.js"></script>
<script type="text/javascript" src="ext/autocompleter/Autocompleter.Request.js"></script>
<script type="text/javascript" src="ext/autocompleter/Observer.js"></script>
<script type="text/javascript" src="includes/javascript/auto_completer.js"></script>
<script type="text/javascript" src="includes/javascript/popup_cart.js"></script>
<script type="text/javascript" src="includes/javascript/bookmark.js"></script>
<?php }else { ?>
<script type="text/javascript" src="templates/<?php echo $osC_Template->getCode(); ?>/javascript/all.min.js"></script>
<?php }?>

<script type="text/javascript">
  window.addEvent('domready', function() {
    new PopupCart({
      template: '<?php echo $osC_Template->getCode(); ?>',
      enableDelete: '<?php 
      	$box_modules = $osC_Template->osC_Modules_Boxes->_modules;
      	
      	$flag = 'yes';
      	foreach($box_modules as $box_group => $modules) {
      		foreach ($modules as $module_code) {
      			if ($module_code == 'shopping_cart') {
      				$flag = 'no';
      				
      				break 2;
      			}
      		}
      	}
      	
      	echo $flag;
      ?>',
      <?php 
      	if (defined('ENABLE_CONFIRMATION_DIALOG') && (ENABLE_CONFIRMATION_DIALOG == '1')) {
      ?>
      dlgConfirmStatus: true,
      <?php 
				}else {
      ?>
      dlgConfirmStatus: false,
      <?php 
				}
      ?>
      sessionName: '<?php echo $osC_Session->getName(); ?>',
      sessionId: '<?php echo $osC_Session->getID(); ?>',
      error_sender_name_empty: '<?php echo $osC_Language->get("error_sender_name_empty"); ?>',
      error_sender_email_empty: '<?php echo $osC_Language->get("error_sender_email_empty"); ?>',
      error_recipient_name_empty: '<?php echo $osC_Language->get("error_recipient_name_empty"); ?>',
      error_recipient_email_empty: '<?php echo $osC_Language->get("error_recipient_email_empty"); ?>',
      error_message_empty: '<?php echo $osC_Language->get("error_message_empty"); ?>',
      error_message_open_gift_certificate_amount: '<?php echo $osC_Language->get('error_message_open_gift_certificate_amount'); ?>'
    });
    
    new TocAutoCompleter('keywords', {
      sessionName: '<?php echo $osC_Session->getName(); ?>',
      sessionId: '<?php echo $osC_Session->getID(); ?>',
      template: '<?php echo $osC_Template->getCode(); ?>',
      maxChoices: <?php echo defined('MAX_DISPLAY_AUTO_COMPLETER_RESULTS') ? MAX_DISPLAY_AUTO_COMPLETER_RESULTS : 10;?>,
			width: <?php echo defined('WIDTH_AUTO_COMPLETER') ? WIDTH_AUTO_COMPLETER : 400; ?>,
      moreBtnText: '<?php echo $osC_Language->get('button_get_more'); ?>',
      imageGroup: '<?php echo defined('IMAGE_GROUP_AUTO_COMPLETER') ? IMAGE_GROUP_AUTO_COMPLETER : 'thumbnail'; ?>',
    });
  });
  new TocBookmark({
  	bookmark: 'bookmark',
  	text: '<?php echo $osC_Language->get('bookmark'); ?>',
    img: '<?php echo 'images/bookmark.png'; ?>'
  });  
</script>

<?php 
  if ($osC_Services->isStarted('google_analytics')) {
    echo SERVICES_GOOGLE_ANALYTICS_CODE;
  }
?>
</body>
</html>