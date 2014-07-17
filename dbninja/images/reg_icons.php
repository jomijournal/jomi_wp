<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

header('Content-type: application/javascript');
foreach (glob('icons/*') as $f) {
	$a = pathinfo($f);
	echo 'var IMG_'.strtoupper($a['filename'])." = 'images/{$f}';\n";
}
?>