<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class to clean values in query
 * and convert them to type of the database table column type
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category database
 * @subpackage database
 */
class Base_Db_Query_Builder_Delete extends Database_Query_Builder_Where {

    protected $_join = array();
    // The last JOIN statement created
    protected $_last_join;

    // DELETE FROM ...
    protected $_table;

    /**
     * Set the table for a delete.
     *
     * @param   mixed  $table  table name or array($table, $alias) or object
     * @return  void
     */
    public function __construct($table = NULL)
    {
        if ($table)
        {
            // Set the inital table name
            $this->_table = $table;
        }

        // Start the query with no SQL
        return parent::__construct(Database::DELETE, '');
    }

    /**
     * Sets the table to delete from.
     *
     * @param   mixed  $table  table name or array($table, $alias) or object
     * @return  $this
     */
    public function table($table)
    {
        $this->_table = $table;

        return $this;
    }

    /**
     * Adds addition tables to "JOIN ...".
     *
     * @param   mixed   $table  column name or array($column, $alias) or object
     * @param   string  $type   join type (LEFT, RIGHT, INNER, etc)
     * @return  $this
     */
    public function join($table, $type = NULL)
    {
        $this->_join[] = $this->_last_join = new Database_Query_Builder_Join($table, $type);

        return $this;
    }

    /**
     * Adds "ON ..." conditions for the last created JOIN statement.
     *
     * @param   mixed   $c1  column name or array($column, $alias) or object
     * @param   string  $op  logic operator
     * @param   mixed   $c2  column name or array($column, $alias) or object
     * @return  $this
     */
    public function on($c1, $op, $c2)
    {
        $this->_last_join->on($c1, $op, $c2);

        return $this;
    }

    /**
     * Adds "USING ..." conditions for the last created JOIN statement.
     *
     * @param   string  $columns  column name
     * @return  $this
     */
    public function using($columns)
    {
        $columns = func_get_args();

        call_user_func_array(array($this->_last_join, 'using'), $columns);

        return $this;
    }

    /**
     * Compile the SQL query and return it.
     *
     * @param   mixed  $db  Database instance or name of instance
     * @return  string
     */
    public function compile($db = NULL)
    {
        if ( ! is_object($db))
        {
            // Get the database instance
            $db = Database::instance($db);
        }

        // Start a deletion query
        $query = 'DELETE ';
        if (is_array($this->_table)) {
            $query .= $db->quote_table($this->_table[1]);
        }
        $query .=' FROM '.$db->quote_table($this->_table);
        if ( ! empty($this->_join))
        {
                    // Add tables to join
                    $query .= ' '.$this->_compile_join($db, $this->_join);
        }

        if ( ! empty($this->_where))
        {
            // Add deletion conditions
            $query .= ' WHERE '.$this->_compile_conditions($db, $this->_where);
        }

        if ( ! empty($this->_order_by))
        {
            // Add sorting
            $query .= ' '.$this->_compile_order_by($db, $this->_order_by);
        }

        if ($this->_limit !== NULL)
        {
            // Add limiting
            $query .= ' LIMIT '.$this->_limit;
        }

        $this->_sql = $query;

        return parent::compile($db);
    }

    public function reset()
    {
        $this->_table = NULL;
        $this->_where = array();

        $this->_parameters = array();

        $this->_sql = NULL;

        return $this;
    }
}
