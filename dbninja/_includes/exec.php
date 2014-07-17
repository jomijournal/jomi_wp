<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

require_once(__INC__.'/mysql.php');

$db = @$ARGS['db'];

if ($db && !$LINK->select_db($db))
	return 'Failed to select default database '.$ARGS['db'];

class SQLStatementReader {
	public $done = FALSE;
	public $error = '';
	public $offset = 0;

	private $start = 0;
	private $delim = ';';
	private $delimLen = 1;
	private $data = '';

	function __construct($fname, $encoding=NULL, $offset=0) {
		$this->encoding = $encoding;
		$this->fileOffset = $offset;
		$this->done = ($this->file = fopen($fname, 'r'))===FALSE;
		if (!$this->done)
			fseek($this->file, $offset);
	}

	private function moreData($len=10000) {
		$s = '';
		do {
			if (($q = fgets($this->file))===FALSE)
				break;
			$s.= $q;
		} while (strlen($s)<$len);
		if (!$s)
			return FALSE;
		if ($this->encoding)
			$s = mb_convert_encoding($s, 'UTF-8', $this->encoding);
		$this->data.= $s;
		return TRUE;
	}

	public function read() {
		while (!$this->done) {
			if ($this->data && preg_match("/-- .*|\#.*|\/\*(?!!)|'|\"|delimiter (.+)|".$this->delim."/im", $this->data, $match, PREG_OFFSET_CAPTURE, $this->offset)) {
				$m = $match[0][0];
				$this->offset = $match[0][1] + strlen($m);
				if ($m=="'" || $m=='"') {
					while (TRUE) {
						do {
							$i = strpos($this->data, $m, $this->offset);
							if ($i===FALSE)
								$this->offset = strlen($this->data);
							else
								break;
						} while ($this->moreData());
						if ($i===FALSE) {
							$this->error = "Can't find a closing quote after position {$this->offset}";
							$this->done = TRUE;
							return FALSE;
						}
						$this->offset = $i + 1;
						$c = 0;
						$i-= 1;
						for (; $i>=0; $i--) {
							if (substr($this->data, $i, 1)=="\\")
								$c++;
							else
								break;
						}
						if (!($c % 2))
							break;
					}
					continue;
				} else if ($m[0]=='#' || $m[0]=='-') {
					continue;
				} else if ($m=='/*') {
					do {
						if (preg_match('/\*\//', $this->data, $match, PREG_OFFSET_CAPTURE, $this->offset))
							break;
					} while ($this->moreData());
					if (!$match) {
						$this->error = "Comment at position {$this->offset} does not have a closing sequence";
						$this->done = TRUE;
						return FALSE;
					}
					$this->offset = $match[0][1] + 2;
					continue;
				} elseif (isset($match[1])) {
					$this->delimLen = strlen($match[1][0]);
					$this->delim = preg_replace('/[^\w]/', '\\\$0', $match[1][0]);
					if (!$this->delim) {
						$this->error = "Invalid delimiter specified at position ".$this->offset;
						$this->done = TRUE;
						return FALSE;
					}
					$this->start = $this->offset;
					continue;
				}
			} else {
				if ($this->moreData())
					continue;
				$this->offset = strlen($this->data);
				$this->done = TRUE;
				$this->delimLen = 0;
			}
			$r = substr($this->data, $this->start, $this->offset - $this->start - $this->delimLen);
			$r = preg_replace('/^[\s\t\r\n]+|[\s\t\r\n]+$/', '', $r);
			$this->start = $this->offset;
			if ($r)
				return $r;
		}
	}
}

$sf = $ARGS['file'].'-set';
if ($ARGS['offset']==0)
	@unlink($sf);

if ($s = file_get_contents($sf)) {
	if (!MySQLQuery::NoResultQuery($LINK, $s))
		return "Can't read statements from script file";
}

if (!($maxPack = MySQLQuery::SimpleQuery($LINK, 'select @@max_allowed_packet', TRUE)))
	return "Can't get the value of 'max_allowed_packet' MySQL variable";
$maxPack = $maxPack[0][0];

$z = new SQLStatementReader($ARGS['file'], @$ARGS['file_encoding'], @$ARGS['offset'] ?: 0);
$c = 0;
while (!$z->done) {
	if (($s = $z->read())===FALSE)
		return $z->error;
	if ($s && ($s = trim(preg_replace('/^(#|--).*/m', '', $s)))) {
		if (preg_match('/^USE\s+(.+)/i', $s, $m))
			$db = trim($m[1],'`');
		else {
			$s = preg_replace('/\/\*!\d+\s+|\s*\*\/$/', '', $s);
			if (preg_match('/^SET\s+.+/i', $s, $m))
				file_put_contents($sf, $m[0].";\n", FILE_APPEND);
		}
		$l = strlen($s);
		if ($l >= $maxPack)
			return "Statement beginning with '<b>".substr($s, 0, 100)."...</b>'<br /> is too large ({$l} bytes). ".
					"Maximum packet size defined in MySQL server's configuration is {$maxPack} bytes. ".
					"<a href=\"http://dev.mysql.com/doc/refman/5.0/en/packet-too-large.html\" target=\"_blank\">More details here</a>";
		if (!MySQLQuery::NoResultQuery($LINK, $s))
			return $LINK->error;
	}
	if ($ARGS['step'] && ++$c==$ARGS['step'])
		break;
}

return array(
	'result' => "Successfully executed {$c} statement".($c>1 ? 's' : ''),
	'count' => $c,
	'offset' => $z->offset + $z->fileOffset,
	'done' => $z->done,
	'db' => $db
);
?>
