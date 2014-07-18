<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

date_default_timezone_set('UTC');
error_reporting(0);
set_time_limit(0);

define('__INC__', dirname(__FILE__).'/_includes');
require(__INC__.'/dbninja.php');

if (isset($_GET['online'])) {
	require(__INC__.'/online.php');
	exit;
}

if (isset($_GET['sessid']))
	session_id($_GET['sessid']);
session_start();

$config = & $_SESSION['config'];

function respond($o, $encr=NULL) {
    global $config;
	header('Content-Type: text/plain');
	$o = is_string($o) ? $o : json_encode($o);
	if ($encr===TRUE || ($encr===NULL && $config['encrypt'])) {
		$a = str_split(uniqid()); shuffle($a); $a = sha1(implode('', $a));
		$k = & $_SESSION['key1'];
		$o = '!:'.bin2hex(rc4($k, '!:'.$a."\n".$o));
		$_SESSION['key2'] = $k;
		$k = $a;
	}
	exit($o);
}

function respondError($s, $js=FALSE, $encr=NULL) {
	if ($js) respond("throw 'Error: ".str_replace(array("\r","\n"),array("\\r","\\n"), addslashes($s))."'");
	respond(array('error'=>$s, 'version'=>VERSION), $encr);
}

function respondFile($fn, $pref='', $ext='') {
	$a = array('file'=>$fn, 'alias'=>($pref ? $pref.'-' : '').date('Ymd-His').($ext ? '.'.$ext : ''));
    respond($a);
}

$act = @$_GET['action'];
if ($act!='auth') {
	if (!$config)
		respondError('session', FALSE, FALSE);
	if (!chdir("./_users/{$_SESSION['uname']}/temp"))
		respondError("Can't change working path to the temporary directory (username: {$_SESSION['uname']})");
}

require(__INC__.'/crypt.php');

if ($act) {
	if ($fn = $_GET['file']) {
		if (!preg_match('/^[0-9a-z\.\-]+$/i', $fn))
			respond('Bad file name requested');
	}
	switch ($act) {
	case 'auth':
		require(__INC__.'/auth.php');
		if ($t = Auth($_POST['uname'], $_POST['passwd']))
			respondError($t);
		respond($config, TRUE);

	case 'ver':
		respond(array('version'=>VERSION), FALSE);

	case 'upload':
		if (!isset($_FILES['Filedata'])) {
			exit("The file was not uploaded. Please make sure 'post_max_size' directive in php.ini is set to a high enough value.");
		} else if ($_FILES['Filedata']['error'] > 0) {
			$e = array(
				1=>"The uploaded file exceeds the 'upload_max_filesize' directive in php.ini file.",
				2=>"The uploaded file exceeds the 'upload_max_filesize' or 'post_max_size' directive in php.ini file.",
				3=>'The uploaded file was only partially uploaded.',
				4=>'No file was uploaded.',
				6=>'Missing a temporary folder.',
				7=>'Failed to write file to disk.',
				8=>'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.'
			);
			exit($e[$_FILES['Filedata']['error']]);
		} else {
			$fn = uniqid('u-');
			if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $fn))
				echo "file:{$fn}";
			else
				echo "Can't write file {$fn}";
		}
		break;

	case 'download':
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="'.(isset($_GET['alias']) ? $_GET['alias'] : $_GET['file']).'"');
		header('Content-length: '.filesize($fn));
		header('Expires: 0');
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		ob_clean();
		flush();
		readfile($fn);
		unlink($fn);
		break;

	case 'imgview':
		$a = getimagesize($fn);
		if (!$a)
			exit('Not image data');
		header('Content-Type: '.$a['mime']);
		readfile($fn);
		break;

	case 'linespreview':
		if (!($f = @fopen($fn, "r")))
			respondError("Can't open the data file");
		$a = array();
		while (($s = fgets($f))!==false) {
			$s = preg_replace('/[\r\n]+$/', '', $s);
			if (!$s)
				continue;
			if ($_GET['enc'])
				$s = mb_convert_encoding($s, 'UTF-8', $_GET['enc']);
			$a[] = $s;
			if (count($a)>9)
				break;
		}
		fclose($f);
		respond($a);

	case 'binview':
		require(__INC__.'/common.php');
		$s = file_get_contents($fn, false, NULL, 0, 1048560);
		if (@$_GET['hex']) {
			echo '<html><head><meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1"></head>'.
				 '<body style="margin:5px;font-size:13px;font-family:monospace">'.hexdump($s).'</body></html>';
		} else {
			header('Content-Type: text/plain');
			echo $s;
		}
		break;
	}
	exit;
}

//////////

if ($req = file_get_contents('php://input')) {
	if (substr($req, 0, 2)=='!:') {
	    $s = substr($req, 2);
		if (($req = json_decode(rc4($_SESSION['key1'], pack('H*', $s)), TRUE))===NULL && isset($_SESSION['key2'])) {
		    if (($req = json_decode(rc4($_SESSION['key2'], pack('H*', $s)), TRUE))===NULL)
		        respondError('session', FALSE, FALSE);
	        $_SESSION['key1'] = $_SESSION['key2'];
		}
		unset($s);
	} else {
	    if (($req = json_decode($req, TRUE))===NULL)
	    	respondError('bad data received from client', TRUE);
	}
} else
	exit;

require(__INC__.'/mysql.php');

