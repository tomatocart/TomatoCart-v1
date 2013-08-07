# $Id: tomatocart_sample_data.sql $
#
# TomatoCart Open Source Shopping Cart Solutions
# http://www.tomatocart.com
#
# Copyright (c) 2009 Elootech Technology Ltd.,  Copyright (c) 2006 osCommerce
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License v2 (1991)
# as published by the Free Software Foundation.
#
# NOTE: * Please make any modifications to this file by hand!
#       * DO NOT use a mysqldump created file for new changes!
#       * Please take note of the table structure, and use this
#         structure as a standard for future modifications!
#       * Any tables you add here should be added in admin/backup.php
#         and in catalog/install/includes/functions/database.php
#       * To see the 'diff'erence between MySQL databases, use
#         the mysqldiff perl script located in the extras
#         directory of the 'catalog' module.
#       * Comments should be like these, full line comments.
#         (don't use inline comments)

#Slide show
INSERT INTO toc_slide_images (image_id, language_id, description, image, image_url, sort_order, status) VALUES
(1, 1, 'Put here the required information.', 'dell_xps630_en.png', 'products.php?1', 0, 1),
(2, 1, 'Put here the required information.', 'apple_23_cinema_en.png', 'products.php?18', 0, 1),
(3, 1, 'Put here the required information.', 'thinkcentre_m57p_en.png', 'products.php?3', 0, 1),
(4, 1, 'Put here the required information.', 'apple_iphone_3g_en.png', 'products.php?17', 0, 1),
(5, 1, 'Put here the required information.', 'hp_tx2510us_en.png', 'products.php?13', 0, 1);

INSERT INTO toc_configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES
('Interval (ms)', 'MODULE_CONTENT_SLIDE_SHOW_INTERVAL', '3000', 'slide show interval', 6, 0, NULL, now(), NULL, NULL),
('Duration (ms)', 'MODULE_CONTENT_SLIDE_SHOW_DURATION', '1000', 'slide show duration', 6, 0, NULL, now(), NULL, NULL),
('Image height (px)', 'MODULE_CONTENT_SLIDE_SHOW_HEIGHT', '210', 'Image height', 6, 0, NULL, now(), NULL, NULL),
('Image width (px)', 'MODULE_CONTENT_SLIDE_SHOW_WIDTH', '960', 'Image width', 6, 0, NULL, now(), NULL, NULL),
('Display Slide info', 'MODULE_CONTENT_SLIDE_SHOW_DISPLAY_INFO', 'False', 'Display Slide Info', 6, 0, NULL, now(), NULL, 'osc_cfg_set_boolean_value(array(''True'', ''False''))'),
('Slide show mode [vertical, horizontal]', 'MODULE_CONTENT_SLIDE_SHOW_MODE', 'horizontal', 'Slideshow Mode', 6, 0, NULL, now(), NULL, 'osc_cfg_set_boolean_value(array(''horizontal'', ''vertical''))');

INSERT INTO toc_templates_boxes (id, title, code, author_name, author_www, modules_group) VALUES
(100, 'Slideshow', 'slide_show', 'TomatoCart', 'http://www.tomatocart.com', 'content');

INSERT INTO toc_templates_boxes_to_pages (templates_boxes_id, templates_id, content_page, boxes_group, sort_order, page_specific) VALUES
(100, 1, 'index/index', 'slideshow', 0, 0);

# Articles Categories
INSERT INTO toc_articles_categories (articles_categories_id, articles_categories_status, articles_categories_order) VALUES (2, 1, 0);
INSERT INTO toc_articles_categories_description (articles_categories_id, language_id, articles_categories_name, articles_categories_url,articles_categories_page_title,articles_categories_meta_keywords,articles_categories_meta_description) VALUES (2, 1, 'Latest News', 'latest-news', '', '', '');
INSERT INTO toc_articles_categories (articles_categories_id, articles_categories_status, articles_categories_order) VALUES (3, 1, 0);
INSERT INTO toc_articles_categories_description (articles_categories_id, language_id, articles_categories_name, articles_categories_url,articles_categories_page_title,articles_categories_meta_keywords,articles_categories_meta_description) VALUES (3, 1, 'Categories 1', 'categories-1', '', '', '');
INSERT INTO toc_articles_categories (articles_categories_id, articles_categories_status, articles_categories_order) VALUES (4, 1, 0);
INSERT INTO toc_articles_categories_description (articles_categories_id, language_id, articles_categories_name, articles_categories_url,articles_categories_page_title,articles_categories_meta_keywords,articles_categories_meta_description) VALUES (4, 1, 'Categories 2', 'categories-2', '', '', '');
INSERT INTO toc_articles_categories (articles_categories_id, articles_categories_status, articles_categories_order) VALUES (5, 1, 0);
INSERT INTO toc_articles_categories_description (articles_categories_id, language_id, articles_categories_name, articles_categories_url,articles_categories_page_title,articles_categories_meta_keywords,articles_categories_meta_description) VALUES (5, 1, 'Categories 3', 'categories-3', '', '', '');
INSERT INTO toc_articles_categories (articles_categories_id, articles_categories_status, articles_categories_order) VALUES (6, 1, 0);
INSERT INTO toc_articles_categories_description (articles_categories_id, language_id, articles_categories_name, articles_categories_url,articles_categories_page_title,articles_categories_meta_keywords,articles_categories_meta_description) VALUES (6, 1, 'Categories 4', 'categories-4', '', '', '');

