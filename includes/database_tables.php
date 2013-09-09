<?php
/*
  $Id: database_tables.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  define('TABLE_ADDRESS_BOOK', DB_TABLE_PREFIX . 'address_book');
  define('TABLE_ADMINISTRATORS', DB_TABLE_PREFIX . 'administrators');
  define('TABLE_ADMINISTRATORS_ACCESS', DB_TABLE_PREFIX . 'administrators_access');
  define('TABLE_ADMINISTRATORS_LOG', DB_TABLE_PREFIX . 'administrators_log');
  define('TABLE_ARTICLES', DB_TABLE_PREFIX . 'articles');
  define('TABLE_ARTICLES_CATEGORIES', DB_TABLE_PREFIX . 'articles_categories');
  define('TABLE_ARTICLES_CATEGORIES_DESCRIPTION', DB_TABLE_PREFIX . 'articles_categories_description');
  define('TABLE_ARTICLES_DESCRIPTION', DB_TABLE_PREFIX . 'articles_description');
  define('TABLE_BANNERS', DB_TABLE_PREFIX . 'banners');
  define('TABLE_BANNERS_HISTORY', DB_TABLE_PREFIX . 'banners_history');
  define('TABLE_CATEGORIES', DB_TABLE_PREFIX . 'categories');
  define('TABLE_CATEGORIES_DESCRIPTION', DB_TABLE_PREFIX . 'categories_description');
  define('TABLE_CATEGORIES_RATINGS', DB_TABLE_PREFIX . 'categories_ratings');
  define('TABLE_CONFIGURATION', DB_TABLE_PREFIX . 'configuration');
  define('TABLE_CONFIGURATION_GROUP', DB_TABLE_PREFIX . 'configuration_group');
  define('TABLE_COUNTER', DB_TABLE_PREFIX . 'counter');
  define('TABLE_COUNTRIES', DB_TABLE_PREFIX . 'countries');
  define('TABLE_COUPONS', DB_TABLE_PREFIX . 'coupons');
  define('TABLE_COUPONS_DESCRIPTION', DB_TABLE_PREFIX . 'coupons_description');
  define('TABLE_COUPONS_REDEEM_HISTORY', DB_TABLE_PREFIX . 'coupons_redeem_history');
  define('TABLE_COUPONS_TO_CATEGORIES', DB_TABLE_PREFIX . 'coupons_to_categories');
  define('TABLE_COUPONS_TO_PRODUCTS', DB_TABLE_PREFIX . 'coupons_to_products');
  define('TABLE_CREDIT_CARDS', DB_TABLE_PREFIX . 'credit_cards');
  define('TABLE_CURRENCIES', DB_TABLE_PREFIX . 'currencies');
  define('TABLE_CUSTOMERS', DB_TABLE_PREFIX . 'customers');
  define('TABLE_CUSTOMERS_BASKET', DB_TABLE_PREFIX . 'customers_basket');
  define('TABLE_CUSTOMERS_CREDITS_HISTORY', DB_TABLE_PREFIX . 'customers_credits_history');
  define('TABLE_CUSTOMERS_GROUPS', DB_TABLE_PREFIX . 'customers_groups');
  define('TABLE_CUSTOMERS_GROUPS_DESCRIPTION', DB_TABLE_PREFIX . 'customers_groups_description');
  define('TABLE_CUSTOMERS_RATINGS', DB_TABLE_PREFIX . 'customers_ratings');
  define('TABLE_CUSTOMIZATION_FIELDS', DB_TABLE_PREFIX . 'customization_fields');
  define('TABLE_CUSTOMIZATION_FIELDS_DESCRIPTION', DB_TABLE_PREFIX . 'customization_fields_description');
  define('TABLE_DEPARTMENTS', DB_TABLE_PREFIX . 'departments');
  define('TABLE_DEPARTMENTS_DESCRIPTION',DB_TABLE_PREFIX . 'departments_description');
  define('TABLE_EMAIL_ACCOUNTS', DB_TABLE_PREFIX . 'email_accounts');
  define('TABLE_EMAIL_FILTERS', DB_TABLE_PREFIX . 'email_filters');
  define('TABLE_EMAIL_FOLDERS', DB_TABLE_PREFIX . 'email_folders');
  define('TABLE_EMAIL_MESSAGES', DB_TABLE_PREFIX . 'email_messages');
  define('TABLE_EMAIL_TEMPLATES', DB_TABLE_PREFIX . 'email_templates');
  define('TABLE_EMAIL_TEMPLATES_DESCRIPTION', DB_TABLE_PREFIX . 'email_templates_description');
  define('TABLE_FAQS', DB_TABLE_PREFIX . 'faqs');
  define('TABLE_FAQS_DESCRIPTION', DB_TABLE_PREFIX . 'faqs_description');
  define('TABLE_GEO_ZONES', DB_TABLE_PREFIX . 'geo_zones');
  define('TABLE_GIFT_CERTIFICATES', DB_TABLE_PREFIX . 'gift_certificates');
  define('TABLE_GUEST_BOOKS', DB_TABLE_PREFIX . 'guest_books');
  define('TABLE_GIFT_CERTIFICATES_REDEEM_HISTORY', DB_TABLE_PREFIX . 'gift_certificates_redeem_history');
  define('TABLE_GOOGLE_ORDERS', DB_TABLE_PREFIX . 'google_orders');
  define('TABLE_LANGUAGES', DB_TABLE_PREFIX . 'languages');
  define('TABLE_LANGUAGES_DEFINITIONS', DB_TABLE_PREFIX . 'languages_definitions');
  define('TABLE_MANUFACTURERS', DB_TABLE_PREFIX . 'manufacturers');
  define('TABLE_MANUFACTURERS_INFO', DB_TABLE_PREFIX . 'manufacturers_info');
  define('TABLE_NEWSLETTERS', DB_TABLE_PREFIX . 'newsletters');
  define('TABLE_NEWSLETTERS_LOG', DB_TABLE_PREFIX . 'newsletters_log');
  define('TABLE_ORDERS', DB_TABLE_PREFIX . 'orders');
  define('TABLE_ORDERS_PRODUCTS', DB_TABLE_PREFIX . 'orders_products');
  define('TABLE_ORDERS_PRODUCTS_CUSTOMIZATIONS', DB_TABLE_PREFIX . 'orders_products_customizations');
  define('TABLE_ORDERS_PRODUCTS_CUSTOMIZATIONS_VALUES', DB_TABLE_PREFIX . 'orders_products_customizations_values');
  define('TABLE_ORDERS_PRODUCTS_VARIANTS', DB_TABLE_PREFIX . 'orders_products_variants');
  define('TABLE_ORDERS_REFUNDS', DB_TABLE_PREFIX . 'orders_refunds');
  define('TABLE_ORDERS_REFUNDS_PRODUCTS', DB_TABLE_PREFIX . 'orders_refunds_products');
  define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', DB_TABLE_PREFIX . 'orders_products_download');
  define('TABLE_ORDERS_RETURNS', DB_TABLE_PREFIX . 'orders_returns');
  define('TABLE_ORDERS_RETURNS_PRODUCTS', DB_TABLE_PREFIX . 'orders_returns_products');
  define('TABLE_ORDERS_RETURNS_STATUS', DB_TABLE_PREFIX . 'orders_returns_status');
  define('TABLE_ORDERS_STATUS', DB_TABLE_PREFIX . 'orders_status');
  define('TABLE_ORDERS_STATUS_HISTORY', DB_TABLE_PREFIX . 'orders_status_history');
  define('TABLE_ORDERS_TOTAL', DB_TABLE_PREFIX . 'orders_total');
  define('TABLE_ORDERS_TRANSACTIONS_HISTORY', DB_TABLE_PREFIX . 'orders_transactions_history');
  define('TABLE_ORDERS_TRANSACTIONS_STATUS', DB_TABLE_PREFIX . 'orders_transactions_status');
  define('TABLE_PRODUCTS', DB_TABLE_PREFIX . 'products');
  define('TABLE_PRODUCTS_ACCESSORIES', DB_TABLE_PREFIX . 'products_accessories');
  define('TABLE_PRODUCTS_ATTACHMENTS', DB_TABLE_PREFIX . 'products_attachments');
  define('TABLE_PRODUCTS_ATTACHMENTS_DESCRIPTION', DB_TABLE_PREFIX . 'products_attachments_description');
  define('TABLE_PRODUCTS_ATTACHMENTS_TO_PRODUCTS', DB_TABLE_PREFIX . 'products_attachments_to_products');
  define('TABLE_PRODUCTS_ATTRIBUTES', DB_TABLE_PREFIX . 'products_attributes');
  define('TABLE_PRODUCTS_ATTRIBUTES_GROUPS', DB_TABLE_PREFIX . 'products_attributes_groups');
  define('TABLE_PRODUCTS_ATTRIBUTES_VALUES', DB_TABLE_PREFIX . 'products_attributes_values');
  define('TABLE_PRODUCTS_DESCRIPTION', DB_TABLE_PREFIX . 'products_description');
  define('TABLE_PRODUCTS_DOWNLOADABLES', DB_TABLE_PREFIX . 'products_downloadables');
  define('TABLE_PRODUCTS_DOWNLOAD_HISTORY', DB_TABLE_PREFIX . 'products_download_history');
  define('TABLE_PRODUCTS_FRONTPAGE', DB_TABLE_PREFIX . 'products_frontpage');
  define('TABLE_PRODUCTS_GIFT_CERTIFICATES', DB_TABLE_PREFIX . 'products_gift_certificates');
  define('TABLE_PRODUCTS_IMAGES', DB_TABLE_PREFIX . 'products_images');
  define('TABLE_PRODUCTS_IMAGES_GROUPS', DB_TABLE_PREFIX . 'products_images_groups');
  define('TABLE_PRODUCTS_NOTIFICATIONS', DB_TABLE_PREFIX . 'products_notifications');
  define('TABLE_PRODUCTS_TO_CATEGORIES', DB_TABLE_PREFIX . 'products_to_categories');
  define('TABLE_PRODUCTS_VARIANTS', DB_TABLE_PREFIX . 'products_variants');
  define('TABLE_PRODUCTS_VARIANTS_ENTRIES', DB_TABLE_PREFIX . 'products_variants_entries');
  define('TABLE_PRODUCTS_VARIANTS_GROUPS', DB_TABLE_PREFIX . 'products_variants_groups');
  define('TABLE_PRODUCTS_VARIANTS_VALUES', DB_TABLE_PREFIX . 'products_variants_values');
  define('TABLE_PRODUCTS_VARIANTS_VALUES_TO_PRODUCTS_VARIANTS_GROUPS', DB_TABLE_PREFIX . 'products_variants_values_to_products_variants_groups');
  define('TABLE_PRODUCTS_XSELL', DB_TABLE_PREFIX . 'products_xsell');
  define('TABLE_POLLS', DB_TABLE_PREFIX . 'polls');
  define('TABLE_POLLS_ANSWERS', DB_TABLE_PREFIX . 'polls_answers');
  define('TABLE_POLLS_ANSWERS_DESCRIPTION', DB_TABLE_PREFIX . 'polls_answers_description');
  define('TABLE_POLLS_DESCRIPTION', DB_TABLE_PREFIX . 'polls_description');
  define('TABLE_POLLS_VOTES', DB_TABLE_PREFIX . 'polls_votes');
  define('TABLE_QUANTITY_DISCOUNT_GROUPS', DB_TABLE_PREFIX . 'quantity_discount_groups');
  define('TABLE_QUANTITY_DISCOUNT_GROUPS_VALUES', DB_TABLE_PREFIX . 'quantity_discount_groups_values');
  define('TABLE_QUANTITY_UNIT_CLASSES', DB_TABLE_PREFIX . 'quantity_unit_classes');
  define('TABLE_RATINGS', DB_TABLE_PREFIX . 'ratings');
  define('TABLE_RATINGS_DESCRIPTION', DB_TABLE_PREFIX . 'ratings_description');
  define('TABLE_REVIEWS', DB_TABLE_PREFIX . 'reviews');
  define('TABLE_SEARCH_TERMS', DB_TABLE_PREFIX . 'search_terms');
  define('TABLE_SESSIONS', DB_TABLE_PREFIX . 'sessions');
  define('TABLE_SLIDE_IMAGES', DB_TABLE_PREFIX . 'slide_images');
  define('TABLE_SPECIALS', DB_TABLE_PREFIX . 'specials');
  define('TABLE_TAX_CLASS', DB_TABLE_PREFIX . 'tax_class');
  define('TABLE_TAX_RATES', DB_TABLE_PREFIX . 'tax_rates');
  define('TABLE_TEMPLATES', DB_TABLE_PREFIX . 'templates');
  define('TABLE_TEMPLATES_BOXES', DB_TABLE_PREFIX . 'templates_boxes');
  define('TABLE_TEMPLATES_BOXES_TO_PAGES', DB_TABLE_PREFIX . 'templates_boxes_to_pages');
  define('TABLE_VARIANTS_SPECIALS', DB_TABLE_PREFIX . 'variants_specials');
  define('TABLE_WEIGHT_CLASS', DB_TABLE_PREFIX . 'weight_classes');
  define('TABLE_WEIGHT_CLASS_RULES', DB_TABLE_PREFIX . 'weight_classes_rules');
  define('TABLE_WHOS_ONLINE', DB_TABLE_PREFIX . 'whos_online');
  define('TABLE_WISHLISTS', DB_TABLE_PREFIX . 'wishlists');
  define('TABLE_WISHLISTS_PRODUCTS', DB_TABLE_PREFIX . 'wishlists_products');
  define('TABLE_WISHLISTS_PRODUCTS_VARIANTS', DB_TABLE_PREFIX . 'wishlists_products_variants');
  define('TABLE_ZONES', DB_TABLE_PREFIX . 'zones');
  define('TABLE_ZONES_TO_GEO_ZONES', DB_TABLE_PREFIX . 'zones_to_geo_zones');
  define('TABLE_ORDER_TRACK', DB_TABLE_PREFIX . 'order_track');
  define('TABLE_ORDER_GOOGLE', DB_TABLE_PREFIX . 'google_orders');
?>