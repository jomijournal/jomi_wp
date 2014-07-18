<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

require_once(__INC__.'/mysql.php');
require_once(__INC__.'/export_stuff.php');

date_default_timezone_set("UTC");

$q = MySQLQuery::SimpleQuery($LINK, 'SELECT VERSION()', TRUE);
if ($q===FALSE)
	return $LINK->error;

$file = new TempFile(NULL, 'UTF-8', $ARGS['file_encoding']);

$db = $ARGS['db'];
$disFKeys = $ARGS['tables_data'] && $ARGS['no_foreign_keys'];
$file->write(getExportHeader($disFKeys, $q[0][0], $db));

function delimit(&$s) {
	if (strrchr($s, ";")) {
		$delim = "$$";
		while (strstr($s, $delim))
			$delim.= $delim;
		return "DELIMITER {$delim}\n\n{$s}{$delim}\n\nDELIMITER ;\n\n\n";
	}
	return $s.";\n\n\n";
};

if ($ARGS['with_create_db']) {
	if ($ARGS['with_drop_db'])
		$file->write("DROP DATABASE IF EXISTS `{$db}`;\n");
	$q = MySQLQuery::SimpleQuery($LINK, "SHOW CREATE DATABASE `{$db}`", TRUE);
	if ($q===FALSE)
		return $LINK->error;
	$file->write($q[0][1].";\n\n");
}

$file->write("USE `{$db}`;\n\n");

foreach ($ARGS['tables_struct'] as $t) {
	$file->write("--\n-- Structure for table: {$t}\n--\n");
	$q = MySQLQuery::SimpleQuery($LINK, "SHOW CREATE TABLE `{$db}`.`{$t}`", TRUE);
	if ($q===FALSE)
		return $LINK->error;
	if ($ARGS['with_drop_obj'])
		$file->write("DROP TABLE IF EXISTS `{$t}`;\n");
	$file->write($q[0][1].";\n\n\n");
}

foreach ($ARGS['views'] as $t) {
	$file->write("--\n-- Structure for view: {$t}\n--\n");
	if ($ARGS['with_drop_obj'])
		$file->write("DROP VIEW IF EXISTS `{$t}`;\n");
	$q = MySQLQuery::SimpleQuery($LINK, "SHOW CREATE VIEW `{$db}`.`{$t}`", TRUE);
	$file->write($q[0][1].";\n\n\n");
}

foreach ($ARGS['procs'] as $t) {
	$file->write("--\n-- Structure for procedure: {$t}\n--\n");
	if ($ARGS['with_drop_obj'])
		$file->write("DROP PROCEDURE IF EXISTS `{$t}`;\n");
	$q = MySQLQuery::SimpleQuery($LINK, "SHOW CREATE PROCEDURE `{$db}`.`{$t}`", TRUE);
	$file->write(delimit($q[0][2]));
}

foreach ($ARGS['funcs'] as $t) {
	$file->write("--\n-- Structure for function: {$t}\n--\n");
	if ($ARGS['with_drop_obj'])
		$file->write("DROP FUNCTION IF EXISTS `{$t}`;\n");
	$q = MySQLQuery::SimpleQuery($LINK, "SHOW CREATE FUNCTION `{$db}`.`{$t}`", TRUE);
	$file->write(delimit($q[0][2]));
}

foreach ($ARGS['events'] as $t) {
	$file->write("--\n-- Structure for event: {$t}\n--\n");
	if ($ARGS['with_drop_obj'])
		$file->write("DROP EVENT IF EXISTS `{$t}`;\n");
	$q = MySQLQuery::SimpleQuery($LINK, "SHOW CREATE EVENT `{$db}`.`{$t}`", TRUE);
	$file->write(delimit($q[0][3]));
}

if ($ARGS['tables_data']) {
	class QueryResultSQLWriter implements MySQLQueryDataProcessor {
		function __construct($link, $tempFile, $table, $groupStmts) {
			$this->link  = $link;
			$this->file  = $tempFile;
			$this->table = $table;
			$this->group =  $groupStmts;
			$this->recordCount = 0;
			$this->groupSize = 0;
		}

		public function handleResultStarted(&$fields) {
			$this->fields = $fields;
			$a = array();
			foreach ($fields as $f)
				$a[] = '`'.$f['field'].'`';
			$this->insert_stmt = "INSERT INTO `{$this->table}` (".implode(',', $a).") VALUES ";
		}

		public function handleRecord($data) {
			$a = array();
			foreach ($data as $i => $c) {
				if ($c===NULL)
					$a[] = 'NULL';
				elseif ($this->fields[$i]['binary'])
					$a[] = "X'".bin2hex($c)."'";
				elseif ($this->fields[$i]['numeric'])
					$a[] = $c;
				else
					$a[] = "'".mysqli_real_escape_string($this->link, $c)."'";
			}
			$a = '('.implode(',', $a).')';
			if ($this->group) {
				if ($this->groupSize>0) {
					$n = $this->groupSize + strlen($a) + 1;
					if ($n < 1048575) {
						$this->groupSize = $n;
						$this->file->write(','.$a);
					} else {
						$this->groupSize = 0;
						$this->file->write(";\n");
					}
				}
				if ($this->groupSize==0) {
					$a = $this->insert_stmt.$a;
					$this->groupSize = strlen($a);
					$this->file->write($a);
				}
			} else
				$this->file->write($this->insert_stmt.$a.";\n");
			$this->recordCount++;
		}

		public function handleResultEnded(&$result) {
			if ($this->recordCount) {
				if ($this->group)
					$this->file->write(";\n");
			} else {
				$this->file->write("-- Table contains no data\n");
			}
		}

		public function handleQueryEnded(&$response) {}
	}

	foreach ($ARGS['tables_data'] as $t) {
		$file->write(
			"--\n-- Data for table: {$t}\n--\n".
			($ARGS['with_lock'] ? "LOCK TABLES `{$t}` WRITE;\n" : '').
			"ALTER TABLE `{$t}` DISABLE KEYS;\n\n"
		);
		$w = new QueryResultSQLWriter($LINK, $file, $t, $ARGS['group_inserts']);
		$q = new MySQLQuery($LINK, $w);
		$q = $q->query("SELECT * FROM `{$db}`.`{$t}`");
		if (is_string($q))
			return $s;
		$file->write(
			"\nALTER TABLE `{$t}` ENABLE KEYS;\n".
			($ARGS['with_lock'] ? "UNLOCK TABLES;\n" : '').
			($ARGS['with_commit'] ? "COMMIT;" : '')."\n\n"
		);
	}
}

foreach ($ARGS['triggers'] as $t) {
	$file->write("--\n-- Structure for trigger: {$t}\n--\n");
	if ($ARGS['with_drop_obj'])
		$file->write("DROP TRIGGER IF EXISTS `{$t}`;\n");
	$q = MySQLQuery::SimpleQuery($LINK, "SHOW CREATE TRIGGER `{$db}`.`{$t}`", TRUE);
	$file->write(delimit($q[0][2]));
}

$file->write(getExportFooter($disFKeys));
return array('result'=>$file->name);
?>