INSERT INTO toc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES
('Maximum List Size', 'BOX_ARTICLES_CATEGORIES_MAX_LIST', '10', 'Maximum amount of article categories to show in the listing', 6, 0, NULL, now(), NULL, NULL);
INSERT INTO toc_templates_boxes (id, title, code, author_name, author_www, modules_group) VALUES (101, 'Article Categories', 'articles_categories', 'TomatoCart', 'http://www.tomatocart.com', 'boxes');
INSERT INTO toc_templates_boxes_to_pages (templates_boxes_id, templates_id, content_page, boxes_group, sort_order, page_specific) VALUES (101, 1, '*', 'right', 70, 0);

# Shop by price
INSERT INTO toc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES
('British Pounds', 'BOX_SHOP_BY_PRICE_GBP', '300;600;900;1200', 'British Pounds price interval (Price seperated by ";")', 6, 0, NULL, now(), NULL, NULL),
('Euro', 'BOX_SHOP_BY_PRICE_EUR', '400;800;1200;1600', 'Euro price interval (Price seperated by ";")', 6, 0, NULL, now(), NULL, NULL),
('US Dollar', 'BOX_SHOP_BY_PRICE_USD', '600;1200;1800;2400', 'US Dollar price interval (Price seperated by ";")', 6, 0, NULL, now(), NULL, NULL);

INSERT INTO toc_templates_boxes (id, title, code, author_name, author_www, modules_group) VALUES (102, 'Shop By Price', 'shop_by_price', 'TomatoCart', 'http://www.tomatocart.com', 'boxes');
INSERT INTO toc_templates_boxes_to_pages (templates_boxes_id, templates_id, content_page, boxes_group, sort_order, page_specific) VALUES (102, 1, '*', 'right', 50, 0);

#manufacturers
INSERT INTO toc_manufacturers (manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified) VALUES 
(1, 'Apple', 'apple.png', now(), now()),
(2, 'Dell', 'dell.png', now(), now()),
(3, 'HP', 'hp.png', now(), now()),
(4, 'Lenovo', 'lenovo.png', now(), now()),
(5, 'Sony', 'sony.png', now(), now());

#manufacturers_info
INSERT INTO toc_manufacturers_info (manufacturers_id, languages_id, manufacturers_url, manufacturers_friendly_url, url_clicked, date_last_click, manufacturers_page_title , manufacturers_meta_keywords, manufacturers_meta_description) VALUES 
(1, 1, 'http://www.apple.com', 'apple', '0', NULL, '', '', ''),
(2, 1, 'http://www.dell.com', 'dell', '0', NULL, '', '', ''),
(3, 1, 'http://www.hp.com', 'hp', '0', NULL, '', '', ''),
(4, 1, 'http://www.lenovo.com', 'lenovo', '0', NULL, '', '', ''),
(5, 1, 'http://www.sony.com', 'sony', '0', NULL, '', '', '');

#product categories
INSERT INTO toc_categories (categories_id, categories_image, parent_id, sort_order, date_added, last_modified) VALUES
(1, 'categories_notebooks.jpg', 0, 0, now(), NULL),
(2, 'categories_desktops.jpg', 0, 0, now(), NULL),
(3, 'categories_monitor.jpg', 0, 0, now(), NULL),
(4, 'categories_printer.jpg', 0, 0, now(), NULL),
(5, 'categories_printer.jpg', 4, 0, now(), NULL),
(6, 'categories_printer.jpg', 4, 0, now(), NULL),
(7, 'categories_printer.jpg', 4, 0, now(), NULL),
(8, 'categories_printer.jpg', 4, 0, now(), NULL),
(9, 'categories_ipod.jpg', 0, 0, now(), NULL);


