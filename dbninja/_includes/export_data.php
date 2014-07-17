<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

require_once(__INC__.'/mysql.php');
require_once(__INC__.'/export_stuff.php');

if ($ARGS['db'] && !$LINK->select_db($ARGS['db']))
	return $LINK->error;

/////

class QueryResultExportCSV implements MySQLQueryDataProcessor {
	function __construct($link, $args) {
		$this->file		 = new TempFile(NULL, 'UTF-8', $args['file_encoding']);
		$this->link      = $link;
		$this->separator = stripcslashes($args['separator']);
		$this->encloser  = stripcslashes($args['encloser']);
		$this->null_rep  = stripcslashes($args['null_rep']);
		$this->term      = stripcslashes($args['term']);
		$this->fixed     = $args['fixed'];
		$this->spacing   = $args['spacing'];
		$this->args      = $args;
	}
	
	public function handleRecord($data) {
		$e  = $this->encloser;
		$ee = $e.$e;
		$a  = array();
		foreach ($data as $i => $c) {
			if ($c===NULL) {
				$c = $this->null_rep;
			} else {
				$c = mysqli_real_escape_string($this->link, $c);
				if ($e)
					$c = $e.str_replace($e, $ee, $c).$e;
			}
			if ($this->fixed && isset($this->widths[$i])) {
				$l = strlen($c);
				if ($this->widths[$i] < $l)
					$this->widths[$i] = $l;
			}
			$a[] = $c;
		}
		$this->file->write(implode(($this->fixed ? "\t" : $this->separator), $a).$this->term);
	}

	public function handleResultStarted(&$fields) {
		$this->widths = array_fill(0, count($fields)-1, 0);
		if ($this->args['add_field_names']) {
			$a = array();
			foreach ($fields as $i => $f) {
				$a[$i] = $this->encloser.$f['fieldAlias'].$this->encloser;
				if ($this->fixed && isset($this->widths[$i]))
					$this->widths[$i] = strlen($a[$i]);
			}
			if ($this->fixed)
				$this->fieldNames = $a;
			else
				$this->file->write(implode($this->separator, $a).$this->term);
		}
	}

	public function handleResultEnded(&$result) {
		if (!$this->fixed)
			return;
		$f = new TempFile();
		if ($this->args['add_field_names']) {
			$a = array();
			foreach ($this->fieldNames as $i => $t)
				if (isset($this->widths[$i]))
					$a[] = str_pad($t, $this->widths[$i] + $this->spacing);
				else
					$a[] = $t;
			$f->write(implode($this->separator, $a).$this->term);
		}
		fseek($this->file->file, 0);
		while (($a = fgets($this->file->file))!==FALSE) {
			$a = explode("\t", $a);
			foreach ($a as $i => &$c) {
				if (isset($this->widths[$i]))
					$c = str_pad($c, $this->widths[$i] + $this->spacing);
			}
			$f->write(implode($this->separator, $a));
		}
		$this->file->close();
		unlink($this->file->name);
		$this->file = $f;
	}

	public function handleQueryEnded(&$response) {
		$response = array('result'=>$this->file->name);
	}
}


class QueryResultExportSQL implements MySQLQueryDataProcessor {
	function __construct($link, $args) {
		$this->file		 = new TempFile(NULL, 'UTF-8', $args['file_encoding']);
		$this->link      = $link;
		$this->encoding  = $args['file_encoding'];
		$this->group     = $args['group_inserts'];
		$this->args      = $args;
		$this->groupSize = 0;
	}

	public function handleRecord($data) {
		$a = array();
		foreach ($data as $i => $c) {
			if ($c===NULL)
				$a[] = 'NULL';
			else if ($this->fields[$i]['binary'])
				$a[] = "X'".bin2hex($c)."'";
			else if ($this->fields[$i]['numeric'])
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
	}

	public function handleResultStarted(&$fields) {
		$this->fields = $fields;
		$this->target = $this->args['target'];

		$this->file->write(getExportHeader($this->args['no_foreign_keys']));
		if ($this->args['with_lock'])
			$this->file->write("LOCK TABLES `{$this->target}` WRITE;\n");
		$this->file->write("ALTER TABLE `{$this->target}` DISABLE KEYS;\n\n");

		$a = array();
		foreach ($fields as $f)
			$a[] = '`'.$f['fieldAlias'].'`';
		$this->insert_stmt = "INSERT INTO `{$this->target}` (".implode(',', $a).") VALUES ";
	}

	public function handleResultEnded(&$result) {
		if ($this->group)
			$this->file->write(";\n");
		$this->file->write("\nALTER TABLE `{$this->target}` ENABLE KEYS;\n");
		if ($this->args['with_lock'])
			$this->file->write("UNLOCK TABLES;\n");
		if ($this->args['with_commit'])
			$this->file->write("COMMIT;\n");
		$this->file->write(getExportFooter($this->args['no_foreign_keys']));
	}

	public function handleQueryEnded(&$response) {
		$response = array('result'=>$this->file->name);
	}
}


class QueryResultExportArrArr implements MySQLQueryDataProcessor {
	private $recsWritten = 0;

	function __construct($link, $args) {
		$this->file		 = new TempFile(NULL, 'UTF-8', $args['file_encoding']);
		$this->link      = $link;
		$this->encoding  = $args['file_encoding'];
		$this->args      = $args;

		if ($args['format']=='json') {
			$this->encl  = array('[',']');
			$this->brack = $args['type']==0 ? array('[', ']') : array('{', '}');
			$this->join  = ':';
		} else {
			$this->encl  = $this->brack = array('array(', ')');
			$this->join  = '=>';
		}
	}

	public function handleRecord($data) {
		$a = array();
		$t = $this->args['type'];
		foreach ($data as $i => $c) {
			if ($c===NULL)
				$c = 'null';
			else if ($this->fields[$i]['binary']) {
				$s = '"';
				$l = strlen($c);
				for ($i=2; $i<$l; $i+=2)
					$s.= '\x'.substr($c, $i, 2);
				$c = '"'.$s.'"';
			} else if (!$this->fields[$i]['numeric'])
				$c = '"'.addcslashes($c, "\0\r\n\t\\\"").'"';
			if ($t==1)
				$c = '"'.$this->fields[$i]['fieldAlias'].'"'.$this->join.$c;
			$a[] = $c;
		}
		$this->file->write(($this->recsWritten++ ? ',' : '')."\n".$this->brack[0].implode(',', $a).$this->brack[1]);
	}

	public function handleResultStarted(&$fields) {
		$this->fields = $fields;
		$this->file->write($this->encl[0]);
	}

	public function handleResultEnded(&$result) {
		$this->file->write("\n".$this->encl[1].";\n");
	}

	public function handleQueryEnded(&$response) {
		$response = array('result'=>$this->file->name);
	}
}

/////

switch (strtolower($ARGS['format'])) {
	case 'csv':
	case 'txt':
		$handler = new QueryResultExportCSV($LINK, $ARGS);
		break;
	case 'sql':
		$handler = new QueryResultExportSQL($LINK, $ARGS);
		break;
	case 'json':
	case 'php':
		$handler = new QueryResultExportArrArr($LINK, $ARGS);
		break;
}

$q = new MySQLQuery($LINK, $handler);
return $q->query($ARGS['query'], NULL, 0, $ARGS['fields'], $ARGS['offset'], $ARGS['limit']);
?>
