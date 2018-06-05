<?php
/*
example:
$sql = new MySQL();
$sql->connect('root', '', 'localhost');
$sql->selectdb('cutenews');
*/

class MySQL {
	function connect($username = 'root', $password = '', $server = 'localhost'){
		$this->username = $username;
		$this->password = $password;
		$this->server	= $server;
		$this->link		= mysql_connect($this->server, $this->username, $this->password);
	}

	function disconnect(){
		mysql_close($this->link);
	}

	function strict($error = false){
	}

	function selectdb($database, $prefix = ''){
		$this->database = $database;
		$this->prefix	= $prefix;
		mysql_select_db($this->database, $this->link);
	}

	function altertable($arg){
		$query .= 'alter table '.$this->prefix.$arg['table']."\n";

		if ($arg['action'] == 'insert'){
			$query .= 'add '.$arg['name'].' '.$this->_values($arg);
		} elseif ($arg['action'] == 'rename table'){
			$query .= 'rename '.$arg['name'];
		} elseif ($arg['action'] == 'rename col'){
			$result = array();
			$list	= mysql_query('select * from '.$this->prefix.$arg['table'], $this->link);
			for ($i = 0; $i < mysql_num_fields($list); $i++){
				if (mysql_field_name($list, $i) == $arg['name']){
					$result[] = mysql_field_type($list, $i).' '.mysql_field_flags($list, $i);
				}
			}

			$query .= 'change '.$arg['name'].' '.$arg['values']['name'].' '.join('', $result)."\n";
		} elseif ($arg['action'] == 'modify'){
			$result = array();
			$list	= mysql_query('select * from '.$this->prefix.$arg['table'], $this->link);
			for ($i = 0; $i < mysql_num_fields($list); $i++){
				if (mysql_field_name($list, $i) == $arg['name']){
					$result[] = mysql_field_type($list, $i).' '.mysql_field_flags($list, $i);
				}
			}

			$query .= 'change '.$arg['name'].' '.$arg['name'].' '.join('', $result).' '.$this->_values($arg).' not null'."\n";
		} elseif ($arg['action'] == 'addkey'){
			$query .= 'add primary key('.$arg['name'].')'."\n";
		} elseif ($arg[ 'action'] == 'drop'){
			$query = 'drop table '.$this->prefix.$arg['table']."\n";
		}

		$query = str_replace('string', 'varchar(255)', $query);
		$query = str_replace('bool', 'tinyint(1)', $query);
		$query = str_replace('primary key', 'primary', $query);
		$query = str_replace('primary', 'primary key', $query);
		return mysql_query($query, $this->link);
	}

	function showdbs($arg){
		$list = mysql_list_dbs($this->link);
		for ($i = 0; $i < mysql_num_rows($list); $i++){
			$result[] = mysql_db_name($list, $i);
		}

		return $result;
	}

	function createdb($arg){
		mysql_create_db($arg['db'], $this->link);
	}

	function dropdb($arg){
		mysql_drop_db($arg['db'], $this->link);
	}

	function createtable($arg){
		foreach ($arg['columns'] as $column => $arg['values']){
			$query[] = $column.' '.$this->_values($arg);

			foreach($arg['values'] as $k => $v){
				if ($k == 'primary'){
				$primary = ','."\n".'primary key ('.$column.')';
				}
			}
		}

		$query = join(', ', $query).$primary;
		$query = str_replace('string', 'varchar(255)', $query);
		$query = str_replace('bool', 'tinyint(1)', $query);
		$query = 'create table '.$this->prefix.$arg['table']." (\n".$query."\n)\n";
		return mysql_query($query, $this->link);
	}

	function table_exists($table, $database = ''){
		$list = mysql_list_tables($database, $this->link);
		for ($i = 0; $i < mysql_num_rows($list); $i++){
			if (mysql_tablename($list, $i) == $this->prefix.$table){
				return true;
			}
		}

		return false;
	}

	function query_count(){
		return $this->query;
	}

	function table_count($table, $database = ''){
		$query = 'show table status like \''.$this->prefix.$table.'\'';
		$query = mysql_query($query, $this->link);
		$row   = mysql_fetch_array($query);
		return $row['Rows'];
	}

