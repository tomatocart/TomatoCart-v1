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

//include general helper
require_once 'helper.php';
?>
<!DOCTYPE html>
<html lang="<?php echo get_lang_code(); ?>">
<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="templates/<?php echo $osC_Template->getCode(); ?>/images/favicon.png" />
    <title><?php echo ($osC_Template->hasMetaPageTitle() ? $osC_Template->getMetaPageTitle() . ' - ' : '') . STORE_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	
    <base href="<?php echo osc_href_link(null, null, 'AUTO', false); ?>" />
    
    <?php if ($osC_Services->isStarted('debug') && defined('SERVICE_DEBUG_SHOW_CSS_JAVASCRIPT') && SERVICE_DEBUG_SHOW_CSS_JAVASCRIPT == 1) { ?>
    <link rel="stylesheet" type="text/css" href="templates/<?php echo $osC_Template->getCode(); ?>/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="templates/<?php echo $osC_Template->getCode(); ?>/css/stylesheet.css" />
    <link rel="stylesheet" type="text/css" href="templates/<?php echo $osC_Template->getCode(); ?>/css/stylesheet.responsive.css" />
    <link rel="stylesheet" type="text/css" href="ext/autocompleter/Autocompleter.css" />
    <?php } else { ?>
    <link rel="stylesheet" type="text/css" href="templates/<?php echo $osC_Template->getCode(); ?>/css/all.min.css" />
    <?php } ?>
    
    <!-- touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="templates/<?php echo $osC_Template->getCode(); ?>/images/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="templates/<?php echo $osC_Template->getCode(); ?>/images/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="templates/<?php echo $osC_Template->getCode(); ?>/images/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="templates/<?php echo $osC_Template->getCode(); ?>/images/apple-touch-icon-57-precomposed.png">

<?php
    if ($osC_Template->hasPageTags()) {
        echo $osC_Template->getPageTags();
    }

    output_javascripts();
  
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
</head>
<body>
<?php
  if ($osC_Template->hasPageHeader()) {
?>
    <div id="pageHeader">
        <div class="container">
            <div class="row-fluid">
                <div class="span4 logo"><?php echo site_logo(); ?></div>
                <div class="span8">
                	<div class="top-nav clearfix">
                        <ul>
                            <li>
                            	<?php 
                            		if ($toC_Wishlist->hasContents()) {
																	$wishlists_products = $toC_Wishlist->getProducts();
																	
																	echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'), $osC_Language->get('my_wishlist') . ' <span class="label label-info">' . count($wishlists_products) . '</span>');
                            		}else {
                            		  echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'), $osC_Language->get('my_wishlist'));
                            		}
                            	?>
                            </li>
                            <?php 
                                if (!$osC_Customer->isLoggedOn()) { 
                            ?>
                            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'create', 'SSL'), $osC_Language->get('create_account')); ?></li>
                            <?php 
                                }
                            ?>
                            <?php 
                                if ((MAINTENANCE_MODE == 1) && isset($_SESSION['admin'])) { 
                            ?>
                            <li id="admin_logout"><?php echo osc_link_object(osc_href_link(FILENAME_DEFAULT, 'maintenance=logoff', 'SSL'), $osC_Language->get('admin_logout')); ?></li>
                            <?php 
                                } 
                            ?>
                            <li id="bookmark"><i class="icon-star"></i></li>    
                            <li class="cart"><?php echo osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), '<span id="popupCart"><i class="icon-shopping-cart"></i> ' . '<span id="popupCartItems">' . $osC_ShoppingCart->numberOfItems() . '</span>' . '<span>' . $osC_Language->get('text_items') . '</span></span>') ; ?></li>
                        </ul>
                    </div>
                    <div class="main-nav">
                        <ul>
                            <li class="visible-desktop"><?php echo osc_link_object(osc_href_link(FILENAME_DEFAULT, 'index'), $osC_Language->get('home')); ?></li>
                            <li><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'new'), $osC_Language->get('new_products')); ?></li>
                            <?php 
                                if ($osC_Customer->isLoggedOn()) { 
                            ?>
                            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'logoff', 'SSL'), $osC_Language->get('logoff')); ?></li>
                            <?php 
                                } else { 
                            ?>
                            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'login', 'SSL'), $osC_Language->get('login')); ?></li>
                            <?php 
                                } 
                            ?>
                            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), $osC_Language->get('my_account')); ?></li>
                            <li><?php echo osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'), $osC_Language->get('checkout')); ?></li>
                            <li><?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'contact'), $osC_Language->get('contact_us')); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- BEGIN: Navigation -->
    <div class="container">
    	<div class="navbar navbar-inverse">
    		<div class="navbar-inner">
                <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                </button>
                <form name="search" method="get" action="<?php echo osc_href_link(FILENAME_SEARCH, null, 'NONSSL', false);?>" class="navbar-search pull-right">
                    <input id="keywords" type="text" name="keywords" class="search-query keywords" placeholder="Search" />
                    <div class="icon-search"></div>
                </form>
    			<div class="nav-collapse collapse">
        			<?php echo build_categories_dropdown_menu(); ?>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Navigation -->  
    
    <?php
      if ($osC_Services->isStarted('breadcrumb')) {
    ?>
    <!-- BEGIN: Breadcrumb -->
    <div class="container">
        <div class="breadcrumb hidden-phone">
            <?php
                echo $breadcrumb->trail(' &raquo; ');
            ?>
        
            <div class="pull-right flags">
              <?php
                foreach ($osC_Language->getAll() as $value) {
                  echo osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('language', 'currency')) . '&language=' . $value['code'], 'AUTO'), $osC_Language->showImage($value['code']));
                }
              ?>
            </div>   
        </div>    
    </div>
    <!-- END: Breadcrumb -->
    <?php
    }
    ?>


