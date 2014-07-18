<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

define("FLAG_NOT_NULL", 1);
define("FLAG_PRI_KEY", 2);
define("FLAG_UNIQUE_KEY", 4);
define("FLAG_MULTIPLE_KEY", 8);
define("FLAG_BLOB", 16);
define("FLAG_UNSIGNED", 32);
define("FLAG_ZEROFILL", 64);
define("FLAG_BINARY", 128);
define("FLAG_ENUM", 256);
define("FLAG_AUTO_INCREMENT", 512);
define("FLAG_TIMESTAMP", 1024);
define("FLAG_SET", 2048);
define("FLAG_NUM", 32768);
define("FLAG_PART_KEY", 16384);
define("FLAG_GROUP", 32768);
define("FLAG_UNIQUE", 65536);

define('QUERY_WITH_FIELD_DETAILS', 1);
define('QUERY_WITH_INSERT_ID', 2);
define('QUERY_WITH_HASHING', 4);
define('QUERY_ONLY_FIELDS', 8);
define('QUERY_CALC_FOUND_ROWS', 16);

$FIELD_TYPES = array(
	0=>"number",    //decimal
	1=>"number",    //tiny
	2=>"number",    //short
	3=>"number",    //long
	4=>"number",    //float
	5=>"number",    //double
	6=>"null",      //null
	7=>"timestamp", //timestamp
	8=>"number",    //longlong
	9=>"number",    //int24
	10=>"date",     //data
	11=>"time",     //time
	12=>"datetime", //datetime
	13=>"year",     //year
	14=>"date",     //date
	15=>"text",     //text
	16=>"bit",      //bit
	246=>"number",  //newdecimal
	247=>"enum",    //enum
	248=>"set",     //set
	249=>"blob",    //tiny_blob
	250=>"blob",    //medium_blob
	251=>"blob",    //long_blob
	252=>"blob",    //blob
	253=>"text",    //var_string
	254=>"text",    //string
	255=>"geometry" //geometry
);

