<?php
  define('HTTP_SERVER', '');
  define('HTTPS_SERVER', '');
  define('ENABLE_SSL', false);
  define('HTTP_COOKIE_DOMAIN', '');
  define('HTTPS_COOKIE_DOMAIN', '');
  define('HTTP_COOKIE_PATH', '');
  define('HTTPS_COOKIE_PATH', '');
  define('DIR_WS_HTTP_CATALOG', '');
  define('DIR_WS_HTTPS_CATALOG', '');
  define('DIR_WS_IMAGES', 'images/');

  define('DIR_FS_ADMIN', 'admin/');
  define('DIR_WS_DOWNLOAD_PUBLIC', 'pub/');
  define('DIR_FS_CATALOG', dirname($_SERVER['SCRIPT_FILENAME']));
  define('DIR_FS_WORK', '/tmp/');
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
  define('DIR_FS_BACKUP', DIR_FS_CATALOG. DIR_FS_ADMIN. 'backups/');
  define('DIR_FS_CACHE', DIR_FS_CATALOG . 'cache/');
  define('DIR_FS_CACHE_ADMIN', DIR_FS_CACHE . DIR_FS_ADMIN); 

  define('DB_SERVER', '');
  define('DB_SERVER_USERNAME', '');
  define('DB_SERVER_PASSWORD', '');
  define('DB_DATABASE', 'TomatoCart');
  define('DB_DATABASE_CLASS', 'mysql');
  define('DB_TABLE_PREFIX', 'toc_');
  define('USE_PCONNECT', 'false');
  define('STORE_SESSIONS', '');
?>