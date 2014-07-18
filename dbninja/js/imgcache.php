<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

header('Content-type: application/javascript');
chdir('../images');
$a = array('.','gui','icons');
$q = array();
foreach ($a as $i) {
	if (!($l = scandir("{$i}")))
		continue;
	foreach ($l as $f) {
		if (preg_match('/\.(gif|png)$/', $f))
			$q[] = "{$i}/{$f}";
	}
}
?>
(function() {
var a = ['<?php echo implode("','", $q) ?>'];
for (var i=0; i<a.length; i++) { var o = new Image(); o.src = "images/"+a[i]; }
})();
