<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

$res = "--\n-- DbNinja v".VERSION." for MySQL\n-- Date: ".date("Y-m-d H:i:s")." (UTC)\n--\n\n";

foreach ($ARGS['users'] as $i) {
    $u = "'".addslashes($i['user'])."'@'".addslashes($i['host'])."'";
    if ($i['withDrop'])
        $res.= "DROP USER $u;\n";
    if ($i['withCreate'])
        $res.= "CREATE USER $u;\n";
    if ($i['withGrants'] && (!$i['withDrop'] || $i['withCreate'])) {
        $q = MySQLQuery::SimpleQuery($LINK, "SHOW GRANTS FOR {$u}", TRUE);
        if ($q===FALSE)
            return $LINK->error;
        $res.= $q[0][0].";\n";
    }
    $res.= "\n";
}
if ($ARGS['to_file']) {
	$f = new TempFile();
	$f->write($res);
}
return array('result'=>($ARGS['to_file'] ? $f->name : $res));
?>
