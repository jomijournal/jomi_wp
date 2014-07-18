<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

if ($ARGS['db']) {
	if (!$LINK->select_db($ARGS['db']))
		return $LINK->error;
}

if (!($f = @fopen($ARGS['file'], "rb")))
	return "Can't read the data file {$ARGS['file']}";

$sep = stripcslashes($ARGS['separator']);
$enc = stripcslashes($ARGS['encloser']);
$nul = stripcslashes($ARGS['null_rep']);
$hex = $ARGS['hex'];
$off = $ARGS['offset'];
$lim = $ARGS['limit'];

$fen = $ARGS['file_encoding'];
if ($fen=='UTF-8')
	$fen = NULL;

$srcCols = array();
$trgCols = array();
$trgType = array();
foreach ($ARGS['columns'] as $k=>$v) {
	$trgCols[] = "`{$k}`";
	$srcCols[] = $v[0];
	$trgType[] = $v[1];
}

if ($enc) {
	$re1 = "/(?:^|\\{$sep})(".($nul ? $nul."|" : "")."\\{$enc}(?:[^\\{$enc}\\\\]|\\{$enc}\\{$enc}|\\\\.)*\\{$enc})/";
	$re2 = "/\\{$enc}\\{$enc}|\\\\{$enc}/";
}

require_once(__INC__.'/mysql.php');

if ($toFile = $ARGS['to_file'])
	$file = new TempFile();

$sqlPrefix = "{$ARGS['cmd']} {$ARGS['table']} (".implode(', ', $trgCols).") VALUES\n";
$rawLineCnt = $columnCnt = $dataLineCnt = 0;

$sql = '';

while (($a = fgets($f))!==FALSE) {
	if (($rawLineCnt++<$off) || !($a = trim($a, "\r\n")))
		continue;
	if ($fen)
		$a = mb_convert_encoding($a, 'UTF-8', $fen);
	if ($enc) {
		preg_match_all($re1, $a, $m);
		$a = $m[1];
	} else
		$a = explode($sep, $a);
	if (($c=count($a)) && $columnCnt) { if ($columnCnt!=$c) return "Inconsistent amount of columns in line {$rawLineCnt}"; } else $columnCnt = $c;
	$b = array();
	foreach ($srcCols as $i=>$n) {
		$v = $a[$n];
		if ($v==$nul)
			$v = 'NULL';
		else {
			if ($enc)
				$v = preg_replace($re2, $enc, substr($v, 1, -1));
			if ($trgType[$i]=='s' || ($trgType[$i]=='b' && !$hex))
				$v = '"'.addcslashes($v,'"').'"';
		}
		$b[] = $v;
	}
	$dataLineCnt++;
	$sql.= ($sql ? ",\n" : '').'('.implode(', ', $b).')';

	if (strlen($sql)>=0x80000) {
		if ($toFile) {
			$file->write($sqlPrefix.$sql.";\n");
		} else {
			if (!MySQLQuery::NoResultQuery($LINK, $sqlPrefix.$sql))
				return $LINK->error;
		}
		$sql = '';
	}

	if ($lim>0 && $dataLineCnt==$lim)
		break;
}
fclose($f);

if ($sql) {
	if ($toFile) {
		$file->write($sqlPrefix.$sql.";\n");
	} elseif (!MySQLQuery::NoResultQuery($LINK, $sqlPrefix.$sql))
		return $LINK->error;
}

return array('result'=>($toFile ? $file->name : TRUE));
?>
