<?php
require_once dirname(__FILE__) . '/Database.php';
require_once dirname(__FILE__) . '/Object.php';
require_once dirname(__FILE__) . '/querybuilder/Join.php';

/**
 * A base class that will automatically create CRUD operations for a table. Note that QueryBuilder 
 * does extend Object, so the children of QueryBuilder can be retrieved statically via get.
 */
abstract class QueryBuilder extends Object {
	/**
	 * Gets data from the database. 
	 * @param array|string $data A list of columns or a column to show, where the desired column is 
	 *	the KEY. Note that if there are no valid columns in data	will return all columns.
	 * @param array|string $join A Join or an array of Join objects. None (array()) by default.
	 * @param array $where Key-value pairs of columns and values to filter by. None (array()) by default.
	 * @param string $orderBy Column name to order by. None ('') by default.
	 * @param int $start Specifies the first parameter in limit clause. Zero by default.
	 * @param int $length Specifies the second parameter in limit clause. No length (<0) by default.
	 * @return array An array containing the viewable columns as keys and their values for each row.
	 */
	public function retrieve($data, $join = array(), array $where = array(), 
			$orderBy = '', $start = 0, $length = -1) {
		if(is_string($data)) {
			$data = array($data);
		}
		if(is_object($join) && get_class($join) === "Join") {
			$join = array($join);
		}
		
		$table = $this->getTable();
		$columns = $this->getColumns();
		$data = array_flip($data);
		$goodData = array_intersect_key($data, $columns);
		$goodDataWithTables = array();
		foreach($goodData as $key => $value) {
			$goodDataWithTables[] = $table . '.' . $key;
		}
		$goodWhere = array_intersect_key($where, $columns);
				
		$options = '';
		if(count($join) > 0) {
			/* @var Join $element */
			foreach($join as $element) {
				if($element->getPreserve()) {
					$options .= ' LEFT JOIN ';
				} else {
					$options .= ' INNER JOIN ';
				}
				$joinColumn = $element->getColumn();
				$joinTable = $element->getTable();
				$joinTableColumns = $element->getTableColumns();
				$newData = array_intersect_key($data, $joinTableColumns);
				foreach($newData as $key => $value) {
					$goodDataWithTables[] = $joinTable . '.' . $key;
				}
				$goodData = array_merge($goodData, $newData);
				$goodWhere = array_merge($goodWhere, array_intersect_key($where, $joinTableColumns));
				$options .= $joinTable . ' ON ' . $joinTable . '.' . $joinColumn . ' = ' . $table . '.' .
					$joinColumn . ' '; 
			}
		}
		if(count($goodWhere) > 0) {
			$options .= ' WHERE 1';
			$i = 0;
			foreach($goodWhere as $key => $value) {
				$options .= ' AND ' . $key . ' = :' . $i;
				$i++;
			}
		}
		if($orderBy !== '' && isset($columns[$orderBy])) {
			$options .= ' ORDER BY ' . $orderBy . ' ';
		}
		$start = intval($start);
		if($start > 0) {
			$options .= ' LIMIT ' . $start;
			$end = intval($length);
			if($end) {
				$options .= ', ' . $end;
			}
		}

		if(count($goodDataWithTables) === 0){
			$fields = '*';
		} else {
			$fields = implode(',', $goodDataWithTables);
		}
		
		$q = Database::get()->PDO()->prepare('SELECT ' . $fields . ' FROM ' . $table . $options);
		$i = 0;
		foreach($goodWhere as $key => $value) {
			if(is_numeric($value)) {
				$q->bindValue(':' . $i, intval($value), PDO::PARAM_INT);				
			} else {
				$q->bindValue(':' . $i, $value);
			}
			$i++;
		}
		$q->execute();
		$result = $q->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}
	
	/**
	 * Adds data to the database. 
	 * @param array $data An array of keys and values where the key is the column name.
	 * @param boolean $replace If true does an MySQL 'REPLACE', if false does an 'INSERT'
	 * @param boolean $ignore If true MySQL/PHP will not generate any error message if there is already
	 *	an item with that primary key or has the same unique index. If false, then MySQL/PHP *may* 
	 *	generate an	error message depending on settings. Note: $ignore does nothing if $replace === true.
	 * @return 
	 */
	public function create(array $data, $replace = false, $ignore = false) {
		$table = $this->getTable();
		$columns = $this->getColumns();
		$goodData = array_intersect_key($data, $columns);
		if(count($goodData) === 0) {
			return;
		}
		
		$action = '';
		if($replace === true) {
			$action = 'REPLACE INTO ';
		} else if($ignore === true) {
			$action = 'INSERT IGNORE INTO ';
		} else {
			$action = 'INSERT INTO ';
		}
		$options = '';
		$i = 0;
		$insertColumns = '';
		foreach($goodData as $key => $value) {
			$options .= ':' . $i . ',';
			$insertColumns .= $key . ',';
			$i++;
		}
		$options = substr($options, 0, strlen($options) - 1); 
		$insertColumns = substr($insertColumns, 0, strlen($insertColumns) - 1); 
		$q = Database::get()->PDO()->prepare($action . $table . ' (' . $insertColumns . ') VALUES (' . 
				$options . ')' );
		$i = 0;
		foreach($goodData as $key => $value) {
			if(is_numeric($value)) {
				$q->bindValue(':' . $i, intval($value), PDO::PARAM_INT);				
			} else {
				$q->bindValue(':' . $i, $value);
			}
			$i++;
		}
		$q->execute();
		
		return Database::get()->PDO()->lastInsertId();
	}
		