INSERT INTO toc_categories_description (categories_id, language_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) VALUES
(1, 1, 'Laptop', 'laptop', '', '', ''),
(2, 1, 'Desktops', 'desktops', '', '', ''),
(3, 1, 'Monitors', 'monitors', '', '', ''),
(4, 1, 'Printers & Scanners', 'printers-scanners', '', '', ''),
(5, 1, 'HP', 'hp', '', '', ''),
(6, 1, 'BROTHER', 'brother', '', '', ''),
(7, 1, 'EPSON', 'epson', '', '', ''),
(8, 1, 'CANON', 'canon', '', '', ''),
(9, 1, 'iPod & Camera', 'ipod-camera', '', '', '');


#products
INSERT INTO toc_products (products_id, products_quantity, products_price, products_date_added, products_last_modified, products_date_available, products_weight, products_weight_class, products_status, products_tax_class_id, manufacturers_id, products_ordered, quantity_discount_groups_id, quantity_unit_class, products_sku, products_model) VALUES
(1, 10, 799.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 2, 0, 0, 1, '', ''),
(2, 10, 599.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 3, 0, 0, 1, '', ''),
(3, 10, 849.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 4, 0, 0, 1, '', ''),
(4, 10, 1099.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 1, 0, 0, 1, '', ''),
(5, 10, 1199.0000, now(), NULL, NULL, 5.00, 2, 1, 0, 1, 0, 0, 1, '', ''),
(6, 10, 1299.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 1, 0, 0, 1, '', ''),
(7, 10, 1799.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 4, 0, 0, 1, '', ''),
(8, 10, 999.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 4, 0, 0, 1, '', ''),
(9, 10, 899.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 4, 0, 0, 1, '', ''),
(10, 10, 499.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 3, 0, 0, 1, '', ''),
(11, 10, 449.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 6, 0, 0, 1, '', ''),
(12, 10, 479.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 3, 0, 0, 1, '', ''),
(13, 10, 699.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 3, 0, 0, 1, '', ''),
(14, 10, 599.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 6, 0, 0, 1, '', ''),
(15, 10, 79.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 1, 0, 0, 1, '', ''),
(16, 10, 120.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 1, 0, 0, 1, '', ''),
(17, 10, 399.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 1, 0, 0, 1, '', ''),
(18, 10, 300.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 1, 0, 0, 1, '', ''),
(19, 10, 199.0000, now(), NULL, NULL, 10.00, 2, 1, 0, 5, 0, 0, 1, '', '');


