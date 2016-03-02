<?php
/*
Wordpress leading whitespace fix
================================
Ever got the infamous "xml declaration not at start of external
entity" error instead of your RSS feed when using Wordpress?

Well, you're not alone. I've spent couple hours tracking down
which of the active Wordpress plugins/themes broke my RSS feed.

When the same situation repeated again on a different blog,
my patience ran out... and I wrote this script that takes
care of the issue once and for all.

If you suffer with the same problem, download the plaintext
version and follow install instructions to free yourself from
the whitespace tyranny. ;)

Download
--------
Plaintext version: http://wejn.org/stuff/wejnswpwhitespacefix.php
Syntax colored: http://wejn.org/stuff/wejnswpwhitespacefix.php.html

Requirements
------------
Works with PHP5 only, as the headers_list() function is missing
in PHP4 which makes output Content-Type detection impossible.

Installation
------------
Either use this as auto_prepend in your .htaccess:

php_value "auto_prepend_file" /path/to/wejnswpwhitespacefix.php

or include it as first thing in Wordpress' index.php file even
before that "short and sweet" line:

<?php
include("wejnswpwhitespacefix.php");
// Short and sweet
define('WP_USE_THEMES', true);
require('./wp-blog-header.php');
?>

Note: For the .htaccess way your AllowOverride must include
"Options" (or better yet, be set to "All"); otherwise all you'll
be getting is "Internal Server Error".

Hint from Eric Auer about tracking down the source of the bug
-------------------------------------------------------------
Recently I received following tip from Eric Auer {eric dot auer at mpi dot nl}
which I post in it's entirety here because it's that good:

	Dear Wejn,

	thanks for writing wejnswpwhitespacefix.php! Apart from using
	it as FIX in the way described in your code, the fix can also
	be used as TOOL to DEBUG whitespace problems. Here is how:

	1. download your script

	2. do not put the include("wejnswpwhitespacefix.php"); at
	   the recommended location, but put it at a LATER place

	3. whenever the script is included BEFORE whatever sends
	   the extra whitespace, it fixes the symptom. Otherwise,
	   it cannot fix the symptom.

	4. move around the include and watch when there is, or is
	   not, extra whitespace (eg. view HTML source or RSS XML)

	5. you found the offending PHP file :-)

	In our case, I went via wp-blog-header.php, wp-load.php,
	wp-config.php to wp-settings.php and found that things
	broke at the moment when TEMPLATEPATH/functions.php got
	loaded. It turned out that our "devio" theme functions.php
	file had whitespace after a final ?> tag. Because outside
	the <? and ?> php tags, everything is raw content, that
	whitespace got added to every webpage and feed. Yuck! By
	either removing the whitespace or the whole ?> tag, the
	problem went away. Great!

	Maybe this trick is worth mentioning in the "user manual"
	of your whitespace fix, for those who do not only want to
	work around the problem but find the source of it as well.

	Regards, Eric

Creds
-----
Author: Michal "Wejn" Jirků {box at wejn dot org}
License: MIT
Version: 2.1
Changelog:
- Added better mime-type detection
- Now works even when C-T header not set
- Changed intro text to better target keywords
- [2.1] Added tip from Eric Auer
*/

function ___wejns_wp_whitespace_fix($input) {
	/* valid content-type? */
	$allowed = false;

	/* found content-type header? */
	$found = false;

	/* we mangle the output if (and only if) output type is text/* */
	foreach (headers_list() as $header) {
		if (preg_match("/^content-type:\\s+(text\\/|application\\/((xhtml|atom|rss)\\+xml|xml))/i", $header)) {
			$allowed = true;
		}

		if (preg_match("/^content-type:\\s+/i", $header)) {
			$found = true;
		}
	}

	/* do the actual work */
	if ($allowed || !$found) {
		return preg_replace("/\\A\\s*/m", "", $input);
	} else {
		return $input;
	}
}

/* start output buffering using custom callback */
ob_start("___wejns_wp_whitespace_fix");
