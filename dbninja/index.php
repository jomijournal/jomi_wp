<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

if (($a = @scandir('_users'))===FALSE)
	exit("Error: directory '_users' is missing or inaccessible");
foreach ($a as $f) {
	if (substr($f, 0, 1)!='.' && is_dir('_users/'.$f) && file_exists('_users/'.$f.'/userdata.php'))
		break;
	$f = NULL;
}
if (!$f) {
	require '_includes/putes.php';
	exit;
}
error_reporting(0);
session_start();
require '_includes/dbninja.php';
require '_includes/common.php';

$err = TRUE;
if (($u = trim(@$_POST['uname'])) && ($p = trim(@$_POST['passwd']))) {
	require '_includes/auth.php';
	$err = Auth($u, $p);
} else if (isset($_SESSION['config'])) {
	require '_includes/auth.php';
	UnAuth();
}
if ($err) {
?>
<!DOCTYPE html>
<html>
<head>
<title>DbNinja MySQL Manager Login</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<meta name="robots" content="noindex,nofollow" />
<meta name="googlebot" content="noindex,nofollow" />
<link rel="stylesheet" type="text/css" href="css/styles.css" />
<link rel="stylesheet" type="text/css" href="css/login.css" />
<script src="js/lib/crypt.js"></script>
<script src="js/lib/browser.js"></script>
<script>
function validate() {
	var q = document.getElementById('uname'), w = document.getElementById('passwd');
	var u = q.value.replace(/^\s+|\s+$/g, ''), p = w.value.replace(/^\s+|\s+$/g, '');
	if (!u || !p) {
		alert('Please enter both user name and password');
		return false;
	}
	p = SHA1(p);
	sessionStorage.setItem('uname', u);
	sessionStorage.setItem('passwd', p);
	q.value = u; w.value = SHA1(p);
	return true;
}
function init() {
<?php echo is_string($err) ? 'alert("'.addslashes($err).'");' : ''?>
	var b=_BD.browser, v=_BD.version;
	if (!sessionStorage || (b=='Explorer' && v<9) || (b=='Chrome' && v<22) || (b=='Safari' && v<5.1) || (b=='Firefox' && v<15) || (b=='Opera' && v<12)) {
   		document.getElementById('main').innerHTML = 'DbNinja does not work on '+_BD.getFullName()+'.<br />Please use another HTML5 compatible browser.';
   		return;
   	}
	var a = document.getElementById('uname');
	a.focus(); a.select();
}
</script>
</head>
<body onload="init()">
<table id="loginTbl"><tr><td id="main" align="center">
<form id="frm" method="POST" onsubmit="return validate()">
<div id="loginBox" class="shadBox roundTop roundBottom shadow">
<img src="images/logo.png" alt="DbNinja" />
<div>
    Username<input id="uname" name="uname" type="text" value="<?php echo @htmlspecialchars($_POST['uname'])?>" tabindex="0" /><br /><br />
    Password<input id="passwd" name="passwd" type="password" value="" tabindex="0" />
    <div id="phSbmt"><input id="sbmt" type="submit" value="Submit" tabindex="0" /></div>
</div>
</div>
</form>
<br />
<a href="http://www.dbninja.com/?page=resources&z=201">Forgot your password?</a>
</td></tr></table>
</body>
</html>
<?php
exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>DbNinja MySQL Manager</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<meta name="author" content="Vayer Software Ltd. <support@dbninja.com>">
<meta name="copyright" content="(c) 2010-2013 Vayer Software Ltd.">
<link rel="stylesheet" type="text/css" href="css/styles.css?<?php echo VERSION?>" />
<link rel="stylesheet" type="text/css" href="js/lib/codemirror.css" />
<link rel="stylesheet" type="text/css" href="js/lib/util/dialog.css" />
<script src="js/lib/json2.js"></script>
<script src="js/lib/crypt.js"></script>
<script src="js/lib/codemirror.js"></script>
<script src="js/lib/util/search.js"></script>
<script src="js/lib/util/searchcursor.js"></script>
<script src="js/lib/util/dialog.js"></script>
<script src="js/lib/mysql.js"></script>
<script src="js/lib/swfobject.js"></script>
<script src="js/lib/diff_match_patch.js"></script>
<script src="js/imgcache.php"></script>
<script src="images/reg_icons.php"></script>
<script src="js/somecode.js?<?php echo VERSION?>"></script>
</head>
<body onload="VERSION='<?php echo VERSION?>';UID='<?php echo $_SESSION['config']['uid']?>';Init()" class="unselectable" onresize="HandleResize()" onbeforeunload="return HandleUnload(event)" onkeydown="HandleKeyDown(event)" oncontextmenu="return HandleSelect(event)" onselectstart="return HandleSelect(event)" onmousedown="HandleBodyMouseDown(event)">
<div id="header">for MySQL</div>
<div id="phTime" data-sticky=",30,13,"></div>
<div id="phLock" data-sticky=",7,7,"></div>
<div id="phHide" data-sticky="5,5,37,5">
<div id="phMenu" data-sticky="0,,0,"></div>
<div id="phIcons1" data-sticky="350,0,0,"></div>
<div id="phIcons2" data-sticky=",0,0,"></div>
<div data-sticky="187,,25,0" class="hSplitter" onmousedown="StartHSplitter(event,$$('phTree'),this,$$('phMain'))"></div>
<div id="phTree" data-sticky="0,,25,0"></div>
<div id="phMain" data-sticky="192,0,25,0"></div>
</div>
<div id="phSql" data-sticky="5,5,,5:,150" class="selectable"></div>
<div id="phIfr"></div>
<iframe id="ifrFu" name="ifrFu" src="about:blank"></iframe>
</body>
</html>