INSERT INTO toc_products_description (products_id, language_id, products_name, products_description, products_keyword, products_tags, products_friendly_url, products_viewed, products_page_title, products_meta_keywords, products_meta_description) VALUES
(1, 1, 'Dell XPS 630', '<h2>Features </h2><ul><li>Experience the latest technology and innovative design with the XPS? 630 - a sleek, head turning warrior created for smashing game performance </li><li>A supersonic gaming extravaganza ? the XPS? 630 offers full-on power with overclocking via BIOS or Nvidia?? nTune GeForce 9800GT, a Quad Intel?? Q9300 processor and a 750W power supply for an extreme experience </li><li>Front: USB 2.0 (2), 1394a, headphone jack, mic, optional 19-in-1 media card reader </li><li>Back (Audio): S/PDIF optical, line-in, line-out, microphone, surround, center/LFE; integrated HDA 7.1 channel sound </li><li>Back (Other): USB 2.0 (4), 1394a, PS/2 Mouse and Keyboard, Gigabit Ethernet8</li><li>Vista Premium 64bit SP1<br></li></ul><p><br></p>', NULL, '', 'dell-xps-630', 4, '', '', ''),
(2, 1, 'HP Pavilion a6433w-b', '<h2>Features\r\n\r\n</h2><ul><li>HP w2207h 22" diagonal widescreen LCD monitor with built-in speakers </li><li>Presents 1680 x 1050 resolution with 5 ms. response time and 1000:1 contrast ratio&nbsp; </li><li>2 GHz Intel Pentium dual-core desktop processor E2180 </li><li>Delivers the processing power required for true multitasking and multimedia&nbsp; </li><li>3 GB of DDR2 system memory </li><li>Offers improved performance for today''s demanding applications along with the option of expanding up to 8 GB&nbsp; </li><li>500 GB hard drive, 7200 rpm </li><li>Provides a tremendous amount of storage space for documents, games, music, photos and videos&nbsp; </li><li>SuperMulti DVD+/-RW drive with double-layer capability </li><li>Lets you burn and play DVDs and CDs for entertainment and data backup&nbsp; </li><li>10/100/1000 Base-T network interface and 56k modem </li><li>Connects to the Internet via DSL, cable or dial-up service&nbsp; </li><li>15-in-1 digital media card reader </li><li>Reads virtually all memory card formats so you can enjoy photos, music and other files&nbsp; </li><li>Vista Home Premium Edition with Windows Media Center and Service Pack 1 </li></ul><p>&nbsp;</p>', NULL, '', 'hp-pavilion-a6433w-b', 2, '', '', ''),
(3, 1, 'ThinkCentre M57p', '<h2>Features</h2><ul><li>Intel? Core? 2 Duo E8200 Processor ( 2.66GHz 1GHz 6MB )</li><li>Genuine Windows Vista Business</li><li>Ultra Small Form Factor</li><li>1 GB DDR2 SDRAM 667MHz </li><li>160GB </li><li>CD-RW/DVD-ROM Combo 48X/32X/48X/16X Max </li></ul>', NULL, '', 'thinkcentre-m57p', 0, '', '', ''),
(4, 1, '15.4" Macbook Pro MB134LL/A', '<h2>Product Features</h2><p><br></p><ul><li>2.5 GHz Intel Core 2 Duo processor with 6 MB on-chip shared L2 cache, Mac OS X v10.5 Leopard</li><li>2 GB RAM (two SO-DIMM) 677 MHz DDR2 SD-RAM (PC2-5300), 250 GB 5400 rpm Serial ATA hard drive, slot load 8x Super Drive (DVD?±R DL/DVD?±RW/CD-RW)</li><li>One FireWire 400, one FireWire 800, two USB 2.0 ports, and ExpressCard/34 slot</li><li>Built-in 10/100/1000BASE-T (Gigabit); built-in 54 Mbps AirPort Extreme (802.11n); built-in Bluetooth 2.0+EDR</li><li>15.4-inch (diagonal), 1440 by 900 resolution, matte TFT LED widescreen display with NVIDIA GeForce 8600M GT with 512 MB of GDDR3 SDRAM and dual-link DVI</li></ul>', NULL, '', '15-4-macbook-pro-mb134ll-a', 5, '', '', ''),
(5, 1, '17" MACBOOK PRO MB166LL/A', '<h2>Products Features</h2><br><ul><li>Processor Core2 Duo 2.5GHz&nbsp; , L2 cache 6MB &nbsp;</li><li>Hard Drive 250 GB </li><li>Optical drive 8X DVD+-RW/CD-RW </li><li>RAM standard 2GB DDR2 SDRAM </li><li>RAM maximum 4GB &nbsp;</li><li>Display 17 active-matrix/TFT/XGA , maximum resolution 1680 x 1050 &nbsp;</li><li>NIC type Gigabit Ethernet &nbsp;</li><li>Wireless NIC included Yes &nbsp;</li><li>Bluetooth capable Yes &nbsp;</li><li>OS Mac OS X v10.5 Leopard </li></ul><br>', NULL, '', '17-macbook-pro-mb166ll-a', 1, '', '', ''),
(6, 1, '13.3" MACBOOK AIR APPLE Z0FS0LL/A', '<h2>Products Features</h2><br><ul><li>Processor Core2 Duo 1.8GHz&nbsp;&nbsp; , L2 cache 6MB &nbsp;</li><li>64GB solid-state drive</li><li>Optional external Apple MacBook Air SuperDrive available seperately </li><li>2GB of 667MHz DDR2 SDRAM onboard</li><li>Display 13.3 backlit LCD , maximum resolution 1280 x 800 </li><li>NIC type Gigabit Ethernet &nbsp;</li><li>Wireless NIC included Yes &nbsp;</li><li>Bluetooth capable Yes &nbsp;</li><li>Mac OS X v10.5 Leopard; iLife a€?08 (includes iTunes, iPhoto, iMovie, iDVD, iWeb, GarageBand)</li></ul>', NULL, '', '13-3-macbook-air-apple-z0fs0ll-a', 3, '', '', ''),
(7, 1, 'LENOVO THINKPAD X301, SU9400 1.4GHZ CPU', '<h2>Products Features</h2><br><ul><li>Processor: Intel Core 2 Duo SU9400 Low-Voltage (1.4Ghz/800mhz/3MB) </li><li>Operating System: Genuine Windows XP Professional - 32 bit edition </li><li>LED Display: 13.3" WXGA+ TFT widescreen (1440 x 900 pixels)&nbsp; &nbsp;</li><li>Memory: 3GB PC3-8500 DDR3 SDRAM 1067MHz Memory </li><li>WWAN: Integrated Wireless Wide Area&nbsp; Network Upgradeable</li><li>Hard drive: 128GB Solid State Drive</li><li>Pointing Device: UltraNav TrackPoint &amp; Touchpad) with Fingerprint Reader </li><li>Graphics Card: Intel GMA 4500MHD Video Card</li><li>Optical Device: DVD/RW +/- Dual Layer (Burns CD &amp; DVD''s)</li><li>Integrated Wireless Card: Intel Pro Wireless WiFi link 5100 (AGN)</li></ul>', NULL, '', 'lenovo-thinkpad-x301-su9400-1-4ghz-cpu', 6, '', '', ''),
(8, 1, 'Lenovo ThinkPad X200', '<h2>Products Features</h2><br><ul><li>Intel Core 2 Duo P8600 2.8 GHz Processor </li><li>3 MB L2 Cache, 1066 MHz Bus speed </li><li>2048 MB DDR2 (PC2-8500) RAM Max - 4GB </li><li>160 GB (5400RPM) SATA Hard Drive</li><li>6-Cell Lithium-ion Battery Pack, AC power adapter, Power Cord; </li><li>Software Bundle - Windows XP Professional, Microsoft Office 2007 60-Day Trial, McAfee VirusScan Plus 30-Day Subscription</li></ul><br>', NULL, '', 'lenovo-thinkpad-x200', 3, '', '', ''),
(9, 1, 'Lenovo ThinkPad T400 2767', '<h2>Products Features</h2><br><ul><li>Core 2 Duo T9400 / 2.53 GHz - Centrino 2 with vPro - </li><li>RAM 2 GB - </li><li>HDD 160 GB - </li><li>DVD-RW (-R DL) / DVD-RAM - </li><li>Mobility Radeon HD 3470 / GMA 4500MHD - </li><li>cellular mdm / mdm ( CDMA 2000 1X EV-DO Rev. A, WCDMA, HSPA ) - </li><li>Verizon - </li><li>Gigabit Ethernet - </li><li>WLAN : 802.11 a/b/g/n (draft), Bluetooth 2.0 - </li><li>TPM - </li><li>fingerprint reader - </li><li>Vista Business -</li><li>14.1" Widescreen TFT 1440 x 900 ( WXGA+ )</li></ul><br>', NULL, '', 'lenovo-thinkpad-t400-2767', 1, '', '', ''),
(10, 1, 'HP Pavilion DV7-1240US 17.0-Inch Laptop', '<h2>Products Features</h2><br><ul><li>Entertainment-centric notebook PC with fluid, modern lines in bronze and chrome with argyle-like patterning; widescreen 17-inch LCD </li><li>2.1 GHz AMD Turion X2 RM-72 dual-core processor, 320 GB hard drive, 4 GB RAM (8 GB max), LightScribe dual-layer DVD drive </li><li>Draft-N Wi-Fi (802.11a/b/g/n); Gigabit Ethernet; ATI Radeon HD 3200 graphics (up to 1918 MB total) </li><li>Connectivity: 4 USB (with 1 shared eSATA), 1 HDMI, 1 VGA, ExpressCard 54/34, 5-in-1 memory card reader </li><li>Pre-installed with Windows Vista Home Premium with SP1 (64-bit version); includes remote control</li></ul>', NULL, '', 'hp-pavilion-dv7-1240us-17-0-inch-laptop', 0, '', '', ''),
(11, 1, 'Toshiba Satellite A355-S6921', '<h2>Products Features</h2><br><ul><li>Portable multimedia laptop with 16-inch screen for true 16:9 aspect ratio and native 720p high-def resolution </li><li>2.0 GHz AMD Turion X2 RM-72 dual-core processor, 250 GB hard drive, 3 GB RAM (4 GB max), Labelflash dual-layer DVD drive </li><li>Draft-N Wi-Fi (802.11b/g/n), Fast Ethernet, ATI Radeon 3100 graphics (up to 1919 MB total available memory) </li><li>Connectivity: 4 USB (with 1 shared eSATA), 1 FireWire, 1 VGA, ExpressCard 54/34, 5-in-1 memory card reader </li><li>Pre-installed with Windows Vista Home Premium with SP1 (64-bit version); Fusion finish with Horizon pattern</li></ul><br>', NULL, '', 'toshiba-satellite-a355-s6921', 2, '', '', ''),
(12, 1, 'HP Compaq 6735s KS117UT 15.4-Inch Notebook', 'Products Features<br><br>Chipset AMD M780G <br>Processors:AMD Turion X2 Dual-Core Mobile Processor RM-70 (2.0 GHz, 2 x 512 KB L2 cache) <br>Memory Standard :667 MHz or 800 MHz DDR2 SDRAM, two slots supporting dual channel memory 1024 MB or 2048 MB <br>Maximum : Upgradeable to 8192 MB with 4096 MB SODIMMs in slots 1 and 2,<br>Maximum memory with AMD Sempron processor is 4096 MB.<br>Dual-channel : Maximized dual-channel performance requires SODIMMs of the same size and speed in both memory slots. <br>Graphics Integrated UMA Graphics ATI Mobility Radeon HD 3200 with shared system memory. Microsoft DirectX 10 capable <br>Display Internal :15.4 inches diagonal WXGA anti-glare (1280 x 800 resolution) 15.4 inches diagonal WXGA BrightView (1280 x 800 resolution) <br>External :Up to 32 bit per pixel color depthVGA port supports resolutions up to 2048 x 1536 at 75 Hz, and lower resolutions at up to 100 Hz. <br>', NULL, '', 'hp-compaq-6735s-ks117ut-15-4-inch-notebook', 0, '', '', ''),
(13, 1, 'HP Pavilion TX2510US 12.1-inch Laptop', '<h2>Products Features</h2><br><ul><li>12.1-inch (Diagonal) Widescreen Integrated Touch Screen, Convertible Display 1280 x 800, Panel Rotates 180 Degrees and Folds Flat </li><li>2.10 GHz AMD Turion X2 ZM-80 Ultra Dual Core Mobile Processor with 2 MB L2 Cache </li><li>3072 MB DDR2 System Memory (2 Dimm), 250 GB (5400RPM) Hard Drive (SATA), ATI Radeon HD 3200 Graphics RS780M with 64 MB DDR2 (Sideport Memory) with up to 1470 MB Total Graphics Memory </li><li>LightScribe Super Multi 8X DVD?±R/RW with Double Layer Support, Wireless LAN 802.11a/b/g/n and Bluetooth </li><li>Windows Vista Home Premium, dims in inches: 8.82 (W) x 12.05 (L) x 1.52 (H) approx., 4.56 lbs.</li></ul><br>', NULL, '', 'hp-pavilion-tx2510us-12-1-inch-laptop', 0, '', '', ''),
(14, 1, 'Toshiba Satellite L305D-S5904 15.4-Inch Laptop', '<h2>Products Features</h2><br><ul><li>15.4-Inch (Diagonal) Widescreen TruBrite TFT LCD Display at 1280 x 800 Resolution </li><li>2.0 GHz AMD Turion 64 X2 Dual Core Mobile Technology with 1MB L2 Cache </li><li>3072MB DDR2 SDRAM System Memory, 250GB (5400 RPM) Hard Drive (SATA), ATI Radeon X1250 Graphics with 128MB-831MB Dynamically Allocated Shared Graphics Memory </li><li>DVD SuperMulti (+/-R Double Layer) with Labelflash, Atheros 802.11 b/g Wireless LAN </li><li>Windows Vista Home Premium, dims in inches: 4.75 (W) x 14.3 (L) x 1.33 (H) approx., 5.49 lbs.</li></ul><br>', NULL, '', 'toshiba-satellite-l305d-s5904-15-4-inch-laptop', 0, '', '', ''),
(15, 1, 'APPLE IPOD NANO 4GB SILVER 3RD GEN', 'An anodized aluminum top and polished stainless steel back. Five eye-catching colors. A larger, brighter display with the most pixels per inch of any Apple display, ever. iPod nano stirs up visual effects from the outside in.<br><br>And it''ll wow you for hours. Play up to 5 hours of video or up to 24 hours of audio on a single charge. All that staying power and a wafer-thin, 6.5-mm profile makes iPod nano one small big attraction.<br><br><b>Cover Flow</b><br>If a picture says a thousand words, think of what all the album art in your collection might say. With Cover Flow on iPod nano, you can flip through your music to find the album you want to hear. Use the Click Wheel to browse music by album cover, then select an album to flip it over and see the track list.<br><br><b>Music</b><br>Use the Click Wheel to adjust volume, navigate songs, browse in Cover Flow, or explore the Music menu by playlist, artist, album, song, genre, composer, and more. Want to mix things up? Click Shuffle Songs. iPod nano makes your music look as good as it sounds, thanks to its bright color display.<br><br><b>Movies</b><br>Buy movies from the iTunes Store and you can sync them to your iPod nano to watch anywhere, anytime. Up to 5 hours of video playback means you can watch two movies back to back. And for your viewing pleasure, the 320-by-240-pixel display--with a whopping 204 pixels per inch--is 65 percent brighter than before. <br>', NULL, '', 'apple-ipod-nano-4gb-silver-3rd-gen', 2, '', '', ''),
(16, 1, 'Apple iPod touch 8 GB', '<h2>Features</h2><ul><li>Size of Display&nbsp;&nbsp;&nbsp; 3.5 inch</li><li>Display Features&nbsp;&nbsp;&nbsp; Battery Level</li><li>Digital Storage Media&nbsp;&nbsp;&nbsp; 8 GB (Built-in Memory)</li><li>PC Interface Supported&nbsp;&nbsp;&nbsp; USB, Wi-Fi</li><li>Battery Run Time&nbsp;&nbsp;&nbsp; Up to 22 hrs.</li><li>Battery Type&nbsp;&nbsp;&nbsp; Internal Battery</li><li>Dimensions (W X D X H)&nbsp;&nbsp;&nbsp; 2.4 in. x 0.31 in. x 4.3 in.</li><li>Weight&nbsp;&nbsp;&nbsp; 4.2 oz.</li><li>MPN&nbsp;&nbsp;&nbsp; MA623ZO/B</li></ul><p><br>&nbsp;</p>', NULL, '', 'apple-ipod-touch-8-gb', 1, '', '', ''),
(17, 1, 'Apple iPhone 3G', 'Features<br><br><ul><li>APPLE 3G 8GB</li><li>APPLE 3.5" TOUCHSCREEN</li><li>QUAD-BAND GSM &amp; EDGE</li><li>WI-FI (802.11b/g)</li><li>BLUETOOTH 2.0 EDR</li><li>8GB BUILT-IN STORAGE </li></ul>', NULL, '', 'apple-iphone-3g', 1, '', '', ''),
(18, 1, 'APPLE 23" HD CINEMA COLOR DISPLAY', '<h2>Features</h2><br><ul><li>Compatibility: Mac</li><li>Panel type: Thin film transistor (TFT) active matrix LCD</li><li>Display size: 23 inches</li><li>Diagonal viewable screen size: 23 inches</li><li>Dot pitch: 1920 x 1200 pixels (optimum)</li><li>Contrast ratio: 350:1</li><li>Glass surface: Anti-glare hardcoat screen treatment</li><li>Horizontal viewing angle: 170 degrees</li><li>Vertical viewing angle: 170 degrees</li><li>Brightness: 200 cd/m2</li><li>Input signals: Digital</li><li>Input connector/cable: Apple Display Connector (ADC) carries power, USB, and digital graphics; two-port self-powered USB hub connects peripherals</li><li>Maximum noninterlaced resolution: 1920 x 1200 dpi</li><li>Power on/off: Yes, system power on/sleep/wake</li><li>Brightness: Yes</li><li>Swivel: Yes, user adjustable</li><li>System requirements: One of the following systems (a,b, or c): (a) Power Mac G4 with NVIDIA GeForce2 MX, GeForce3, GeForce4 MX, or GeForce4 Ti graphics card, or ATI Radeon 7500 graphics card; and Mac OS X v10.1.3 or Mac OS 9.2.2, (b) Power Mac G4 with DVI port (via an NVIDIA GeForce4 Ti graphics card) and Apple DVI to ADC Adapter, (c) PowerBook G4 with DVI port and Apple DVI to ADC Adapter</li><li>Width: 24.2 inches</li><li>Height: 19.2 inches</li><li>Depth: 7.3 inches</li><li>Weight: 25.3 pounds</li></ul><br>', NULL, '', 'apple-23-hd-cinema-color-display', 0, '', '', ''),
(19, 1, 'SONY DSC-T700(g) DIGITAL VIDEO CAMERA', '<h2>Features</h2><ul><li>Slim, beautiful design.</li><li>10.1 effective megapixels</li><li>4GB internal memory, stores up to 40,000 photos</li><li>Powerful album functions to store, organize and share your photos</li><li>Smile Shutter automatically captures smiling faces</li><li>Beautiful portraits with Face Detection and Anti-blink technology</li><li>Enjoy your images on the Extra-large 3.5a€? touchscreen LCD</li><li>Easy web upload for sharing images on social networking sites</li><li>Double Anti-blur Solution</li><li>Portable Photo Album</li><li>Face Detection</li><li>Intelligent Scene Recognition</li><li>In Camera Retouch</li><li>Share HD-Quality Images </li></ul>', NULL, '', 'sony-dsc-t700-g-digital-video-camera', 2, '', '', '');