function getLink($cid) {
	global $config;
	foreach ($config['hosts'] as $o) {
		if ($o['cid']==$cid)
			break;
		$o = NULL;
	}
	if (!$o) return;
	$link = GetMySQLLink($o['host'], $o['uname'], ($o['passwd']===NULL ? $o['tempPasswd'] : $o['passwd']), $o['port']);
	if (is_string($link))
		respondError($link, TRUE);
	return $link;
}

function runPlugin(&$ARGS) {
	$LINK = getLink($ARGS['cid']);
    $q = include(__INC__.'/'.$ARGS['plugin'].'.php');
	if (is_string($q))
     	respondError($q, TRUE);
    return $q;
}

if (isset($req['file']) && !preg_match('/^[0-9a-z\-\.]+$/i', $req['file']))
	respondError('Illegal file name received', TRUE);

try {
	switch ($act = $req['action']) {
	case 'query':
		$c = getLink($req['cid']);
		if ($req['db'])
			$c->select_db($req['db']);
		$q = & $req['query'];
		$p = & $req['params'];

		if ($req['limited']) {
			$pageSize = $config['queryPageSize'];
			$cellSize = $config['queryMaxCellSize'];
			$dataSize = $config['queryMaxDataSize'];
			$rowsAmnt = $config['queryMaxRows'];
		} else
			$pageSize = $cellSize = $dataSize = $rowsAmnt = 0;

		$a = new MySQLQuery($c, ($req['limited'] ? new QueryResultJSONWriter($pageSize) : NULL), $cellSize, $dataSize, $rowsAmnt);
		if (is_array($q)) {
			$aff = 0;
			for ($i=0; $i<count($q); $i++) {
				$r = $a->query($q[$i], $p[$i], $req['flags']);
				$aff+= $r['affectedRows'];
			}
			$a = array('affectedRows'=>$aff);
		} else {
			$a = $a->query($q, $p, $req['flags']);
		}
		if ($req['toFile'] && $a['results']) {
			$f = new TempFile();
			$f->write($a['results'][0]['data'][0][0]);
			$a = array('file'=>$f->name, 'alias'=>'cell-'.date('Ymd-His').'.data');
		}
		respond($a);
		break;

	case 'get_query_result':
		$f = $req['file'].'-'.$req['result'].'-'.$req['page'];
		if (($s = file_get_contents($f))===FALSE)
			respondError("Can't read file: {$f}");
		respond($s);
		break;

	case 'kill_query_result':
		array_walk(glob($req['file'].'-*'), create_function('&$v, $k', 'unlink($v);'));
		break;

	case 'ping':
		$c = getLink($req['cid']);
		if (!$c->ping())
			respondError('MySQL server did not respond to a keep-alive message. You may need to reconnect the server.');
		break;

	case 'load_config':
		respond($config, TRUE);
		break;

	case 'save_config':
		$a = $req['config'];
		$a['passwd'] = ($a['passwd'] ? $a['passwd'] : $config['passwd']);
		$a['uid'] = $config['uid'];
		if (!file_put_contents('../userdata.php', "<?php\nreturn ".var_export($a, TRUE).";\n?>"))
			respondError("can't write userdata.php file", TRUE);
		foreach ($a['hosts'] as &$i) {
			if (!isset($i['cid'])) $i['cid'] = uniqid();
			foreach ($config['hosts'] as &$j) {
				if ($j['tempPasswd'] && $i['cid']==$j['cid'])
					$i['tempPasswd'] = $j['tempPasswd'];
			}
		}
		$config = $a;
		respond($config, TRUE);
		break;

	case 'server_info':
		$a = array('phpVersion'=>phpversion(), 'os'=>php_uname('s'), 'host'=>php_uname('n'), 'time'=>date('U')+0, encodings=>NULL);
		foreach (array('file_uploads', 'memory_limit', 'post_max_size', 'upload_max_filesize') as $i)
			$a[$i] = ini_get($i);
		if (function_exists('mb_list_encodings')) {
			$e = array();
			foreach (mb_list_encodings() as $t) if ($t!='auto' && $t!='pass') $e[] = $t;
			sort($e, SORT_STRING);
			$a['encodings'] = $e;
		}
		respond($a);
		break;

	case 'set_temp_passwd':
		foreach ($config['hosts'] as &$o) {
			if ($o['cid']==$req['cid']) {
				$o['tempPasswd'] = $req['passwd'];
				break;
			}
		}
		break;

	case 'format_sql':
		require(__INC__."/sql-formatter/sql-formatter.php");
		respond(array('result'=>SqlFormatter::format($req['sql'], false)));
		break;

	case 'plugin':
		$t = runPlugin($req);
		switch($req['plugin']) {
			case 'import_data':
				if ($t['result']===TRUE)
					respond($t);
				respondFile($t['result'], 'import', 'sql');
				break;
			case 'export_db':
				respondFile($t['result'], $req['db'], 'sql');
				break;
			case 'export_data':
				respondFile($t['result'], 'data', strtolower($req['format']));
				break;
			case 'export_users':
				if ($req['to_file'])
					respondFile($t['result'], 'users', 'sql');
				else
					respond($t);
				break;
			case 'exec':
				respond($t);
				break;
		}
		break;

	case 'external':
		break;
	}
} catch (Exception $e) {
	respondError($e->getMessage());
}
?>