	function last_insert_id($table = '', $database = '', $select = ''){
		$query = 'show table status like \''.$this->prefix.$table.'\'';
		$query = mysql_query($query, $this->link);
		$row   = mysql_fetch_array($query);

		return ($row['Auto_increment'] - 1);
	}

	function insert($arg){
		foreach ($arg['values'] as $k => $v){
			$insert[] = $k;
			$values[] = '\''.mysql_escape_string($v).'\'';
		}

		$query .= 'insert into '.$this->prefix.$arg['table']."\n";
		$query .= '('.join(', ', $insert).')'."\n";
		$query .= ' values ('.join(', ', $values).')'."\n";
		$debug_return = false;
		if (mysql_query($query, $this->link)) {
			$debug_return = mysql_affected_rows($this->link) != 0;
		}
		return $debug_return;
	}

	function delete($arg){
		$query .= 'delete from '.$this->prefix.$arg['table']."\n";
		$query .= $this->_where($arg)."\n";
		$query .= ($arg['limit'] ? 'limit '.join(', ', $arg['limit']) : '')."\n";
		$debug_return = false;
		if (mysql_query($query, $this->link)) {
			$debug_return = mysql_affected_rows($this->link) != 0;
		}
		return $debug_return;
	}

	function update($arg){
		$query .= 'update '.$this->prefix.$arg['table']."\n";
		$query .= 'set '.$this->_values($arg, ' = ', ', ')."\n";
		$query .= $this->_where($arg)."\n";
		$query .= ($arg['limit'] ? 'limit '.join(', ', $arg['limit']) : '')."\n";
		$debug_return = false;
		if (mysql_query($query, $this->link)) {
			$debug_return = mysql_affected_rows($this->link) != 0;
		}
		return $debug_return;
	}

	function select($arg){
		$this->query++;
		$query .= 'select '.(!$arg['select'] ? '*' : join(', ', $arg['select']))."\n";
		$query .= 'from '.$this->prefix.$arg['table']."\n";
		$query .= ($arg['join'] ? 'inner join '.$this->prefix.$arg['join']['table'].' on '.$arg['join']['where'] : '')."\n";
		$query .= $this->_where($arg)."\n";
		$query .= ($arg['orderby'] ? 'order by '.join(' ', $arg['orderby']) : '')."\n";
		$query .= ($arg['limit'] ? 'limit '.join(', ', $arg['limit']) : '')."\n";
		$query	= mysql_query($query, $this->link);
		$result = array();

		while ($row = @mysql_fetch_assoc($query)){
			$result[] = $row;
		}

		return $result;
	}

	function _values($arg, $separator_in = ' ', $separator_out = ' '){
		foreach ($arg['values'] as $k => $v){
			if ($k != 'primary'){
				if ($k == 'type' or ($k == 'name' and $arg['action'])){
					$result[] = $v.' not null';
				} elseif ($k == 'auto_increment' or $k == 'permanent'){
					$result[] = $k;
				} else {
					$result[] = $k.$separator_in.'\''.mysql_escape_string($v).'\'';
				}
			}
		}

		return join($separator_out, $result);
	}

	function _where($arg, $separator = ' '){
		if ($arg['where']){
			$operators = '( |=|!=|<>|<|<=|>|>=|=~|!~|\?|\?!)';

			foreach ($arg['where'] as $k => $v){
				if (preg_match('/\[(.*)\]/i', $v, $match)){
					foreach (explode('|', $match[1]) as $or){
						$where[] = preg_replace('/(.*)'.$operators.'(.*)/i', '\\1\\2\'\\3\'', preg_replace('/\['.str_replace('|', '\|', $match[1]).'\]/i', $or, $v));
					}

					$result[] = '('.join(' or ', $where).')'."\r\n";
				} elseif ($v != 'and' and $v != 'or' and $v != 'xor'){
					$result[] = preg_replace('/(.*)'.$operators.'(.*)/i', '\\1\\2\'\\3\'', $v)."\r\n";
				} else {
					$result[] = $v."\r\n";
				}
			}

			return 'where '.str_replace(array('!~', '=~'), array('not like', 'like'), join($separator, $result));
		}
	}
}
?>