<?php
session_start();
error_reporting(E_ALL);

require_once("configs/config.inc.php");
require_once("configs/base.inc.php");

$_SESSION['uid'] = 1;

class FAQClass extends BaseClass
{
  var $url, $mid;
  public function __construct() {
    parent::__construct();
    $this->mid = 60;
  }

  function get_left($mid=0)
  {
    if(!$mid) $mid = $this->mid;
    $ary = array();
    $sql = "SELECT cid, linkname, title FROM contents WHERE mid=".$mid." AND active='Y'";
    $res = $this->mdb2->query($sql);
    if (PEAR::isError($res)) {
      die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
    }
    while ($row = $res->fetchRow()) {
  array_push($ary, $row);
    }
    return $ary;
  }

  function get_header() {
    $ary = array(
	array($this->mid, 'Frequently Asked Questions', 'Frequently Asked Questions'), 
	array($this->mid, 'Ask A Question', 'Ask A Question')
    );
    $as = array();
    $count = 1;
    foreach($ary as $h) {
      $t = $_SERVER['PHP_SELF']."?js_get_module=1&mid=$h[0]";
      array_unshift($as, '<a id="tab'.$count++. '" class="current"  href="'.$t.'" title="' . htmlspecialchars($h[2]) . '" ><span></span><strong>'.htmlspecialchars($h[1]).'</strong></a>');
    }
    return $as; 
  }

  function get_contexts($mid=0)
  {
    if(!$mid) $mid = $this->mid;
    $ary = array();
    $sql = "SELECT cid, linkname, title, content FROM `contents` WHERE mid=".$mid." AND active='Y'";
    $res = $this->mdb2->query($sql);
    if (PEAR::isError($res)) {
      die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
    }
    while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
      $t = '<h4>'.$row['linkname'].'</h4>'."\n";
      $t .= '<a id="a_'.$row['cid'].'" class="anchor"></a>'."\n";
      $t .= $row['content']."\n";
      array_push($ary, $t);
    }
    return $ary;
  }

  function get_content($cid) {
    $sql = "SELECT content FROM contents WHERE cid = " . $cid;
  $content = $this->mdb2->queryOne($sql);
  if (PEAR::isError($content)) {
    die($content->getMessage());
  }
  return $content;
  }

}

//////////////////////////
try {
  $obj = new FAQClass();
} catch (Exception $e) {
  echo $e->getMessage(), "line __LINE__.\n";
}

$_GET['mid'] = 60;
global $config;

if(isset($_GET['js_get_module'])) {
  $obj->assign('config', $config);
  $main = $obj->get_contexts($_GET['mid']);
  $questions = $obj->get_left($_GET['mid']);

  $obj->assign('main', $main);
  $obj->assign('questions', $questions);
  $obj->display($config['path'].'templates/faq.tpl.html');
}
else if(isset($_GET['js_get_form'])) {
  echo 'abcdef';
}
else {
  $obj->assign('config', $config);
  $obj->assign('header', $obj->get_header());
  $obj->assign('main', $obj->get_contexts());

  $obj->assign('questions', $obj->get_left());
  $obj->assign('left', $obj->get_left());

  $obj->display($config['path'].'templates/faq.tpl.html');
}

?>
