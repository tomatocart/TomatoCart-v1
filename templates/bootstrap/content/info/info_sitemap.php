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

  $osC_CategoryTree->reset();
  $osC_CategoryTree->setShowCategoryProductCount(false);
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<div class="moduleBox" id="sitemap">

	<div class="content btop">
		<div class="row-fluid">
            <div class="span6">
                <?php echo $osC_CategoryTree->buildTree(); ?>
            </div>
            <div class="span6">
                <ul>
                    <li>
                        <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), $osC_Language->get('sitemap_account')); ?>
                        <ul>
                            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'edit', 'SSL'), $osC_Language->get('sitemap_account_edit')); ?></li>
                            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'address_book', 'SSL'), $osC_Language->get('sitemap_address_book')); ?></li>
                            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'orders', 'SSL'), $osC_Language->get('sitemap_account_history')); ?></li>
                            <li><?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'newsletters', 'SSL'), $osC_Language->get('sitemap_account_notifications')); ?></li>
                        </ul>
                    </li>
                    <li><?php echo osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $osC_Language->get('sitemap_shopping_cart')); ?></li>
                    <li><?php echo osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'shipping', 'SSL'), $osC_Language->get('sitemap_checkout_shipping')); ?></li>
                    <li><?php echo osc_link_object(osc_href_link(FILENAME_SEARCH), $osC_Language->get('sitemap_advanced_search')); ?></li>
                    <li><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'new'), $osC_Language->get('sitemap_products_new')); ?></li>
                    <li><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'specials'), $osC_Language->get('sitemap_specials')); ?></li>
                    <li><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'reviews'), $osC_Language->get('sitemap_reviews')); ?></li>
                    <li><?php echo osc_link_object(osc_href_link(FILENAME_INFO), $osC_Language->get('box_information_heading')); ?>
			          <ul>
			            <?php
			                while($Qinformation_listing->next()) {
			            ?>
			            <li><?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qinformation_listing->valueInt('articles_id')), $Qinformation_listing->value('articles_name')); ?></li>
			            <?php
			                }
			            ?>
			          </ul>
			        </li>
					<?php if ($articles_categories !== null): ?>
						<?php foreach ($articles_categories as $article_category): ?>
						<li>
							<?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'articles_categories&articles_categories_id=' . $article_category['articles_categories_id']), $article_category['articles_categories_name']); ?>
							<?php if (count($article_category['articles']) > 0): ?>
							<ul>
								<?php foreach ($article_category['articles'] as $article): ?>
								<li><?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $article['articles_id']), $article['articles_name']); ?></li>
								<?php endforeach; ?>
							</ul>
							<?php endif; ?>
						</li>
				        <?php endforeach; ?>
			        <?php endif; ?>
			        <li>
			          <?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'faqs'), $osC_Language->get('info_faqs_heading'));?>
			          <ul>
			            <?php
			                while($Qfaqs_listing->next()) {
			            ?>
			            <li><?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'faqs&faqs_id=' . $Qfaqs_listing->valueInt('faqs_id')), $Qfaqs_listing->value('faqs_question')); ?></li>
			            <?php
			                }
			            ?>
			          </ul>
			        </li>
                </ul>
            </div>
		</div>
	</div>
</div>

<div class="submitFormButtons" style="text-align: right;">
	<a class="btn btn-small" href="<?php echo osc_href_link(FILENAME_DEFAULT); ?>"><i class="icon-chevron-right icon-white"></i> <?php echo $osC_Language->get('button_continue'); ?></a>
</div>
