<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

function getExportHeader($disFKeys, $server="", $db="") {
	return
		"--\n-- DbNinja v".VERSION." for MySQL\n--\n".
		"-- Dump date: ".date("Y-m-d H:i:s")." (UTC)\n".
		($server ? "-- Server version: {$server}\n" : "").
		($db ? "-- Database: {$db}\n" : "").
		"--\n\n".
		"/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n".
		"/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n".
		"/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n".
		"/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;\n".
		"/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n".
		"/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n".
		"/*!40101 SET NAMES utf8 */;\n".
		($disFKeys ? "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n" : "").
		"\n";
}

function getExportFooter($disFKeys) {
	return "\n".
		"/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n".
		"/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n".
		"/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n".
		"/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;\n".
		"/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n".
		"/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n".
		($disFKeys ? "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n" : "").
		"\n";
}
?>