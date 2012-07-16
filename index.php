<?php
session_start();
error_reporting(E_ALL);
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';

define("SITEROOT", "./");
require_once(SITEROOT."configs/config.inc.php");
require_once(SITEROOT."configs/base.inc.php");

global $config;
define ('LOGO', 250);
define ('BANNER', 253);
define ('INTRO', 254);
define ('DEFAULT_CONTENT', 255);

class SupportClass extends BaseClass
{
  var $sid, $url, $self, $ignore_ary, $default_mid;
  public function __construct($site_id) {
   parent::__construct();
   $this->sid = $site_id;
   $this->url = $_SERVER['PHP_SELF'];
   $this->self = basename($this->url, '.php');
   $this->ignore_ary = array(BANNER,INTRO,LOGO); //banner, introduction are ignored. 
   $this->default_mid = $this->get_default_mid();
  }

  /**
   * in table, weight=255 means it is the default 'Home Page', or 'Default Module' when launch. site['id']=1
   */
  function get_default_mid() 
  {
  $sql = "SELECT m.mid FROM modules m 
 INNER JOIN pages_modules pm ON (pm.mid=m.mid) 
 INNER JOIN pages p ON(p.pid=pm.pid) 
 WHERE p.weight=255 AND m.weight=255 AND m.site_id=".$this->sid;
  $res = $this->mdb2->queryOne($sql);
  if (PEAR::isError($res)) {
    die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
  }
  return $res;
  }

