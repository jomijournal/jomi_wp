<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

define('AUTH_PATH', './_users');
define('AUTH_MESS', 'Wrong username or password');

function ReadConfig($u) {
	if (!preg_match('/^[a-z0-9]{1,100}$/i', $u) || !file_exists($d = AUTH_PATH.'/'.$u))
		return AUTH_MESS;
	if ($s = include($d.'/userdata.php')) {
		if (is_array($s) || (is_string($s) && $s=unserialize($s)))
			return $s;
		return "Contents of userdata.php file don't make sense.";
	} else
		return "Can't read userdata.php file. Plase make sure the appropriate reading permissions are given.";
}

function Auth($u, $p) {
	if (is_string($conf = ReadConfig($u)))
		return $conf;
	if (sha1($conf['passwd'])==$p) {
		
		/* Version 3.2.0 updates */
		if (isset($conf['limitRows'])) {
			$conf['queryPageSize'] = $conf['limitRows'];
			unset($conf['limitRows']);
		}
		if (isset($conf['limitCell'])) {
			$conf['queryMaxCellSize'] = $conf['limitCell'];
			unset($conf['limitCell']);
		}
		/* * * * * * * * * * * */

		foreach (Array(
			'hosts'				=> Array(),
			'encrypt'			=> TRUE,
			'reqTimeout'		=> 60,
			'hideSysDBs'		=> FALSE,
			'showQueryRowNums'	=> TRUE,
			'queryMaxDataSize'	=> 20,
			'queryMaxRows'		=> 100000,
			'queryPageSize'		=> 100,
			'queryMaxCellSize'	=> 1024) as $k => $v) {
			if (!isset($conf[$k]))
				$conf[$k] = $v;
		}

		foreach ($conf['hosts'] as &$i)
			if (!isset($i['cid']))
				 $i['cid'] = uniqid();
		$_SESSION['uname'] = $u;
		$_SESSION['key1'] = $conf['passwd'];
		$_SESSION['config'] = $conf;

		Cleanup();
		return FALSE;
	} else
		return AUTH_MESS;
}

function Cleanup() {
	array_map('unlink', glob(AUTH_PATH.'/'.$_SESSION['uname'].'/temp/*'));
}

function UnAuth() {
	if (isset($_SESSION['uname'])) {
		Cleanup();
		session_unset();
	}
}
?>