<?php
}
?>
<!--  slideshow  -->
<?php 
    $slideshow = $osC_Template->getContentGroup('slideshow');
    if (!empty($slideshow)) {
?>
    <div id="slideShow" class="container">
    <?php 
        echo $slideshow;
    ?>
    </div>
<?php 
    }
?>
<!--  END: slideshow  -->

<!--  Database Connection failed  -->
<?php 
  if ($messageStack->size('db_error') > 0) {
?>
<div class="container"><?php echo  $messageStack->output('db_error'); ?></div>
<?php
  }
?>
<!--  END: Database Connection failed  -->

<div id="pageWrapper" class="container">
	<div class="row-fluid">
        <?php
            $content_left = $osC_Template->getBoxGroup('left');
            
            if (!empty($content_left)) {
        ?>
        <div id="content-left" class="span3 hidden-phone"><?php echo $content_left; ?></div> 
        <?php
            }
        ?>
        
        <div  id="content-center" class="span<?php echo 12 - (!empty($content_left) ? 3 : 0); ?>">
            <?php
                if ($messageStack->size('header') > 0) {
                  echo $messageStack->output('header');
                }
            ?>
            
            <!--  before module group  -->
            <?php 
                if ($osC_Template->hasPageContentModules()) {
                    foreach ($osC_Services->getCallBeforePageContent() as $service) {
                        $$service[0]->$service[1]();
                    }
                    
                    $content_before = $osC_Template->getContentGroup('before');
                    if (!empty($content_before)) {
                        echo $content_before;
                    }
                }
            ?>
            <!--  END: before module group  -->
            
            <!--  page body  -->
            <?php 
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
            <!--  END: page body  -->
            
            <!--  after module group  -->
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
            <!--  END: after module group  -->
        </div>
        
        <?php
            unset($content_left);
        ?>
	</div>
</div>