 function get_modules_list_header() {
  $ary = array();
  $sql = "SELECT m.mid, m.name, m.url, m.url_flag, m.weight, m.left1, m.right3, m.submenu
 FROM modules m INNER JOIN pages_modules pm ON (pm.mid=m.mid) 
 INNER JOIN pages p ON(p.pid=pm.pid)
 WHERE p.weight=255 AND m.active='Y' AND m.site_id=".$this->sid." 
 AND (m.weight not between 100 and 199)
 ORDER BY m.weight ";
  $res = $this->mdb2->query( $sql );
  if (PEAR::isError($res)) {
    die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
  }
  while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    if($row['weight']==255) array_unshift($ary, $row);
    else array_push($ary, $row);
  }
  return $ary;
 }

 function get_modules_list_footer() {
  $ary = array();
  $sql = "SELECT m.mid, m.name, m.url, m.url_flag, m.weight, m.left1, m.right3, m.submenu
 FROM modules m INNER JOIN pages_modules pm ON (pm.mid=m.mid) 
 INNER JOIN pages p ON(p.pid=pm.pid)
 WHERE p.weight=255 AND m.active='Y' AND m.site_id=".$this->sid." 
 AND (m.weight between 100 and 199)
 ORDER BY m.weight ";
  $res = $this->mdb2->query( $sql );
  if (PEAR::isError($res)) {
    die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
  }
  while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    if($row['weight']==255) array_unshift($ary, $row);
    else array_push($ary, $row);
  }
  return $ary;
 }

 function get_header_submenu($mid)
 {
  $ary = array();
  if (! isset($mid)) $mid = $this->default_mid;
  $sql = "SELECT cid, linkname, author FROM contents WHERE mid=" . $mid . "
   AND site_id=".$this->sid . "
   AND weight NOT IN (". implode(',', $this->ignore_ary) .") ORDER BY cid DESC";
  $res = $this->mdb2->query($sql);
  if (PEAR::isError($res)) {
    die($res->getMessage().' - line '.__LINE__.': '.$sql);
  }
  while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
    $ary[$row['cid']] = $row['linkname'];
  return $ary;
 }

 function get_footer_submenu($mid)
 {
  $ary = array();
  if (! isset($mid)) $mid = $this->default_mid;
  $sql = "SELECT cid, linkname, author FROM contents WHERE mid=" . $mid . "
   AND site_id=".$this->sid . "
   AND weight NOT IN (". implode(',', $this->ignore_ary) .") ORDER BY cid ";
  $res = $this->mdb2->query($sql);
  if (PEAR::isError($res)) {
    die($res->getMessage().' - line '.__LINE__.': '.$sql);
  }
  while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
    $ary[$row['cid']] = $row['linkname'];
  return $ary;
 }

  
 function get_header() {
  $all = array();
  $ary = $this->get_modules_list_header();
  foreach($ary as $h) {
    $mid = $h['mid'];
    $all[$mid] = $h;
    if($h['submenu']=='Y')
      $all[$mid]['submenu'] = $this->get_header_submenu($mid);
  }
  return $all;
 }
  
 function get_footer() 
 {
  $all = array();
  $ary = $this->get_modules_list_footer();
  foreach($ary as $h) {
    $mid = $h['mid'];
    $all[$mid] = $h;
    if($h['submenu']=='Y')
      $all[$mid]['submenu'] = $this->get_footer_submenu($mid);
  }
  return $all;
 }

 // contents.weight=250, 1 site has only 1 logo.
 function get_logo() 
 {
  $sql = "SELECT content FROM `contents` WHERE site_id=".$this->sid." AND active='Y' AND weight=".LOGO;
  $res = $this->mdb2->queryOne($sql);
  if (PEAR::isError($res)) {
    die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
  }
  return $res;
 }

 // contents.weight=253, 1 module can has a banner(s).
 function get_banner($mid='') {
  $ary = array();
  if (!$mid) $mid = $this->default_mid;
  $sql = "SELECT cid, linkname, author, content FROM `contents` WHERE mid=".$mid."
   AND site_id=".$this->sid . "
   AND active='Y' AND weight=".BANNER;

  $res = $this->mdb2->query($sql);
  if (PEAR::isError($res)) {
    die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
  }
  while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $t = $row['content']."\n";
    array_push($ary, $t);
  }
  return $ary;
 }

 /**
  * contents.weight = 254
  * if (!$mid) $mid = $this->default_mid;
  * $sql = "SELECT cid, linkname, author, content FROM `contents` WHERE mid=".$mid." AND active='Y' AND weight=".INTRO;
  */
 function get_intro($mid='') 
 {
  $ary = array();
  $sql = "SELECT cid, linkname, author, content FROM `contents` WHERE mid=".$mid." AND active='Y' AND weight=".INTRO;

  $res = $this->mdb2->query($sql);
  if (PEAR::isError($res)) {
    die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
  }
  while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    array_push($ary, $row['content']);
  }
  return $ary;
 }
 
 function get_latest_10($mid='') 
 {
  $ary = array();
  $url = $this->url . '?js_latest_content=1&cid=';
  $sql = "SELECT cid, linkname, author, updated, mname
   FROM `contents` WHERE site_id=".$this->sid . " ORDER BY updated DESC, cid DESC LIMIT 0, 10 ";
  $res = $this->mdb2->query($sql);
  if (PEAR::isError($res)) {
    die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
  }
  array_push($ary, "<h3>最近10条信息</h3>");
  array_push($ary, "<ul>");
  while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $t1 = $row['linkname'] . ' - ' . $row['author'] . ' ('. $row['updated'] . ') 〖 ' . $row['mname'] . ' 〗';
    $t = '<li><a rel="group_new" href="'.$url.$row['cid'].'">'.$t1.'</a></li>';
    array_push($ary, $t);
  }
  array_push($ary, "</ul>");
  return $ary;
 }

  function  get_latest_content($cid) {
    $sql = "SELECT content FROM contents WHERE active='Y' AND cid = " . $cid;
    $res = $this->mdb2->queryOne($sql);
    if (PEAR::isError($res)) {
      die($res->getMessage().' - line '.__LINE__.', '.$sql);
    }
	return $res;
  }

  function get_content($cid) {
    $pattern = array("/\<p>\&nbsp;\<\/p>/", "/\<p>\s*\<\/p>/", "/<p>\&nbsp;<\/p>/");
    $sql = "SELECT linkname, author, date(updated) as created, content FROM contents WHERE active='Y' AND cid = " . $cid;
    $res = $this->mdb2->query($sql);
    if (PEAR::isError($res)) {
      die($res->getMessage().' - line '.__LINE__.', '.$sql);
    }
    while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
      $t1 = preg_replace($pattern, '', $row['content']);
      $t2 = '<h4>'.$row['linkname'].' - 〖 ' . $row['author'] . ' 于 ' . $row['created']. '〗</h4>'."\n";
      $t2 .= $t1;
    }
    return $t2;
  }  
  //$sql = "SELECT cid, linkname, author, content FROM `contents` WHERE mid=".$mid." AND weight IN (". implode(',', $this->ignore_ary) .") ORDER BY cid";
  function get_default_content_by_mid($mid='') {
    $pattern = array("/\<p>\&nbsp;\<\/p>/", "/\<p>\s*\<\/p>/", "/<p>\&nbsp;<\/p>/");
    $ary = array();
    if (!$mid) $mid = $this->default_mid;

    $sql = "SELECT cid, linkname, author, date(updated) as created, content FROM `contents` WHERE mid=".$mid." AND active='Y' AND weight = ". DEFAULT_CONTENT . " ORDER BY updated DESC, cid DESC";
    $res = $this->mdb2->query($sql);
    if (PEAR::isError($res)) {
      die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
    }
    while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
      $t = '<h4>'.$row['linkname'].' - 〖 ' . $row['author'] . ' 于 ' . $row['created']. ' 〗</h4>'."\n";
      $t .= preg_replace($pattern, '', $row['content'])."\n";
      array_push($ary, $t);
    }
    if(empty($ary)) {
      $sql = "SELECT cid, linkname, author, date(updated) as created, content FROM `contents` WHERE active='Y' AND mid=".$mid." AND weight<200 ORDER BY updated DESC, cid DESC ";
      $row = $this->mdb2->queryRow($sql);
      $t = '<h4>'.$row[1].' - 〖 ' . $row[2] . ' 于 ' . $row[3]. ' 〗</h4>'."\n";
      $t .= preg_replace($pattern, '', $row[4]);
      array_push($ary, $t);
    }
    return $ary;
 }

 function get_left($mid=NULL) {
  $ary = array();
  if (! isset($mid)) $mid = $this->default_mid;
  $sql = "SELECT cid, linkname, author, date(updated) as created FROM contents WHERE mid=".$mid." AND active='Y' AND weight NOT IN (".implode(",",$this->ignore_ary).") ORDER BY updated DESC, cid DESC limit 0,20";
  $res = $this->mdb2->query($sql);
  if (PEAR::isError($res)) {
    die($res->getMessage().' - line '.__LINE__.': '.$sql);
  }
  while ($row = $res->fetchRow()) {
    array_push($ary, $row);
  }
  return $ary;
 }

 function get_right($mid=NULL) {
   return array();
   $ary = array();
   if (! isset($mid)) $mid = $this->default_mid;
   $sql = "SELECT rid, file, author, concat(path,'/',file) as path
 FROM resources 
 WHERE mid = ".$mid."
 ORDER BY author, UPDATED DESC";

    $res = $this->mdb2->query( $sql );
    if (PEAR::isError($res)) {
     die($res->getMessage().' -line '.__LINE_.': '.$sql);
    }
    while ($row = $res->fetchRow()) {
      array_push($ary, $row);
    }
    return $ary;
  }


  function get_display_columns($mid) {
   $sql = "SELECT left1, right3 FROM modules WHERE mid=".$mid;
   $res = $this->mdb2->queryRow($sql);
  if (PEAR::isError($res)) {
    die($res->getMessage().' - line '.__LINE__.', '.$sql);
  }
  return $res;
  }  

  // module 'pictures' id=13
  function get_pictures_list() {
    $ary = array();
    $sql = "SELECT concat( path, '/', file ) AS photo, author, notes
        FROM resources
        WHERE mid = 13
        AND active = 'Y'
        AND TYPE LIKE 'image%'
        ORDER BY updated DESC
        LIMIT 0 , 30";

    $res = $this->mdb2->query( $sql );
    if (PEAR::isError($res)) {
      die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
    }
    while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
      array_push($ary, $row);
    }
    return $ary;
  }

  function update_logout_info() {
	$query = "update login_info set logout='Y', logout_time=NULL, session=NULL where session='".session_id()."'";
	$res = $this->mdb2->exec($query);
    if (PEAR::isError($res)) {
      die($res->getMessage(). ' - line ' . __LINE__ . ': ' . $sql);
    }
  }
}