INSERT INTO toc_products_images (id, products_id, image, default_flag, sort_order, date_added) VALUES
(1, 1, '4589430859034895043.jpg', 1, 0, '2009-01-26 17:32:02'),
(2, 2, '5443523452354.jpg', 1, 0, '2009-01-26 17:45:14'),
(3, 3, '437893748943838943.jpg', 1, 0, '2009-01-26 17:56:27'),
(4, 4, '54892437589237584.jpg', 1, 0, '2009-01-26 18:12:38'),
(5, 4, '0904856904586475689.jpg', 0, 0, '2009-01-26 18:12:57'),
(6, 4, '098489508435893845.jpg', 0, 0, '2009-01-26 18:13:09'),
(7, 5, '0904856904586475689.jpg', 0, 0, '2009-01-26 18:56:24'),
(8, 5, '54892437589237584.jpg', 1, 0, '2009-01-26 18:56:31'),
(9, 5, '098489508435893845.jpg', 0, 0, '2009-01-26 18:56:39'),
(10, 6, '54892437589237584.jpg', 1, 0, '2009-01-26 19:03:33'),
(11, 6, '098489508435893845.jpg', 0, 0, '2009-01-26 19:03:42'),
(12, 6, '0904856904586475689.jpg', 0, 0, '2009-01-26 19:03:50'),
(18, 7, 'h45435345345345.jpg', 0, 0, '2009-01-26 19:18:13'),
(19, 7, 'v34234234234234.jpg', 1, 0, '2009-01-26 19:18:16'),
(22, 8, '56435345345345345.jpg', 1, 0, '2009-01-26 19:23:50'),
(26, 9, 't400-ds798348923482.jpg', 1, 0, '2009-01-26 19:33:59'),
(24, 8, '67567567567567.jpg', 0, 0, '2009-01-26 19:23:57'),
(27, 10, 'dv70-1240-34823489234.jpg', 1, 0, '2009-01-26 19:36:59'),
(28, 10, 'dv70-1240-34823484349234.jpg', 0, 0, '2009-01-26 19:37:18'),
(30, 11, 'a355-349234-23489234.jpg', 1, 0, '2009-01-26 19:45:27'),
(31, 11, 'a355-349234-33489234.jpg', 0, 0, '2009-01-26 19:46:15'),
(32, 12, '6735s-ks117ut-334839234.jpg', 1, 0, '2009-01-26 19:56:00'),
(33, 13, 'tx2510us-39293843.jpg', 1, 0, '2009-01-26 19:59:08'),
(34, 13, 'tx2510us-39293845.jpg', 0, 0, '2009-01-26 19:59:36'),
(35, 13, 'tx2510us-39493845.jpg', 0, 0, '2009-01-26 20:00:00'),
(36, 14, 'l305d-s5904-39293323.jpg', 1, 0, '2009-01-26 20:04:36'),
(37, 14, 'l305d-s5904-39245323.jpg', 0, 0, '2009-01-26 20:04:57'),
(38, 14, 'l305d-s5904-55593323.jpg', 0, 0, '2009-01-26 20:05:04'),
(39, 15, 'ipod-nano3-392339239.jpg', 1, 0, '2009-01-26 21:51:24'),
(40, 16, 'ipod-touch-392033.jpg', 1, 0, '2009-01-26 22:04:47'),
(41, 16, 'ipod-touch-3323.jpg', 0, 0, '2009-01-26 22:10:45'),
(42, 16, 'ipod-touch-39203333.jpg', 0, 0, '2009-01-26 22:11:38'),
(43, 17, 'iphone-03923923.jpg', 1, 0, '2009-01-26 22:18:37'),
(44, 18, 'cinema-3923823.jpg', 1, 0, '2009-01-26 22:34:17'),
(45, 19, 'dsc-t700-38324.jpg', 1, 0, '2009-01-26 22:38:18'),
(46, 19, 'dsc-t700-38434.jpg', 0, 0, '2009-01-26 22:38:39'),
(47, 19, 'dsc-t700-42434.jpg', 0, 0, '2009-01-26 22:38:44');