	/**
	 * Updates the table.
	 * @param array $data Key-value pairs that represent the columns to update.
	 * @param array $where Key-value pairs to be used to filter the information.
	 */
	public function update(array $data, array $where) {
		$table = $this->getTable();
		$columns = $this->getColumns();
		$goodData = array_intersect_key($data, $columns);
		$goodWhere = array_intersect_key($where, $columns);
		
		if(count($goodData) === 0){
			return;
		}
		
		$options = '';
		$i = 0;
		foreach($goodData as $key => $value) {
			$options .= $key . ' = :' . $i . ',';
			$i++;
		}
		$options = substr($options, 0, strlen($options) - 1); 

		if(count($goodWhere) > 0) {
			$options .= ' WHERE 1 ';
			foreach($goodWhere as $key => $value) {
				$options .= ' AND ' . $key . ' = :' . $i;
				$i++;
			}
		}
		
		$q = Database::get()->PDO()->prepare('UPDATE ' . $table . ' SET ' . $options);
		$i = 0;
		foreach($goodData as $key => $value) {
			if(is_numeric($value)) {
				$q->bindValue(':' . $i, intval($value), PDO::PARAM_INT);				
			} else {
				$q->bindValue(':' . $i, $value);
			}
			$i++;
		}
		foreach($goodWhere as $key => $value) {
			if(is_numeric($value)) {
				$q->bindValue(':' . $i, intval($value), PDO::PARAM_INT);				
			} else {
				$q->bindValue(':' . $i, $value);
			}
			$i++;
		}
		$q->execute();
	}
	

	/**
	 * Deletes data from the database.
	 * @param aray $where An array of keys and values where the key is the column name.
	 */
	public function delete($where) {
		$table = $this->getTable();
		$columns = $this->getColumns();
		$goodWhere = array_intersect_key($where, $columns);
	
		if($goodWhere === 0) {
			return;
		}
		
		$options = ' WHERE 1 ';
		$i = 0;
		foreach($goodWhere as $key => $value) {
			$options .= ' AND ' . $key . ' = :' . $i;
			$i++;
		}
		
		$q = Database::get()->PDO()->prepare('DELETE FROM ' . $table . $options);
		$i = 0;
		foreach($goodWhere as $key => $value) {
			if(is_numeric($value)) {
				$q->bindValue(':' . $i, intval($value), PDO::PARAM_INT);				
			} else {
				$q->bindValue(':' . $i, $value);
			}
			$i++;
		}
		$q->execute();
	}	
	
	/**
	 * Configures the database by adding the appropiate table.
	 * @param Database $database The database to add the table into.
	 */
	static public function configure() {
		$class = get_called_class();
		$columns = $class::get()->getColumns();
		$options = '';
		$primaryKeys = array();
		foreach($columns as $key => $value) {
			$type = $value['Type'];
			$null = '';
			if($value['Null'] === 'NO') {
				$null = ' NOT NULL';
			} 
			if($value['Key'] !== '') {
				$primaryKeys[] = $key;
			}
			$default = '';
			if(!is_null($value['Default'])) {
				$default = ' DEFAULT ' . (string) $value['Default'];
			}
			$extra = '';
			if($value['Extra'] !== '') {
				$extra = ' ' . $value['Extra'];
			}
			$options .= ' ' . $key . ' ' . $type . $null . $default . $extra . ',';
		}
		if(count($primaryKeys) !== 0) {
			$options .= ' PRIMARY KEY( ' . implode(',', $primaryKeys) . ' )';
		} else {
			$options = substr($options, 0, strlen($options) - 1);
		}
		
		$index_query = '';
		$indexes = $class::get()->getIndexes();
		foreach($indexes as $key => $value) {
			if($key !== "PRIMARY") {
				$index_query .= 'CREATE UNIQUE INDEX ' . $key . ' ON ' . 
					$class::get()->getTable() . ' ( ' . implode(', ', $value) . ' ); ';
			}
		}		
		
		$q = Database::get()->PDO()->prepare('CREATE TABLE IF NOT EXISTS ' . 
			$class::get()->getTable() . ' (' . $options . '); ' . $index_query);
		$q->execute();
	}
	
	/**
	 * A wrapper for Object's get() function to help with type hints.
	 * @return QueryBuilder
	 */
	static public function get($parameters = array()) {
		return parent::get();
	}
	
	/**
	 * Get the table name. The returned result should include a prefix.
	 */
	abstract public function getTable();
	
	/**
	 * Get the columns as an array of key-value pairs. The keys should be the column names and the 
	 * values should be arrays of the column definition as returned by SHOW COLUMNS
	 */
	abstract public function getColumns();
}

?>