//////////////////////////
if(isset($_SESSION[$config['browser']]['expire']) && (time()<$_SESSION[$config['browser']]['expire'])){
  session_unset();
  session_destroy();
  header("Location: " . LOGIN);
  exit;
}

try {
  $obj = new SupportClass($config['site_id']);
} catch (Exception $e) {
  echo $e->getMessage(), "line __LINE__.\n";
}

$config['url'] = $obj->url;
$config['self'] = $obj->self;
$config['path'] = SITEROOT . 'themes/default/';
define('TMP_DIR', $config['path'].'templates/');

if(isset($_GET['js_get_content'])) {
  $main = array();
  if(isset($_GET['cid'])) {
    $main['contents'] = $obj->get_content($_GET['cid']);
  }
  elseif(isset($_GET['mid'])) {
    $main['contents'] = $obj->get_default_content_by_mid($_GET['mid']);
  }
  $obj->assign('main', $main);
  $obj->display(TMP_DIR.'main.tpl.html');
}
elseif(isset($_GET['js_latest_content'])) {
  echo $obj->get_latest_content($_GET['cid']);
  exit;
}
elseif(isset($_GET['js_get_resource'])) {
  $obj->assign('right', $obj->get_right());
  $obj->display(TMP_DIR.'right.tpl.html');
}
elseif(isset($_REQUEST['js_fancybox'])) {
  $obj->assign('config', $config);
  $obj->assign('pictures', $obj->get_pictures_list());
  $obj->display(TMP_DIR.'fancybox.tpl.html');
}
elseif(isset($_GET['js_get_submenu'])) {
  $obj->get_header_submenu($_GET['mid']);
}
elseif(isset($_GET['js_get_module'])) {

  $mid = $_GET['mid'];
  $obj->assign('config', $config);
  
  $main = array();
  if(! isset($_GET['cid'])) {
    $main['banner'] = $obj->get_banner($mid);
    $main['intro'] = $obj->get_intro($mid);
    $main['contents'] = $obj->get_default_content_by_mid($mid);
    $obj->assign('main', $main);
  }
  else {
    $main['banner'] = $obj->get_banner($mid);
    $main['intro'] = $obj->get_intro($mid);
    $main['contents'] = $obj->get_content($_GET['cid']);
    $obj->assign('main', $main);
  }

  $left = isset($_GET['l'])?$_GET['l']:'';
  $right = isset($_GET['r'])?$_GET['r']:'';
  
  if ($left=='N' || $right=='N') {
    if ($left=='N' && $right=='N') {
      $obj->assign('left', array());
      $obj->assign('right', array());
    }
    else {
     if ($left=='N') {
      $obj->assign('left', array());
      $right = array();
      $right['nav'] = $obj->get_right($mid);
      $obj->assign('right', $right);
     }
     if ($right=='N') {
      $obj->assign('right', array());
      $left = array();
      $left['nav'] = $obj->get_left($mid);
      $obj->assign('left', $left);
     }
    }
  }
  else {
    $left = array();
    $left['nav'] = $obj->get_left($mid);
    $obj->assign('left', $left);
  
    $right = array();
    $right['nav'] = $obj->get_right($mid);
    $obj->assign('right', $right);
   } 
  
   $obj->assign('main_template', TMP_DIR.'main.tpl.html');
   $obj->assign('left_template', TMP_DIR.'left.tpl.html');
   $obj->assign('right_template', TMP_DIR.'right.tpl.html');
   $obj->display(TMP_DIR.'context.tpl.html');
}
else {
  if(isset($_GET['logout'])) {
	$obj->update_logout_info();
    session_unset();
    session_destroy();
  }

  $obj->assign('config', $config);
  
  $header = array();
  $header['logo'] = $obj->get_logo();
  $header['menu'] = $obj->get_header();
  $obj->assign('header', $header);
  
  $obj->assign('footer', $obj->get_footer());
  
  $main = array();
  $main['banner'] = $obj->get_banner();
  $main['intro'] = $obj->get_latest_10();
  $main['contents'] = $obj->get_default_content_by_mid();
  $obj->assign('main', $main);

  list($left1, $right3) = $obj->get_display_columns($obj->default_mid);

  $left = array();
  if (strcmp($left1, 'Y')==0) $left['nav'] = $obj->get_left();
  $obj->assign('left', $left);

  $right = array();
  if ($right3 == 'Y') $right['nav'] = $obj->get_right();
  $obj->assign('right', $right);

  $obj->assign('header_template', TMP_DIR.'header.tpl.html');
  $obj->assign('menu_template', TMP_DIR.'menu.tpl.html');
  $obj->assign('main_template', TMP_DIR.'main.tpl.html');
  $obj->assign('footer_template', TMP_DIR.'footer.tpl.html');
  $obj->assign('left_template', TMP_DIR.'left.tpl.html');
  $obj->assign('right_template', TMP_DIR.'right.tpl.html');
  
  $obj->display(TMP_DIR.'home.tpl.html');
}

?>
