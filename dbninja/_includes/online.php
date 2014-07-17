<!DOCTYPE html>
<html>
<head>
<title>DbNinja for MySQL</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<link rel="stylesheet" type="text/css" href="css/styles-online.css" />
</head>
<body>
<?php
	$req = array('user'=>'admin', 'task'=>$_GET['task']);

	require(__INC__.'/auth.php');
	$conf = ReadConfig($req['user']);
	if (is_string($conf))
		exit($conf);

	if (!($tasks = include(AUTH_PATH."/{$req['user']}/tasks.php")))
		exit('No tasks are defind for this user');

	$task = NULL;
	foreach ($tasks as $t) {
		if ($t['tid']==$req['task']) {
			$task = $t;
			break;
		}
	}
	if (!$task)
		exit("Task #{$req['task']} was not found");

	$host = NULL;
	foreach ($conf['hosts'] as $h) {
		if ($h['cid']==$task['cid']) {
			$host = $h;
			break;
		}
	}
	if (!$host)
		exit("Connection with ID {$task['cid']} was not found");

	require(__INC__.'/mysql.php');
	$link = GetMySQLLink($host['host'], $host['uname'], $host['passwd'], $host['port'], $task['db']);
	if (is_string($link))
    	exit($link);

	$res = MySQLQuery($link, $task['sql'], NULL, 0, -1, 1024);
	if (is_string($res))
		exit($res);

	echo '<div>'.$task['title'].'</div>';

	$cnt = count($res['fields']);
	for ($i=0; $i<$cnt; $i++) {
		echo '<div class="t"><div class="r header">';
		foreach ($res['fields'][$i] as $f)
			echo '<div class="c">'.$f['fieldAlias'].'</div>';
		echo '</div>';
		foreach ($res['data'][$i] as $r) {
			echo '<div class="r">';
			foreach ($r as $c)
				echo '<div class="c">'.($c===NULL ? '<i>NULL</i>' : $c).'</div>';
			echo '</div>';
		}
		echo '</div>';
	}
?>
</body>
</html>