<?php 
  if ($osC_Template->hasPageFooter()) {
?>
<!--  BEGIN: Page Footer -->
<div class="container ">
	<div id="pageFooter" class="row-fluid clearfix hidden-phone">
    	<div class="span3">
            <?php 
                $col1 = $osC_Template->getBoxGroup('footer-col-1');
                if (!empty($col1)) {
                    echo $col1; 
                }
            ?>
    	</div>
    	<div class="span3">
            <?php 
                $col2 = $osC_Template->getBoxGroup('footer-col-2');
                if (!empty($col2)) {
                    echo $col2; 
                }
            ?>
    	</div>
    	<div class="span3">
            <?php 
                $col3 = $osC_Template->getBoxGroup('footer-col-3');
                if (!empty($col3)) {
                    echo $col3; 
                }
            ?>
    	</div>
    	<div class="span3">
        	<!-- PayPal Logo --><table style="width: 100%; text-align: center"><tr><td><a href="https://www.paypal.com/webapps/mpp/paypal-popup" title="How PayPal Works" onclick="javascript:window.open('https://www.paypal.com/webapps/mpp/paypal-popup','WIPaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700'); return false;"><img src="templates/<?php echo $osC_Template->getCode(); ?>/images/paypal_solution.jpg" alt="PayPal Acceptance Mark"></a></td></tr></table><!-- PayPal Logo -->
			<!-- PayPal Logo --><table style="width: 100%; text-align: center"><tr><td><a href="https://www.paypal.com/webapps/mpp/paypal-popup" title="How PayPal Works" onclick="javascript:window.open('https://www.paypal.com/webapps/mpp/paypal-popup','WIPaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700'); return false;"><img src="templates/<?php echo $osC_Template->getCode(); ?>/images/paypal_accepting.jpg" alt="Now accepting PayPal"></a></td></tr></table><!-- PayPal Logo -->
    	</div>
    </div>
    <div class="row">
        <ul class="bottomNav">
          <?php
            echo '<li>' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'index'), $osC_Language->get('home')) . '<span>|</span></li>' .
                 '<li>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'specials'), $osC_Language->get('specials')) . '<span>|</span></li>' .
                 '<li>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'new'), $osC_Language->get('new_products')) . '<span>|</span></li>' .
                 '<li>' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), $osC_Language->get('my_account')) . '<span>|</span></li>' .
                 '<li>' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'), $osC_Language->get('my_wishlist')) . '<span>|</span></li>' .     
                 '<li>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $osC_Language->get('cart_contents')) . '<span>|</span></li>' .
                 '<li>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'), $osC_Language->get('checkout')) . '<span>|</span></li>' .
                 '<li>' . osc_link_object(osc_href_link(FILENAME_INFO, 'contact'), $osC_Language->get('contact_us')) . '<span>|</span></li>'.
                 '<li>' . osc_link_object(osc_href_link(FILENAME_INFO, 'guestbook&new'), $osC_Language->get('guest_book')) . '<span>|</span></li>' .
                 '<li style="width: 60px">' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'rss'), osc_image(DIR_WS_IMAGES . 'rss16x16.png') . '<span>RSS</span>') . '</li>';
          ?>
        </ul>
    </div>
    <div class="row">
        <p class="copyright pull-right">
            <?php
                echo sprintf($osC_Language->get('footer'), date('Y'), osc_href_link(FILENAME_DEFAULT), STORE_NAME);
            ?>
        </p>
    </div>
</div>
<!--  END: Page Footer -->
  
<?php 
    if ($osC_Services->isStarted('banner') && $osC_Banner->exists('468x60')) {
      echo '<p align="center">' . $osC_Banner->display() . '</p>';
    }
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
<script type="text/javascript" src="templates/<?php echo $osC_Template->getCode(); ?>/javascript/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="templates/<?php echo $osC_Template->getCode(); ?>/javascript/bootstrap.min.js"></script>
<?php }else { ?>
<script type="text/javascript" src="templates/<?php echo $osC_Template->getCode(); ?>/javascript/all.min.js"></script>
<?php }?>

<script type="text/javascript">
  jQuery.noConflict();
</script>

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
			sessionName: '<?php echo $osC_Session->getName(); ?>',
			sessionId: '<?php echo $osC_Session->getID(); ?>'
    });
    new TocAutoCompleter('keywords', {
        sessionName: '<?php echo $osC_Session->getName(); ?>',
        sessionId: '<?php echo $osC_Session->getID(); ?>',
        template: '<?php echo $osC_Template->getCode(); ?>',
        maxChoices: <?php echo defined('MAX_DISPLAY_AUTO_COMPLETER_RESULTS') ? MAX_DISPLAY_AUTO_COMPLETER_RESULTS : 10;?>,
				width: <?php echo defined('WIDTH_AUTO_COMPLETER') ? WIDTH_AUTO_COMPLETER : 400; ?>,
				moreBtnText: '<?php echo $osC_Language->get('button_get_more'); ?>',
				imageGroup: '<?php echo defined('IMAGE_GROUP_AUTO_COMPLETER') ? IMAGE_GROUP_AUTO_COMPLETER : 'mini'; ?>'
    });
    new TocBookmark({
        bookmark: 'bookmark',
        text: '<?php echo $osC_Language->get('bookmark'); ?>',
        img: '<?php echo 'images/bookmark.png'; ?>'
    });
});
</script>

<script type="text/javascript">
if (typeof jQuery != 'undefined') {
    (function($) {
        $(document).ready(function(){
            $('.carousel').carousel({
                interval: 3000
            }).each(function(index, element) {
            	$(this)[index].slide = null;
            });
        });
    })(jQuery);
}
</script>
<?php 
    if ($osC_Services->isStarted('google_analytics')) {
        echo SERVICES_GOOGLE_ANALYTICS_CODE;
    }
?>
</body>
</html>