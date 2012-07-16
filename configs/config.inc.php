<?php
if (!defined('SITEROOT')) { //backdoor.
  define('SITEROOT', './');
}
define("LOGIN", SITEROOT."login.php"); // entry point.
define('COMMON_USER', 'common_users'); // login table.

$config = array(
  'debug' => true, // usd by smarty templates as well as php.
  'site' => 'church', //default.
  'site_id' => 1,
  'path' => SITEROOT.'themes/default/',
  'ipath' => SITEROOT.'include/',
  'include' => SITEROOT.'include/',
  'smarty' => SITEROOT.'configs/smarty.ini',
  'title' => 'One Family Fellowship - Surrey Christian Alliance Church',
  'browser' => browser_id(),
  'header' => array(
    'title' => 'One Family Fellowship - Surrey Christian Alliance Church',
    'description' => 'One Family Fellowship - Surrey Christian Alliance Church',
    'keywords' => 'One Family Fellowship - Surrey Christian Alliance Church',
    'meta_content' => 'text/html, charset=>utf-8',
    'meta_defaultrobots' => 'index,follow',
    'meta_robots' => '',
  ),
);

if (preg_match("/^(192\.168|127\.)/", $_SERVER['REMOTE_ADDR']) || preg_match("/::1/", $_SERVER['REMOTE_ADDR'])) { 
  define("HOST", "localhost");
  define("USER", "");
  define("PASS", "");
  define("DB_NAME", "");
}
else {
  define("HOST", "");
  define("USER", "");
  define("PASS", "!");
  define("DB_NAME", "");
}

function browser_id() {
  if(strstr($_SERVER['HTTP_USER_AGENT'], 'Firefox')){ $id="firefox"; }
  elseif(strstr($_SERVER['HTTP_USER_AGENT'], 'Safari') && !strstr($_SERVER['HTTP_USER_AGENT'], 'Chrome')){ $id="safari"; }
  elseif(strstr($_SERVER['HTTP_USER_AGENT'], 'Chrome')){ $id="chrome"; }
  elseif(strstr($_SERVER['HTTP_USER_AGENT'], 'Opera')){ $id="opera"; }
  elseif(strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')){ $id="ie6"; }
  elseif(strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE 7')){ $id="ie7"; }
  elseif(strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE 8')){ $id="ie8"; }
  elseif(strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE 9')){ $id="ie9"; }
  return $id;
}

?>
