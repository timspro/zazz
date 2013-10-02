<?php
/**
 * Represents a JOIN operation for the purposes of information retrieval.
 */
class Join {
	private $column;
	private $table;
	private $preserve;

	/**
	 * Constructs a Join object.
	 * @param string $table The table to perform the join with in the form of the QueryBuilder child
	 *	class.
	 * @param string $column The column
	 * @param boolean $preserve Specifies whether results should be kept from the first table when they
	 *	can't be matched with the second (i.e. true corresponds to "LEFT JOIN", false corresponds to
	 *	"INNER JOIN").
	 */
	public function __construct($column, QueryBuilder $table, $preserve = false) {
		$this->column = $column;
		$this->table = $table; 
		$this->preserve = $preserve;
	}
	
	/**
	 * Gets the table name.
	 * @return string The table name.
	 */
	public function getTable() { return $this->table->getTable(); }
	
	/**
	 * Gets the column name that will be joined on.
	 * @return string The column name.
	 */
	public function getColumn() { return $this->column; }
	
	/**
	 * Gets if data from the first table should be preserved (i.e. do 'LEFT JOIN').
	 * @return boolean
	 */
	public function getPreserve() { return $this->preserve;}
}
?>
