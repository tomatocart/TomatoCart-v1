# $Id: google_sitemap.php $
# TomatoCart Open Source Shopping Cart Solutions
# http://www.tomatocart.com
#
# Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd; Copyright (c) 2007 osCommerce
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License v2 (1991)
# as published by the Free Software Foundation.

heading_title = Google Sitemaps

action_heading_google_sitemaps_generation = Google Sitemaps Generation

introduction_create_google_sitemaps = <p><b>NOTE:</b> Please ensure your tomatocart directory is writable to create the sitemaps for each language except english. After the creation, you should make it unwritable for the security.</p><p>Be careful with non-ascii characters in frienld URLs for products, categories and articles because they are not the standard characters for url. Only characters you can reliably use for the actual name parts of a URL are a-z, A-Z, 0-9, -, ., _, and ~. Any other characters may result in problem.</p>

introduction_google_sitemaps_submission  = <b>NOTE:</b> The location of the Sitemap is included in the robots.txt file so that Google and other search engines know about your sitemap. But Google still recommend that the sitemaps should be submitted through Google Webmaster Tools account so you can make sure that the Sitemap was processed without any issues, and to get additional statistics about your site. <br/><br/>Please ensure that you has registered with Google Sitemaps, and submitted your initial sitemap before proceeding the following step.

google_sitemaps_infobox_title = What is Google Sitemaps?

field_language_selection = Select Language:
field_categories = Categories Frequency:
field_products = Products Frequency:
field_articles = Articles Frequency:

field_priority = Priority:

field_daily = Daily
field_month = Monthly
field_year = Yearly

button_create_sitemaps = Create Sitemaps
button_submit_sitemaps = Submit Sitemaps to Google

error_directory_not_writable = Your tomatocart directory is not writable. Please make it writable to create the sitemap.

