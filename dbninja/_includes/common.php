<?php
/*--------------------------------------------------------------------*
 | Copyright (c) 2010-2013 Vayer Software Ltd. - All Rights Reserved. |
 *--------------------------------------------------------------------*/

function randString($len) {
	$a = "abcdefghijklmnopqrstuvwxyz0123456789";	
	$l = strlen($a) - 1;
	$s = "";
	for (;$len; $len--)
		$s.= $a[rand(0, $l)];
	return $s;
}

class FileLineReader {
	public  $lineCount = 0;
	private $tail = "";
	private $offset = 0;
	private $done = false;

	function __construct($handle) {
		$this->handle = $handle;
	}

	function read() {
		if ($this->done)
			return false;
		while(true) {
			$s = fread($this->handle, 4096);
			if ($this->tail)
				$s = $this->tail.$s;
			if (preg_match("/\x0D\x0A|\x0A\x0D|\x0A|\x0D/", $s, $match, PREG_OFFSET_CAPTURE, $this->offset)) {
				$line = substr($s, 0, $match[0][1]);
				$this->tail = substr($s, $match[0][1] + strlen($match[0][0]));
				$this->offset = 0;
				$this->lineCount++;
				return $line;
			} else {
				if (feof($this->handle)) {
					$this->done = true;
					$this->tail = null;
					$this->lineCount++;
					return $s;
				}
				$this->tail = $s;
				$this->offset = strlen($s);
			}
		}
	}

	function close() {
		$this->tail = null;
		fclose($this->handle);
	}
}

function hexdump($data, $html=True, $limit=0) {
    $s = $h = $a = '';
	$i = $j = $t = 0;
    $l = $limit ? min($limit, strlen($data)) : strlen($data);
    for (; $i<$l; $i++) {
		$p = $data[$i];
        $h.= bin2hex($p).' ';
        $a.= (ord($p) >= 32 ? $p : '.');
        if ($j==7) {
            $h.= ' ';
            $a.= ' ';
        }
        if (++$j==16 || $i==$l-1) {
            $s.= sprintf('%05x  %-49s  %s', $t, $h, $a);
            $h = $a = '';
            $t+= 16;
            $j = 0;
            if ($i!=$l-1)
                $s.= "\n";
        }
    }
	if ($html)
		return '<pre>'.str_replace(array('&','<','>'), array('&quot;','&lt;','&gt;'), $s).'</pre>';
    return $s."\n";
}

function formatBytes($size, $precision=2) {
	$base = log($size) / log(1024);
	$suff = array('', 'k', 'M', 'G', 'T');
	return round(pow(1024, $base - floor($base)), $precision) . $suff[floor($base)];
}

?>