<?php

error_reporting(E_ALL ^ E_NOTICE);
session_start();
define('SITEROOT', './');
include_once(SITEROOT.'configs/config.inc.php');
global $config;

class Login
{
  var $site, $dbh;
  function __construct($site){
    $this->site = $site;
    $this->dbh = $this->mysql_connect_demo();
  }
  
  function mysql_connect_demo()
  {
    $db = mysql_pconnect(HOST, USER, PASS) or die(mysql_error());
    mysql_select_db(DB_NAME, $db);
    return $db;
  }

  function initial() {
  global $config;
?>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
  $('li.login_tab', '#signup_n_login_container').click(function() {
    if($('#signup_form_container').is(':visible')) {
	  $('#login_form_container').show();
	} 
  });

var form = $('#login_form');
$(form).submit(function(event){
  event.preventDefault();
    $.ajax({
      type: $(this).attr('method'),
      url: $(this).attr('action'),
      data: $(this).serialize(),
      dataType: 'json',
      beforeSend: function() {
        $('#msg').show();
      },
      success: function(data) {
        if(data instanceof Object) {
          document.location.href='/cadmin/index.php';
        }
        else {
          $('#msg').hide();
          if ($('#div1').length>0) {
            $('#div1').show();
          } else {
            $('<div></div>').attr({'id':'div1','class':'noUser'}).html("No such user, Please try again.").insertAfter(form);
            $('#username').select().focus();  
          }
          if(data instanceof Array) {
            alert('=============='+data+'--------------');
          }
        }
      }
    });
  return false;
});

  var t = $('div.site_browser').attr('id');
  var site = t.substring(0, t.indexOf('_'));
  var browser = t.substr(t.indexOf('_')+1);

  if( $.cookie(site+'['+browser+'][username]') && $.cookie(site+'['+browser+'][userpass]') ) {
    $('#username').val($.cookie(site+'['+browser+'][username]'));
    $('#password').val($.cookie(site+'['+browser+'][userpass]'));
    $('#rememberme').attr('checked', true);
  }
  else {
    $('#rememberme').attr('checked', false);
  }
  // $('#username').select().focus();  
});
</script>

<div class="site_browser" id="<?=$config['site'].'_'.browser_id();?>"></div>
<div id="signup_n_login_container">
  <div class="forms_container">
    <div id="login_form_container" class="form_container">
      <form method="post" class="cf" id="login_form" action="<?=$_SERVER['PHP_SELF'];?>">
        <div class="input first">
          <input type="text" placeholder="Username" id="username" name="user[username]" autocomplete="on">
        </div>
        <div class="input">
          <input type="password" placeholder="Password" id="password" name="user[userpass]" autocomplete="on">
        </div>
        <div class="input last">
          <input type="submit" value="Log In" class="submit_btn" />&nbsp;<input type="button" value="Close" onclick="$('#div_ls').hide();" />
          <span id="msg" name="msg" style="display: none"><img src="images/spinner.gif" width="16" height="16" border="0"></span> </div>
        <div class="input remember_me">
          <input type="checkbox" value="1" id="rememberme" name="user[rememberme]" class="checkbox_field">
          <span>Remember me</span> </div>
      </form>
    </div>
  </div>
</div>
<?
  }
  
  
  // user[pwd]	ILoveJesus, user[rememberme] 1, user[name]	OneFamily
  function check_user()
  {
    global $config;
    $username = mysql_real_escape_string(trim($_POST['user']['username']));
    $password =  $_POST['user']['userpass'];
    $rememberme = isset($_POST['user']['rememberme']) ? true : false;

    $query = "SELECT * FROM admin_users WHERE username='".$username."' AND password='" . $password . "'";
    $res = mysql_query($query);
    $total = mysql_num_rows($res);
    if ($total>0) {
      $username = ucfirst(strtolower($username));
      if($rememberme) {
        $expire = time() + 17280000;

        setcookie($this->site.'['.$config['browser'].'][username]', $username, $expire);
        setcookie($this->site.'['.$config['browser'].'][userpass]', $password, $expire);
      }
      else {
        setcookie($this->site.'['.$config['browser'].'][username]', NULL);
        setcookie($this->site.'['.$config['browser'].'][userpass]', NULL);
      }

      $row = mysql_fetch_assoc($res);
      $_SESSION[$this->site][$config['browser']]['username'] = $row['username'];
      $_SESSION[$this->site][$config['browser']]['expire'] = time() + 30*60;

      $this->update_login_info($username, $row['uid']);
      return $row;
    }
    return false;
  }

  function update_login_info($username, $uid)  {
    $ip = $this->get_real_ip();
	$browser = $this->get_browser();
	$session = session_id();
    $query = "insert into login_info(uid,ip,browser,username,session,count,login_time,logout,logout_time, expired)
      values(".$uid.", '".$ip."', '".$browser."', '".$username."', '".$session."', 1, NULL, 'N', '', NOW() + INTERVAL 10 HOUR)
      on duplicate key update
      count = count+1,
	  login_time = NULL,
	  expired = NOW() + INTERVAL 10 HOUR,
	  session = '".$session."', 
      logout='N',
	  logout_time=''";
	mysql_query($query);
  }

  function get_browser() {
	return $_SERVER["HTTP_USER_AGENT"];
  }
  function get_real_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    else
      $ip=$_SERVER['REMOTE_ADDR'];
    return $ip;
  }
  
  
}

//////////////////////////
$login = new Login($config['site']);

if (isset($_POST['user'])) {
  // echo "<pre>"; print_r($_POST); echo "</pre>"; $_post not work.
  $ret = $login->check_user();
  if($ret) echo json_encode($ret);
}
elseif(isset($_GET['logout'])) {
  session_unset();
  session_destroy();
  header('Location: index.php');
  exit;
}
else {
  $login->initial();
}

?>
