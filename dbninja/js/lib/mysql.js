/*
 *	MySQL Mode for CodeMirror 2 by MySQL-Tools
 *	@author James Thorne (partydroid)
 *	@link 	http://github.com/partydroid/MySQL-Tools
 * 	@link 	http://mysqltools.org
 *	@version 02/Jan/2012
*/
CodeMirror.defineMode("mysql", function(config) {
  var indentUnit = config.indentUnit;
  var curPunc;

  function wordRegexp(words) {
    return new RegExp("^(?:" + words.join("|") + ")$", "i");
  }
  var ops = wordRegexp(["str", "lang", "langmatches", "datatype", "bound", "sameterm", "isiri", "isuri",
                        "isblank", "isliteral", "a"]);	//"union"
  var keywords = wordRegexp([
	  'ABS','ACCESSIBLE','ACOS','ADD','ADDDATE','ADDTIME','AES_DECRYPT','AES_ENCRYPT','ALL','ALTER','ANALYSE','ANALYZE','AND','AS','ASC','ASCII','ASENSITIVE','ASIN',
	  'ATAN','ATAN2','AVG','BEFORE','BENCHMARK','BETWEEN','BIGINT','BIN','BINARY','BIT_AND','BIT_COUNT','BIT_LENGTH','BIT_OR','BIT_XOR','BLOB','BOTH','BY','CALL',
	  'CASCADE','CASE','CAST','CEIL','CEILING','CHANGE','CHAR','CHARACTER','CHARACTER_LENGTH','CHARSET','CHAR_LENGTH','CHECK','COALESCE','COERCIBILITY','COLLATE',
	  'COLLATION','COLUMN','COMPRESS','CONCAT','CONCAT_WS','CONDITION','CONNECTION_ID','CONSTRAINT','CONTINUE','CONV','CONVERT','CONVERT_TZ','COS','COT','COUNT',
	  'CRC32','CREATE','CROSS','CURDATE','CURRENT_DATE','CURRENT_TIME','CURRENT_TIMESTAMP','CURRENT_USER','CURSOR','CURTIME','DATABASE','DATABASES','DATE','DATEDIFF',
	  'DATE_ADD','DATE_FORMAT','DATE_SUB','DAY','DAYNAME','DAYOFMONTH','DAYOFWEEK','DAYOFYEAR','DAY_HOUR','DAY_MICROSECOND','DAY_MINUTE','DAY_SECOND','DEC','DECIMAL',
	  'DECLARE','DECODE','DEFAULT','DEGREES','DELAYED','DELETE','DESC','DESCRIBE','DES_DECRYPT','DES_ENCRYPT','DETERMINISTIC','DISTINCT','DISTINCTROW','DIV','DOUBLE',
	  'DROP','DUAL','EACH','ELSE','ELSEIF','ELT','ENCLOSED','ENCODE','ENCRYPT','ESCAPED','EXISTS','EXIT','EXP','EXPLAIN','EXPORT_SET','EXTRACT','ExtractValue','FALSE',
	  'FETCH','FIELD','FIND_IN_SET','FLOAT','FLOAT4','FLOAT8','FLOOR','FOR','FORCE','FOREIGN','FORMAT','FOUND_ROWS','FROM','FROM_BASE64','FROM_DAYS','FROM_UNIXTIME',
	  'FULLTEXT','GET','GET_FORMAT','GET_LOCK','GRANT','GREATEST','GROUP','GROUP_CONCAT','GTID_SUBSET','GTID_SUBTRACT','HAVING','HEX','HIGH_PRIORITY','HOUR',
	  'HOUR_MICROSECOND','HOUR_MINUTE','HOUR_SECOND','IF','IFNULL','IGNORE','IN','INDEX','INET6_ATON','INET6_NTOA','INET_ATON','INET_NTOA','INFILE','INNER','INOUT',
	  'INSENSITIVE','INSERT','INSTR','INT','INT1','INT2','INT3','INT4','INT8','INTEGER','INTERVAL','INTO','IO_AFTER_GTIDS','IO_BEFORE_GTIDS','IS','ISNULL','IS_FREE_LOCK',
	  'IS_IPV4','IS_IPV4_COMPAT','IS_IPV4_MAPPED','IS_IPV6','IS_USED_LOCK','ITERATE','JOIN','KEY','KEYS','KILL','LAST_DAY','LAST_INSERT_ID','LCASE','LEADING','LEAST',
	  'LEAVE','LEFT','LENGTH','LIKE','LIMIT','LINEAR','LINES','LN','LOAD','LOAD_FILE','LOCALTIME','LOCALTIMESTAMP','LOCATE','LOCK','LOG','LOG10','LOG2','LONG','LONGBLOB',
	  'LONGTEXT','LOOP','LOWER','LOW_PRIORITY','LPAD','LTRIM','MAKEDATE','MAKETIME	MAKETIME()','MAKE_SET','MASTER_BIND','MASTER_POS_WAIT','MASTER_SSL_VERIFY_SERVER_CERT',
	  'MATCH','MAX','MAXVALUE','MD5','MEDIUMBLOB','MEDIUMINT','MEDIUMTEXT','MICROSECOND','MID','MIDDLEINT','MIN','MINUTE','MINUTE_MICROSECOND','MINUTE_SECOND','MOD',
	  'MODIFIES','MONTH','MONTHNAME','NAME_CONST','NATURAL','NOT','NOW','NO_WRITE_TO_BINLOG','NULL','NULLIF','NUMERIC','OCT','OCTET_LENGTH','OLD_PASSWORD','ON','ONE_SHOT',
	  'OPTIMIZE','OPTION','OPTIONALLY','OR','ORD','ORDER','OUT','OUTER','OUTFILE','PARTITION','PASSWORD','PERIOD_ADD','PERIOD_DIFF','PI','POSITION','POW','POWER','PRECISION',
	  'PRIMARY','PROCEDURE','PURGE','QUARTER','QUOTE','RADIANS','RAND','RANGE','READ','READS','READ_WRITE','REAL','REFERENCES','REGEXP','RELEASE','RELEASE_LOCK','RENAME',
	  'REPEAT','REPLACE','REQUIRE','RESIGNAL','RESTRICT','RETURN','REVERSE','REVOKE','RIGHT','RLIKE','ROUND','ROW_COUNT','RPAD','RTRIM','SCHEMA','SCHEMAS','SECOND',
	  'SECOND_MICROSECOND','SEC_TO_TIME','SELECT','SENSITIVE','SEPARATOR','SESSION_USER','SET','SHA1','SHA2','SHOW','SIGN','SIGNAL','SIN','SLEEP','SMALLINT','SOUNDEX',
	  'SOUNDS','SPACE','SPATIAL','SPECIFIC','SQL','SQLEXCEPTION','SQLSTATE','SQLWARNING','SQL_AFTER_GTIDS','SQL_BEFORE_GTIDS','SQL_BIG_RESULT','SQL_CALC_FOUND_ROWS',
	  'SQL_SMALL_RESULT','SQL_THREAD_WAIT_AFTER_GTIDS','SQRT','SSL','STARTING','STD','STDDEV','STDDEV_POP','STDDEV_SAMP','STRAIGHT_JOIN','STRCMP','STR_TO_DATE','SUBDATE',
	  'SUBSTR','SUBSTRING','SUBSTRING_INDEX','SUBTIME','SUM','SYSDATE','SYSTEM_USER','TABLE','TAN','TERMINATED','THEN','TIME','TIMEDIFF','TIMESTAMP','TIMESTAMPADD',
	  'TIMESTAMPDIFF','TIME_FORMAT','TIME_TO_SEC','TINYBLOB','TINYINT','TINYTEXT','TO','TO_BASE64','TO_DAYS','TO_SECONDS','TRAILING','TRIGGER','TRIM','TRUE','TRUNCATE',
	  'UCASE','UNCOMPRESS','UNCOMPRESSED_LENGTH','UNDO','UNHEX','UNION','UNIQUE','UNIX_TIMESTAMP','UNLOCK','UNSIGNED','UPDATE','UPPER','USAGE','USE','USER','USING',
	  'UTC_DATE','UTC_TIME','UTC_TIMESTAMP','UUID','UUID_SHORT','UpdateXML','VALIDATE_PASSWORD_STRENGTH','VALUES','VARBINARY','VARCHAR','VARCHARACTER','VARIANCE','VARYING',
	  'VAR_POP','VAR_SAMP','VERSION','WAIT_UNTIL_SQL_THREAD_AFTER_GTIDS','WEEK','WEEKDAY','WEEKOFYEAR','WEIGHT_STRING','WHEN','WHERE','WHILE','WITH','WRITE','XOR','YEAR',
	  'YEARWEEK','YEAR_MONTH','ZEROFILL',
	  'BEGIN','END','DATA','COMMENT','TEMPORARY','NEW','OLD','ENGINE','AUTO_INCREMENT','DEFINER','DUPLICATE','ROW','AFTER','BEFORE','VIEW','ALGORITHM','UNDEFINED','SECURITY',
	  'STATUS','FUNCTION','EVENTS','TRIGGERS','CLOSE'
  ]);
  var operatorChars = /[*+\-<>=&|]/;

  function tokenBase(stream, state) {
    var ch = stream.next();
    curPunc = null;
    if (ch == "$" || ch == "?") {
      stream.match(/^[\w\d]*/);
      return "variable-2";
    }
    else if (ch == "<" && !stream.match(/^[\s\u00a0=]/, false)) {
      stream.match(/^[^\s\u00a0>]*>?/);
      return "atom";
    }
    else if (ch == "\"" || ch == "'") {
      state.tokenize = tokenLiteral(ch);
      return state.tokenize(stream, state);
    }
    else if (ch == "`") {
      state.tokenize = tokenOpLiteral(ch);
      return state.tokenize(stream, state);
    }
    else if (/[{}\(\),\.;\[\]]/.test(ch)) {
      curPunc = ch;
      return null;
    }
    else if (ch == "-") {
		if(stream.next()=="-")
		{
			if (/[\n\r\t ]/.test(stream.next())) {
				stream.skipToEnd();
				return "comment";
			}
		}
    }
	else if (ch == '#') {
		stream.skipToEnd();
		return "comment";
	}
    else if (ch == "/" && stream.eat("*")) {
      state.tokenize = tokenComment;
      return state.tokenize(stream, state);
    }
    else if (operatorChars.test(ch)) {
      stream.eatWhile(operatorChars);
      return null;
    }
    else if (ch == ":") {
      stream.eatWhile(/[\w\d\._\-]/);
      return "atom";
    }
    else {
      stream.eatWhile(/[_\w\d]/);
      if (stream.eat(":")) {
        stream.eatWhile(/[\w\d_\-]/);
        return "atom";
      }
      var word = stream.current(), type;
      if (ops.test(word))
        return null;
      else if (keywords.test(word))
        return "keyword";
      else
        return "variable";
    }
  }

  function tokenLiteral(quote) {
    return function(stream, state) {
      var escaped = false, ch;
      while ((ch = stream.next()) != null) {
        if (ch == quote && !escaped) {
          state.tokenize = tokenBase;
          break;
        }
        escaped = !escaped && ch == "\\";
      }
      return "string";
    };
  }

  function tokenOpLiteral(quote) {
    return function(stream, state) {
      var escaped = false, ch;
      while ((ch = stream.next()) != null) {
        if (ch == quote && !escaped) {
          state.tokenize = tokenBase;
          break;
        }
        escaped = !escaped && ch == "\\";
      }
      return "variable-2";
    };
  }

  function tokenComment(stream, state) {
    for (;;) {
      if (stream.skipTo("*")) {
        stream.next();
        if (stream.eat("/")) {
          state.tokenize = tokenBase;
          break;
        }
      } else {
        stream.skipToEnd();
        break;
      }
    }
    return "comment";
  }


  function pushContext(state, type, col) {
    state.context = {prev: state.context, indent: state.indent, col: col, type: type};
  }
  function popContext(state) {
    state.indent = state.context.indent;
    state.context = state.context.prev;
  }

  return {
    startState: function(base) {
      return {tokenize: tokenBase,
              context: null,
              indent: 0,
              col: 0};
    },

    token: function(stream, state) {
      if (stream.sol()) {
        if (state.context && state.context.align == null) state.context.align = false;
        state.indent = stream.indentation();
      }
      if (stream.eatSpace()) return null;
      var style = state.tokenize(stream, state);

      if (style != "comment" && state.context && state.context.align == null && state.context.type != "pattern") {
        state.context.align = true;
      }

      if (curPunc == "(") pushContext(state, ")", stream.column());
      else if (curPunc == "[") pushContext(state, "]", stream.column());
      else if (curPunc == "{") pushContext(state, "}", stream.column());
      else if (/[\]\}\)]/.test(curPunc)) {
        while (state.context && state.context.type == "pattern") popContext(state);
        if (state.context && curPunc == state.context.type) popContext(state);
      }
      else if (curPunc == "." && state.context && state.context.type == "pattern") popContext(state);
      else if (/atom|string|variable/.test(style) && state.context) {
        if (/[\}\]]/.test(state.context.type))
          pushContext(state, "pattern", stream.column());
        else if (state.context.type == "pattern" && !state.context.align) {
          state.context.align = true;
          state.context.col = stream.column();
        }
      }

      return style;
    },

    indent: function(state, textAfter) {
      var firstChar = textAfter && textAfter.charAt(0);
      var context = state.context;
      if (/[\]\}]/.test(firstChar))
        while (context && context.type == "pattern") context = context.prev;

      var closing = context && firstChar == context.type;
      if (!context)
        return 0;
      else if (context.type == "pattern")
        return context.col;
      else if (context.align)
        return context.col + (closing ? 0 : 1);
      else
        return context.indent + (closing ? 0 : indentUnit);
    }
  };
});

CodeMirror.defineMIME("text/x-mysql", "mysql");
