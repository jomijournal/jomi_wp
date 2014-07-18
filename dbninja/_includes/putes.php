<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

session_start();
require "common.php";
require "dbninja.php";

$st = @$_POST['st'] ?: '0';
$stv = NULL;

function delTemp() { @array_walk(glob('_users/[0-9][0-9][0-9][0-9][0-9]'), create_function('&$v, $k', 'unlink($v);')); }

$err = array();
if (version_compare(PHP_VERSION, '5.3.0') < 0)
	$err[] = "PHP version 5.3.0 or higher is required. Your version is ".PHP_VERSION.".";
if (!extension_loaded('mysqli'))
	$err[] = "PHP installation is missing the 'mysqli' extension";
if (!function_exists('json_decode'))
	$err[] = "PHP installation is missing JSON functionality";
if (!isset($_SESSION))
	$err[] = 'Support for sessions is disabled in PHP';
if ($st=='0') {
	if (file_exists('_users')) {
		delTemp();
		if (@file_put_contents('_users/'.mt_rand(10000, 99999), '')===FALSE)
			$err[] = 'Directory "_users" cannot be written';
	} else
		$err[] = 'Directory "_users" does not exist';
}

if ($st=='2') {
	$f = @$_POST['fname'];
	if (!preg_match('/^\d{5}$/', $f) || !file_exists("_users/{$f}")) {
		$st = '1';
		$err = TRUE;
	}
} elseif ($st=='3') {
	$p = trim(@$_POST['passwd'] ?: '');
	if (strlen($p) < 6) {
		$st = '2';
		$err = TRUE;
	} else {
		$q = '_users/admin'; 
		if (!(file_exists($q) || @mkdir($q)) || !(file_exists("{$q}/temp") || @mkdir("{$q}/temp"))
			|| !@file_put_contents("{$q}/userdata.php", "<?php\nreturn ".var_export(array('uid'=>randString(20), 'passwd'=>sha1($p)), TRUE).";\n?>"))
		{
			$stv = '3';
			$err = TRUE;
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>DbNinja MySQL Manager | Setup</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="css/setup.css" />
</head>
<body>
<script>
function dis(i, f) { document.getElementById(i).disabled = f; }
if (location.protocol=='http:')
	document.write('<div class="alert alert-danger text-center center-block"><strong>Warning: You are not using a secure connection and data is sent unencrypted.</strong></div>');
</script>
<div class="panel panel-primary center-block" style="width:700px">
	<div class="panel-heading"><?php echo ($st=='0' && $err ? 'SETUP FAILED' : 'Step '.($st+1)); ?></div>
	<div class="panel-body">
	<form method="POST" class="form-inline" role="form">
		<input type="hidden" name="st" value="<?php echo $stv ?: $st+1; ?>" />
<?php
if ($st=='0') {
	if ($err) {
		foreach ($err as $e)
			echo "<div class=\"bs-callout bs-callout-danger\">{$e}</div>";
		echo '<div class="text-center"><a href="http://www.dbninja.com/?page=resources&z=102" target="_blank" class="text-center"><strong>Click here for details</strong></a></p></div>';
	} else {
?>
		<h3>Welcome to DbNinja v<?php echo VERSION; ?> Setup!</h3>
		<p>You must agree with the following terms of use in order to continue:</p>
		<iframe src="license.html"></iframe>
		<div class="text-center spacerV20">
			<label class="checkbox-inline"><input type="checkbox" onclick="dis('s',!checked)" /> I Agree</label>
			<button id="s" type="submit" class="btn btn-default spacerH10" disabled="disabled">Continue</button>
		</div>
<?php
	}
} elseif ($st=='1') {
?>
		Now we need to make sure you're the owner of this server. A file with a name that consists of <strong>5 digits</strong> has been created in &quot;<strong>'<?php echo basename(getcwd()); ?>/_users</strong>&quot; directory.
		Please enter the name of the file below.
<?php
	if ($err)
		echo '<div class="bs-callout bs-callout-danger">The file name you have entered is wrong. Please try again.</div>';
?>
		<div class="text-center spacerV20">
			<div class="form-group"><input type="text" class="form-control" id="fname" name="fname" placeholder="File name" /></div>
			<button id="s" class="btn btn-default" type="submit">Continue</button>
		</div>
		<script>document.getElementById('fname').focus();</script>
<?php
} elseif ($st=='2') {
?>
		Please choose a strong password for your account. We recommend you use capitals and numbers.
<?php
	if ($err)
		echo '<div class="bs-callout bs-callout-danger">This password is too short. Please use at least 6 characters.</div>';
?>
		<div class="text-center spacerV20">
			<div class="form-group"><input type="text" class="form-control" id="passwd" name="passwd" placeholder="Password (6 chars min)" /></div>
			<button id="s" class="btn btn-default" type="submit">Continue</button>
		</div>
		<script>document.getElementById('passwd').focus();</script>
<?php
} elseif ($st=='3') {
	if (!$err) {
		delTemp();
?>
		<h3>Congratulations, setup has completed successfully!</h3>
		<div class="bs-callout bs-callout-info">To log into DbNinja, use <strong style="color:red">admin</strong> as user name and the password you chose.</div>
		<div class="text-center spacerV20">
			<button id="s" class="btn btn-default" type="submit">Continue</button>
		</div>
<?php
	} else {
?>
		The following error occurred while creating the account:
		<div class="bs-callout bs-callout-danger">
			Directory &quot;<?php echo basename(getcwd()); ?>/_users&quot; cannot be written.
			<a href="http://www.dbninja.com/?page=resources&z=102" target="_blank">Click here for details</a>
		</div>
		<div class="text-center spacerV20">
			<button id="s" class="btn btn-default" type="submit">Retry</button>
		</div>
		<input type="hidden" name="passwd" value="<?php echo htmlspecialchars(@$_POST['passwd']); ?>" />
	</form>
<?php
	}
}
?>
	</form>
	</div>
</div>
</body>
</html>