INSERT INTO toc_products_to_categories (products_id, categories_id) VALUES
(1, 2),
(2, 2),
(3, 2),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 5),
(16, 5),
(17, 5),
(18, 3),
(19, 5);

#popular search term
INSERT INTO toc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Cache Contents', 'BOX_POPULAR_SEARCH_TERM_CACHE', '60', 'Number of minutes to keep the contents cached (0 = no cache)', '6', '0', now());
INSERT INTO toc_search_terms (search_terms_id, text, products_count, search_count, synonym, show_in_terms, date_updated) VALUES
(1, 'apple', 2, 30, '', 1, now()),
(2, 'asus', 0, 7, '', 0, now()),
(3, 'ibm', 0, 16, '', 0, now()),
(4, 'sony', 0, 27, '', 1, now()),
(5, 'acer', 0, 95, '', 1, now()),
(6, 'benq', 0, 46, '', 1, now()),
(7, 'lenovo', 0, 55, '', 1, now()),
(8, 'nokia', 0, 74, '', 1, now()),
(9, 'LG', 0, 75, '', 1, now()),
(10, 'Samsung', 0, 42, '', 1, now());

#feature products
INSERT INTO toc_products_frontpage (products_id, sort_order) VALUES
(6, 1),
(5, 2),
(17, 3),
(19, 4),
(3, 5),
(9, 6);

