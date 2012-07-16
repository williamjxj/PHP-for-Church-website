<?php
# instead in php.ini, set PEAR PATH here.
global $config;

define('SMARTY_DIR', SITEROOT.'include/Smarty-3.0.4/libs/');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('MDB2.php');

class BaseClass extends Smarty
{
  var $url, $self, $mdb2, $template_dir, $compile_dir, $config_dir, $cache_dir, $session;
  
  function __construct() 
  {
    parent::__construct();
    $this->url = $_SERVER["PHP_SELF"];
    $this->self = basename($this->url, '.php'); // will extend in sub-class.
    
    $this->mdb2 = $this->pear_connect_admin();
    $this->caching = false; //$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
    $this->auto_literal = true;
    $this->template_dir = SITEROOT.'themes/default/templates/';
    $this->compile_dir = SITEROOT.'templates_c/';
    $this->config_dir = SITEROOT.'configs/';
    $this->cache_dir = SITEROOT.'cache/';
  }

  public function pear_connect_admin() {
 
	if (preg_match("/^(192\.168|127\.)/", $_SERVER['REMOTE_ADDR']) || preg_match("/::1/", $_SERVER['REMOTE_ADDR'])) { 
		$dsn = array (
		);
	}
	else {
		$dsn = array (
		);
	}  
  
    $options = array(
      'debug'       => 2,
      'persistent'  => true,
      'portability' => MDB2_PORTABILITY_ALL,
    );
    $mdb2 = MDB2::factory($dsn, $options);
    if (PEAR::isError($mdb2)) {
      die($mdb2->getMessage());
    }
	$mdb2->query("SET NAMES 'utf8'");
    return $mdb2;
  }

  function check_email($emailAddress) {
    if (preg_match('/\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $emailAddress)){
      $emailArray = explode("@",$emailAddress);
      if (checkdnsrr($emailArray[1])){
        return TRUE;
      }
    }
    return false;
  }

  function get_date($date) {
    return preg_match("/YYYY-MM-DD/i", $date) ? '' : $date;
  }  

  function clear_array($ary) {
      if(is_array($ary) && count($ary)>0){
        foreach($ary as $key=>$data) {
          $ary[$key] = '';
          unset($ary[$key]);
        }
      }
  }

  //return rand(1,99)*time(); //bigint instead of varchar(32);
  function get_session() {
  return session_id();
  }

  function set_default_config($array) {
  global $config;
  foreach($array as $k=>$v) $config[$k] = $v;
  }
  // new for the front-side.
  function set_logfile() {
    $log = SITEROOT.LOG_FILE;
    if (is_file()) {
    $fh = fopen($log, 'a') or die("can't open file: ".__FILE__.__LINE__);
  }
  else {
    $fh = fopen($log, 'w') or die("can't open file: ".__FILE__.__LINE__);    
  }
  fwrite($fh, $str);
  fclose($fh);
  }
  function print_logfile($vars) {
    global $config;
    if (!isset($config['debug']) || (! $config['debug']) ) return;
  if(is_array($vars) || is_object($vars)) { echo "<pre>"; print_r($vars); echo "</pre>"; }
  else echo $vars."<br>\n";
  }

  function get_env() {
    if(isset($_SERVER['SERVER_SOFTWARE'])) {
    if(preg_match('/Win32/i', $_SERVER['SERVER_SOFTWARE'])) return 'Windows';
    return 'Unix';
    }
  }
  function set_breakpoint()
  {
  $fh = fopen(SITEROOT.BREAKPOINT, 'w') or die("can't open file");
  fwrite($fh, $this->url);
  fclose($fh);
  }

  function get_html_template($assign=NULL)
  {
    global $config;
    if($assign) $this->html = $config['path'].'/templates/'.$assign;
    elseif(! $this->html) $this->html = $config['path'].'/templates/'.DEFAULT_LIST;
  return $this->html;
  }
}
?>