function GetMySQLLink($host, $uname, $passwd, $port, $db=NULL) {
	if (!(function_exists('mysqli_init') && ($link = mysqli_init())))
		return "Can't initialize MySQL connection. Please make sure your PHP has the mysqli extension.";
	if (!mysqli_options($link, MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 1'))
		return 'Setting MYSQLI_INIT_COMMAND failed';
	if (!mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 5))
		return 'Setting MYSQLI_OPT_CONNECT_TIMEOUT failed';
	if (function_exists('mysqli_fetch_all'))
		$host = 'p:'.$host;
	if (!mysqli_real_connect($link, $host, $uname, $passwd, $db, $port))
		return 'MySQL connect error #'.mysqli_connect_errno().': '.mysqli_connect_error();
	$link->set_charset("utf8");
	return $link;
}

interface MySQLQueryDataProcessor {
	public function handleRecord($data);
	public function handleResultStarted(&$fields);
	public function handleResultEnded(&$result);
	public function handleQueryEnded(&$response);
}

class TempFile {
	function __construct($name=NULL, $fromEnc='UTF-8', $toEnc='UTF-8', $path='./') {
		$this->fromEnc= $fromEnc;
		$this->toEnc= $toEnc ? $toEnc : $fromEnc;
		$this->name = $name ? str_replace('$', uniqid(), $name) : uniqid('tmp-');
		$this->file = fopen($path.'/'.$this->name, 'w+');
		if ($this->file===FALSE)
			throw new Exception("Can't open temporary file: ".$this->name);
	}

	public function write($s) {
		if ($this->fromEnc!==$this->toEnc)
			$s = mb_convert_encoding($s, $this->toEnc, $this->fromEnc);
		fwrite($this->file, $s);
	}
	
	public function close() {
		fclose($this->file);
	}
}

class QueryResultJSONWriter implements MySQLQueryDataProcessor {
	private $result = 0;
	private $record = 0;
	private $page = 0;

	function __construct($pageSize=0, $path='./') {
		$this->pageSize = $pageSize;
		$this->workPath = realpath($path);
		$this->tempFileName = 'tmp-'.uniqid();
	}

	private function openNextFile($end=FALSE) {
		if ($this->file) {
			$this->file->write("];");
			$this->file->close();
		}
		if ($end)
			return;
		$this->file = new TempFile($this->tempFileName.'-'.$this->result.'-'.$this->page, 'UTF-8', 'UTF-8', $this->workPath);
		$this->file->write("[\n");
	}

	public function handleResultStarted(&$fields) {
		$this->page = 0;
		$this->record = 0;
		$this->openNextFile();
	}

	public function handleResultEnded(&$result) {
		$result['pageCount'] = $this->page+1;
		$this->result++;
		$this->openNextFile(TRUE);
	}

	public function handleQueryEnded(&$response) {
		$response['outFile']  = $this->tempFileName;
		$response['pageSize'] = $this->pageSize;
	}

	public function handleRecord($data) {
		if ($this->pageSize) {
			if ((int)($this->record / $this->pageSize) > $this->page) {
				$this->page++;
				$this->openNextFile();
			}
			$this->record++;
		}
		$this->file->write(json_encode($data).",\n");
	}
}

class MySQLQuery {
	function __construct(&$link, $dataHandler=NULL, $maxCellSizeB=0, $maxDataSizeMB=0, $maxRows=0, $path='./') {
		$this->link        = $link;
		$this->dataHandler = $dataHandler;
		$this->maxCellSize = $maxCellSizeB;
		$this->maxDataSize = $maxDataSizeMB * 1048576;
		$this->maxRows     = $maxRows;
		$this->path        = realpath($path);
	}

	public function query($sql, $params=null, $flags=0, $wantFields=null, $offset=0, $limit=0) {
		$link = $this->link;
		$handler = $this->dataHandler;
		$wantFields = array_flip($wantFields);
		
		if ($params) {
			$offs = 0;
			foreach ($params as $p) {
				$i = strpos($sql, '?', $offs);
				if ($i===FALSE)
					return 'Too many parameters supplied for the query';
				$d = & $p['data'];
				if (isset($p['file'])) {
					$fn = $p['file'];
					if (!preg_match('/^[0-9a-z\.\-]+$/i', $fn))
						return 'Illegal file name';
					$d = file_get_contents($this->path.'/'.$p['file']);
				}
				if ($p['type']=='b')
					$d = 'X\''.bin2hex($d).'\'';
				else if ($p['type']=='s')
					$d = '\''.mysqli_real_escape_string($link, $p['data']).'\'';
				else
					$d = $d.'';
				$sql = substr_replace($sql, $d, $i, 1);
				$offs = $i + strlen($d);
			}
		}
		$sqlLen = strlen($sql);
		if ($sqlLen>0x80000) {
			$t = MySQLQuery::SimpleQuery($link, 'SHOW VARIABLES LIKE "max_allowed_packet"', TRUE);
			if (!$t)
				throw new Exception("Query is very large ({$sqlLen} bytes). Failed to read the value of 'max_allowed_packed' MySQL variable");
			if ($sqlLen>=$t[0][1])
				throw new Exception("Length of the query ({$sqlLen} bytes) is larger than 'max_allowed_packet' variable ({$t[0][1]} bytes) in MySQL configuration file.");
		}

		$t = microtime(TRUE);
		if (!$link->multi_query($sql))
			throw new Exception($link->error);
		
		$extime  = microtime(TRUE) - $t;
		$affRows = $link->affected_rows;
		$results = array();
		$noData  = ($flags & QUERY_ONLY_FIELDS) ? TRUE : FALSE;
		$hashing = ($flags & QUERY_WITH_HASHING) ? TRUE : FALSE;
		$totalRows = $totalSize = 0;
		$warning = '';
		do {
			if (!($result = $link->use_result()))
				continue;
			$res  = array();
			$flds = array();
			$resRowCnt = 0;
			foreach ($result->fetch_fields() as $f)
				$flds[] = MySQLQuery::GetFieldParams($f);
			if ($wantFields)
				$flds = array_intersect_key($flds, $wantFields);
			if ($handler && ($q = call_user_func_array(array($handler, 'handleResultStarted'), array(&$flds))))
				$warning = $q;
			while (!$warning && !$noData) {
				while ($row = $result->fetch_row()) {
					if ($offset > 0) {
						$offset--;
						continue;
					}
					if ($this->maxRows>0 && ++$totalRows>$this->maxRows) {
						$warning = "Query aborted: {$this->maxRows} record limit has been reached.";
						break;
					} else {
						if ($wantFields)
							$row = array_intersect_key($row, $wantFields);
						foreach ($row as $i=>&$c) {
							if ($c===NULL || $c==='')
								continue;
							$totalSize+= strlen($c);
							if ($hashing) {
								if ($flds[$i]['binary'])
									$c = array('label'=>'Binary', 'hash'=>md5($c));
								else if ($this->maxCellSize>0 && strlen($c)>$this->maxCellSize)
									$c = array('label'=>'Large', 'hash'=>md5($c));
							}
						}
						if ($this->maxDataSize>0 && $totalSize>$this->maxDataSize) {
							$warning = "Query aborted: {$this->maxDataSize} megabyte limit has been reached.";
							break;
						} else {
							if ($handler) {
								if ($q = call_user_func_array(array($handler, 'handleRecord'), array(&$row))) {
									$warning = $q;
									break;
								}
							} else
								$res[] = $row;
						}
						if (++$resRowCnt==$limit)
							break;
					}
				}
				break;
			}
			$result->free();
			$a = array('data'=>($handler ? NULL : $res), 'fields'=>$flds, 'rowCount'=>$resRowCnt);
			if ($flags & QUERY_CALC_FOUND_ROWS) {
				$t = MySQLQuery::SimpleQuery($link, 'SELECT FOUND_ROWS()', TRUE);
				$a['calcFoundRows'] = (int)$t[0][0];
			}
			if ($handler)
				call_user_func_array(array($handler, 'handleResultEnded'), array(&$a));
			$results[] = $a;
		} while ($link->more_results() && $link->next_result());
		
		if ($flags & QUERY_WITH_FIELD_DETAILS) {
			$cols = array();
			foreach ($results as &$r) {
				foreach ($r['fields'] as &$f) {
					if (!($t = $f['table']))
						continue;
					$c = & $cols[$t];
					if (!isset($c)) {
						$c = MySQLQuery::SimpleQuery($link, 'SHOW COLUMNS FROM '.($f['db'] ? '`'.$f['db'].'`.' : '')."`{$t}`");
						if (!$c) {
							$f['table'] = '';
							continue;
						}
					}
					foreach ($c as &$q) {
						if ($q['Field']!=$f['field'])
							continue;
						if ($f['type']=='set' || $f['type']=='enum') {
							$f['values'] = str_replace("','", ",", preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $q['Type']));
						} else {
							$f['values'] = '';
						}
						$f['default'] = $q['Default'];
						break;
					}
				}
			}
		}
		$a = array(
			'results'=>$results,
			'affectedRows'=>($affRows < 0 ? 0 : $affRows),
			'execTime'=>sprintf("%.3f",$extime)
		);
		if ($handler)
			call_user_func_array(array($handler, 'handleQueryEnded'), array(&$a));
		if (is_string($warning))
			$a['warning'] = $warning;
		if ($flags & QUERY_WITH_INSERT_ID) {
			$t = MySQLQuery::SimpleQuery($link, 'SELECT LAST_INSERT_ID()', TRUE);
			$a['lastInsertId'] = $t[0][0];
		}
		return $a;
	}

	public static function SimpleQuery(&$link, $sql, $fetch_array=FALSE) {
		if (!($result = $link->query($sql, MYSQLI_STORE_RESULT)))
			return FALSE;
		$res = array();
		if (is_object($result)) {
			if ($fetch_array) {
				while ($row = $result->fetch_row())
					$res[] = $row;
			} else {
				while ($row = $result->fetch_assoc())
					$res[] = $row;
			}
			$result->free();
			while ($link->more_results() && $link->next_result()) {
				$res = $link->use_result();
				if ($res)
					$res->free();
			}
		}
		return $res;
	}

	public static function NoResultQuery(&$link, $sql) {
		if (!$link->multi_query($sql))
			return FALSE;
		do {
			if ($r = $link->store_result())
				$r->free();
		} while ($link->next_result());
		return TRUE;
	}
	
	public static function GetFieldParams(&$field) {
		global $FIELD_TYPES;
		$type = '';
		//*** bug: http://bugs.mysql.com/bug.php?id=11974 ***//
		foreach (range(249, 252) as $j) {
			if ($field->type==$j && $field->charsetnr!=63) {
				//for all blob types not-binary charset should be normal TEXT
				$type = 'text';
				break;
			}
		}
		//***************************************************//
		if (!$type && isset($FIELD_TYPES[$field->type])) {
			$type = $FIELD_TYPES[$field->type];
			//*** bug: http://bugs.mysql.com/bug.php?id=31134 ***//
			if ($field->flags & FLAG_ENUM) $type = 'enum';
			else if ($field->flags & FLAG_SET) $type = 'set';
			//*****************************************************
		} else {
			$type = 'text';
		}
		return array(
			'type'       => $type,
			'binary'     => ($field->flags & FLAG_BINARY) && in_array($type, array('text','blob','enum','set')),
			'db'         => $field->db,
			'tableAlias' => $field->table,
			'table'      => $field->orgtable,
			'fieldAlias' => $field->name,
			'field'      => $field->orgname,
			'size'       => $field->length,
			'maxSize'    => $field->max_length,
			'flags'      => $field->flags,
			'numeric'    => in_array($type, array('number','year','bit'))
		);
	}
}
?>