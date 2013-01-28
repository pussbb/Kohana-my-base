<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class to work with db queries easily
 *
 *<code>
 * $m = new Model_User::find_all(array(
 *    'login' => 'bla'
 * ))
 *</code>
 * @package Kohana-my-base
 * @copyright 2013 pussbb@gmail.com(alexnevpryaga@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category database
 * @subpackage database
 */

class Base_Model extends Base_Db_Model {

    /**
     * array of model objects
     *
     * if in select query more than one record
     * was returned
     * @var array
     * @access public
     */
    public $records = array();

    /**
     * default value for limit rows
     * @var int
     * @access public
     */
    public $per_page = 10;

    /**
     * number of total records
     *
     * by default count($this->records), but if system filter 'count_total'
     * was specified. This variable will be contain count of all records in DB
     * even if limit was set.
     * @var int
     * @access public
     */
    public $count = 0;

    /**
     * Sets default ordering for DB query
     *
     * <code>
     *  <?php
     *     class Model_User extends Model{
     *          protected $order = array('email', 'DESC');
     *     }
     *  ?>
     * </code>
     * @var array
     * @access protected
     */
    protected $order = array();

    /**
     * sets the primary key name
     * <code>
     *  <?php
     *     class Model_User extends Model{
     *          protected $primary_key = 'email';
     *     }
     *  ?>
     * </code>
     * @var string
     * @access protected
     */
    protected $primary_key = 'id';

    /**
     * contains database table name
     *
     * Model_User ->(in database) table will be `users`
     * @var string
     * @access private
     */
    private $db_table = NULL;


     /**
     * contains module name
     *
     * @var string
     * @access private
     * @internal
     */
    private $module_name = NULL;

    /**
     * DB object for queries(uses Kohana's DB class)
     * @var object
     * @access protected
     */
    protected $db_query = NULL;

    /**
     * last inserted row in db for table
     *
     * after insert some row to db, last inserted row id in db will be append
     * to that variable
     * @var int
     * @access protected
     */
    protected $last_inserted_id = NULL;

    /**
     * controls if need to validate data
     *
     * when inserting or updating row
     * @var bool
     * @access protected
     */
    protected $validate = TRUE;

    /**
     * Enable or disable cleaning garbage
     * @var bool
     * @access protected
     */
    protected $auto_clean = TRUE;

    /**
     * assoc array of errors for this model
     * @var array
     * @access private
     */
    private $errors = array();

    /**
     * Last normal SQL query executed as string
     * @var string
     * @access private
     */
    private $last_query = NULL;

    /**
     * defines system variables(commands)
     *
     * which can parse in filter function
     * @var array
     * @access private
     */
    private $system_filters = array(
        'limit', //limit of rows
        'offset', //offset ...
        'with', // query with join of known relation
        'total_count', // for select will added total_count to count all rows if limit set
        'distinct',
        'group_by',
        'expression'
    );

    /**
     * Count all rows with the same conditions or not
     *
     * if need to make another one query to count all records ignoring limit and offset
     * @var bool
     * @access private
     */
    private $count_total = FALSE;

    /**
     * contains all necessary data to create appropriate model from join query
     * @internal
     */
    private $with = array();


    /**
     * array from field meta_data(json string) - prevent multiplier usage of json_decode
     * @internal
     */
    private $meta_data_cache = NULL;

    /**
     *
     * @internal
     */
    private $_loaded = FALSE;

    /**
     * ignore it just a hack
     * @internal
     */
    private $select_args = array();

    /**
     *
     */
    const BELONGS_TO = 1;
    /**
     *
     */
    const HAS_MANY = 2;
    /**
     *
     */
    const HAS_ONE = 3;

    /**
     *
     */
    const STAT = 4;


    /**
     * Constructs the object
     *
     * <code>
     *
     *      $model = new Model_User(array(
     *          'email' => 'domain@site.com',
     *          .....
     *      ));
     *
     * </code>
     * @param array $params
     * @access public
     * @return \Base_Model
     *   @internal
     */
    public function __construct($params = NULL)
    {
        $this->_table_columns = $this->get_table_columns();
        $this->_table_fields = array_keys($this->_table_columns);
        if (Arr::is_array($params)) {
            $this->update_params($params);
        }
        if (is_numeric($params)) {
            $this->data[$this->primary_key] = intval($params);
        }
        $this->db_table = self::db_table_name();
        $this->module_name = strtolower(self::module_name());


    }

    /**
     * dynamically append variable to object
     *
     * <code>
     * $model = new Model();
     * $model->login = 'user';
     * </code>
     * @param $name
     * @param $value
     * @access public
     * @internal
     */
    public function __set($name, $value)
    {
        if (in_array($name, $this->_table_fields))
            $value = $this->sanitize($name, $value);
        $this->data[$name] = $value;
    }

    /**
     * get value of dynamically appended variable
     *
     * <code>
     * $model = new Model();
     * $model->login = 'user';
     * echo $model->login; // will output user
     * </code>
     * @param $name
     * @throws Exception_Collection_PropertyNotExists
     * @return mixed
     * @access public
     * @internal
     */
    public function __get($name)
    {
        if (isset($this->$name))
            return $this->data[$name];

        $relation = $this->get_relation($name);
        if ( ! $relation)
            throw new Exception_Collection_PropertyNotExists();
        return $this->_relation($name, $relation);
    }

    /**
     * checks if dynamically appended variable exists
     *
     * @param $name
     * @return bool
     * @access public
     * @internal
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * removes dynamically appended variable
     *
     * <code>
     * $model = new Model();
     * $model->login = 'user';
     * unset($model->login); //here
     * </code>
     * @param $name
     * @access public
     * @internal
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * calls functions for Base_Model or DB classes in Kohana
     * @param $name
     * @param $arguments
     * @throws Exception_MethodNotExists
     * @return mixed
     * @internal
     */
    public function __call($name, $arguments)
    {

        if (method_exists($this, $name))
            return call_user_func_array(array($this, $name), $arguments);
        if (method_exists($this->db_query, $name))
                return call_user_func_array(array($this->db_query, $name), $arguments);

        $relation = $this->get_relation($name);
        if ( ! $relation)
            throw new Exception_MethodNotExists();

        return $this->_relation($name, $relation, Arr::get($arguments, 0, array()));
    }

    /**
     * adds availability to call some functions as static
     *
     * <code>
     *  self::destroy($id);
     *  self::exists($params);
     * </code>
     * @ignore
     * @static
     * @param $name
     * @param $arguments
     * @return mixed/NULL
     * @access public
     * @internal
     */
    public static function __callStatic($name, $arguments)
    {
        $klass = get_called_class();
        switch ($name) {
            case 'destroy':
                $obj = new $klass();
                return $obj->destroy($arguments[0]);
                break;
            case 'exists':
                return  call_user_func_array(array(new $klass, $name), $arguments);
                break;
            case 'table_labels':
                return call_user_func_array(array(new $klass, 'labels'), $arguments);
            default:
                break;
        }
    }

    /**
     * returns last query string
     *
     * <code>
     *  $model = Model_User::find($id);
     *  echo (string) $model; // returns SELECT * FROM ...
     * </code>
     * @return null|string
     * @access public
     * @internal
     */
    public function __toString()
    {
        $string = (string) $this->db_query;
        if ($string)
            return $string;
        return $this->last_query;
    }

   /**
     * Returns model name
     *
     * e.g. Model_User::module_name() will return 'User'
     * @static
     * @param string $glue
     * @return string name of current module
     * @access public
     */
    public static function module_name($glue = '_')
    {
        $klass_pieces = explode('_', get_called_class());
        unset($klass_pieces[0]);
        return implode($glue, $klass_pieces);
    }

    /**
     * return a table name in plural form
     *
     * Model_User::db_table_name() = `users`
     * @static
     * @param string $glue
     * @return mixed
     * @access private
     */
    private static function db_table_name($glue = '_')
    {
        return Inflector::plural(strtolower(self::module_name($glue)));
    }

    /**
     * returns relation data or null
     * @param $name string
     * @return array|null
     */
    private function get_relation($name)
    {
        return Arr::get($this->relations(), $name);
    }

    /**
     * get data for relation depends on relation option
     * @param $name string
     * @param $relation array
     * @param $filter array
     * @throws Base_Db_Exception_UnknownRelationType
     * @return
     */
    private function _relation($name, $relation, $filter = array() )
    {
        $type = Arr::get($relation, 0);
        $klass = Arr::get($relation, 1);
        $foreign_key = Arr::get($relation, 2);
        $model_key = Arr::get($relation, 3, $this->primary_key);
        $filter[$foreign_key] = $this->$model_key;
        switch ($type) {
            case self::BELONGS_TO:
            case self::HAS_ONE:
                $result = $klass::find($filter);
                break;
            case self::HAS_MANY:
                $result = $klass::find_all($filter)->records;
                break;
            case self::STAT:
                $obj = new $klass();
                $result = $obj->select(array(DB::expr('COUNT('.$obj->query_field($obj->primary_key).')'), 'total_count'))
                                ->filter($filter)
                                    ->execute()
                                        ->get('total_count');
                break;
            default:
                throw new Base_Db_Exception_UnknownRelationType();
                break;
        }
        $this->data[$name] = $result;
        return $result;
    }

    /**
     * returns assoc array of dynamically append variable
     *
     * <code>
     *  $model = Model_User::find($id);
     *  var_dump($model->__toArray());
     *  //print
     * array(
     *   'login' => 'bla',
     *   'email' => 'email@site.com',
     *   ....
     * )
     * </code>
     * @return array|null
     * @internal
     */
    public function __toArray()
    {
        return $this->obj_to_array($this);
    }

    /**
     * recursively parse data variable and convert all objects to assoc array
     *
     * @param \Base_Model |object $obj (must be instanceof Base_Model)
     * @access private
     * @return array
     */
    private function obj_to_array(Base_Model $obj)
    {
        $result = array();
        foreach($obj->data as $key => $value) {
            if (is_object($value) && $obj instanceof Base_Model) {
                $value = $this->obj_to_array($value);
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * returns assoc array of dynamically append variable
     *
     * <code>
     *  $model = Model_User::find($id);
     *  var_dump($model->as_array());
     *  //print
     * array(
     *   'login' => 'bla',
     *   'email' => 'email@site.com',
     *   ....
     * )
     * </code>
     * @return array|null
     * @internal
     */
    public function as_array()
    {
        return $this->__toArray();
    }

    /**
     * find record in database table by condition
     *
     * <code>
     *  //if we need to find by primary key
     *  $model = Model_User::find($some_value);
     *  // complex condition
     *  $model = Model_User::find(array(
     *    'login' => 'bla',
     *    ....
     *  ));
     *  //get values
     *  echo $model->login;// output 'bla'
     * </code>
     *
     * if row was not found Exception will be called
     * @static
     * @param $filter
     * @param bool|null $cache
     * @throws Base_Db_Exception_RecordNotFound
     * @return mixed
     */
    public static function find($filter, $cache = FALSE)
    {
        $klass_name = get_called_class();
        $klass = new $klass_name;
        if (is_numeric($filter)) {
            $filter = array($klass->primary_key => $filter);
        }
        $result = $klass::find_all($filter, 1, NULL, $cache);
        if ( ! Arr::get($result->records, 0))
            throw new Base_Db_Exception_RecordNotFound();
        $_result = $result->records[0];
        $_result->last_query = $result->last_query;
        return $_result;
    }

    /**
     * find a collections of rows in database table
     *
     * <code>
     *  // complex condition only
     *  $model = Model_User::find_all(array(
     *    'login' => 'bla',
     *    ....
     * ));
     * </code>
     *
     * to get records
     * <code>
     *  $records = $model->records;
     * </code>
     * each item in array it's a object of certain module
     * so you can do everything.
     * e.g. delete
     *<code>
     * foreach($model->records as $_model)
     * {
     *  $_model->destroy();//can be everything from Base_Model
     * }
     *</code>
     * @static
     * @param array $filter
     * @param null $limit
     * @param null $offset
     * @param bool|null $cache
     * @return mixed
     */
    public static function find_all($filter = array(), $limit = NULL, $offset = NULL, $cache = FALSE)
    {
        $klass_name = get_called_class();
        $obj = new $klass_name();
        $obj->select('*', $limit, $offset, $cache);
        $obj->filter($filter)->exec();
        $obj->data = array();
        return $obj;
    }

    /**
     *  Helper function to return Kohana_Database SELECT object
     *  data parsed throw Kohana My Base Model
     *
     * @access public
     * @static
     * @param mixed $select
     * @param array $filter
     * @param null $limit
     * @param null $offset
     * @param null $cache
     * @return Database_Query_Builder
     */
    public static function select_query($select, $filter = array(), $limit = NULL, $offset = NULL, $cache = NULL)
    {
        $klass = get_called_class();
        return $klass::query(Database::SELECT, $select, $filter, $limit, $offset, $cache );
    }

    /**
     *  Helper function to return Kohana_DB DELETE object
     *  data parsed throw Kohana My Base Model
     * @access public
     * @static
     * @param array $filter
     * @return Database_Query_Builder
     */
    public static function delete_query($filter = array())
    {
        $klass = get_called_class();
        return $klass::query(Database::DELETE, $filter);
    }

    /**
     *  Helper function to return Kohana_DB INSERT object
     *  data parsed throw Kohana My Base Model
     *
     * @access public
     * @static
     * @param array $filter
     * @return Database_Query_Builder
     */
    public static function insert_query($filter = array())
    {
        $klass = get_called_class();
        return $klass::query(Database::INSERT, $filter);
    }

    /**
     *  Helper function to return Kohana_DB UPDATE object
     *  data parsed throw Kohana My Base Model
     *
     * @access public
     * @static
     * @param mixed $primary_value
     * @param array $filter
     * @return Database_Query_Builder
     */
    public static function update_query($primary_value, $filter = array())
    {
        $klass = get_called_class();
        return $klass::query(Database::UPDATE, $primary_value, $filter);
    }

    /**
     *  Helper function to return Kohana_DB object
     *  data parsed throw Kohana My Base Model
     * @access public
     * @static
     * @throws Base_Db_Exception_UnknownDatabaseQueryType
     * @return Database_Query_Builder
     */
    public static function query()
    {
        $args = func_get_args();
        $klass = get_called_class();
        $obj = new $klass();
        switch (Arr::get($args, 0)) {
            case Database::SELECT:
                $obj->select(Arr::get($args, 1), Arr::get($args, 3), Arr::get($args, 4), Arr::get($args, 5))
                    ->filter(Arr::get($args, 2));
                break;
            case Database::DELETE:
                $obj->destroy(Arr::get($args, 1));
                break;
            case Database::INSERT:
                $obj->insert(Arr::get($args, 1));
                break;
            case Database::UPDATE:
                $obj->{$obj->primary_key} = Arr::get($args, 1);
                $obj->update()
                    ->update_params(Arr::get($args, 2));
                break;
            default:
                throw new Base_Db_Exception_UnknownDatabaseQueryType();
                break;

        }
        return $obj->prepare_for_query()->db_query;
    }

    public function loaded()
    {
        return $this->_loaded;
    }

    public function has($name, $values = NULL)
    {

        $relation = Arr::get($this->relations(), $name);
        if ( ! $relation)
            throw new Base_Db_Exception_UnknownRelation();

        $klass = Arr::get($relation, 1);
        $model = new $klass();
        $field = Arr::path($relation, 3, $this->primary_key);
        $foreign_key = Arr::path($relation, 2, $model->primary_key);
        if ( ! $values )
            $values = $this->$field;
        $select = array(DB::expr('COUNT('.$model->query_field($model->primary_key).')'), 'total_count');
        $result = $model::select_query($select, array($foreign_key => $values))
                        ->execute()
                            ->get('total_count');
        return $result > 0;
    }

    /**
     * returns array with table columns SQL query
     * @static
     * @param null $table
     * @return array
     */
    public static function table_columns($table = NULL)
    {
        if ( ! $table) {
            $klass_name = get_called_class();
            $table = $klass_name::db_table_name();
        }
        $columns = Kohana::cache($table . '_columns');
        if ( ! $columns) {
            $columns = Database::instance()->list_columns($table);
            foreach ($columns as $key => $values) {
                if (Arr::get($values, 'character_maximum_length'))
                    $columns[$key]['max'] = $values['character_maximum_length'];
            }
            Kohana::cache($table . '_columns', $columns, 3600 * 24 * 30);
        }
        return $columns;
    }

    /**
     * execute commands which get as array
     * e.g.
     * <code>
     * $m = Model_User::find_all(array(
     *    'limit' => 2, //will set limit
     * ));
     * </code>
     * <ul>
     *  <li>limit - sets limit for query</li>
     *  <li>offset - sets offset for query</li>
     *  <li> total_count - sets to execute another one query to count all records
     *    in table with the same conditions from previous query ignoring limit
     *    and offset. Result of this query will be set to $count variable of this class
     *  </li>
     * </ul>
     * @param $key
     * @param $value
     * @access private
     */
    private function system_filters($key, $value)
    {
        switch ($key) {
            case 'limit':
                $this->db_query->limit((int) $value);
                break;
            case 'offset':
                $this->db_query->offset((int) $value);
                break;
            case 'total_count':
                $this->count_total = TRUE;
                break;
            case 'distinct':
                $this->db_query->distinct($value);
                break;
            case 'group_by':
                $this->db_query->group_by($value);
                break;
            case 'with':
                if (is_array($value)){
                    foreach ($value as $item) {
                        $this->with($item);
                    }
                }
                else {
                    $this->with($value);
                }
                break;
            case 'expression':
                $expression = $value[0];
                unset($value[0]);

                $values = array();
                foreach($value as $key => $item)
                {

                    if (in_array($key, $this->_table_fields))
                        $key = $this->query_field($key, '.', TRUE);

                    if (is_array($item)) {
                        foreach($item as $_item) {
                            $values[] = $key;
                            $values[] = '\''.Base_Db_Sanitize::string($_item).'\'';
                        }
                    }
                    else {
                        $values[] = $key;
                        $values[] = '\''.Base_Db_Sanitize::string($item).'\'';
                    }
                }
                $this->db_query->where(NULL, NULL, DB::expr(vsprintf($expression, $values)));
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * returns array with names all columns from db table for request with join
     * @internal
     */
    public function query_columns_for_join()
    {
        $result = array();
        foreach ($this->_table_fields as $column)
        {
            $result[] = array($this->query_field($column), $this->query_field($column, ':'));
        }
        return $result;
    }

    /**
     * JOIN function alternative
     * @param $name string string can be name of some model or relation name,
     * @throws Base_Db_Exception_UnknownRelation
     * @return void
     * @access public
     */
    public function with($name)
    {
        if ($this->_loaded)
            throw new Base_Db_Exception_LoadedModel;

        $relation = Arr::get($this->relations(), $name);
        if ( ! $relation)
            throw new Base_Db_Exception_UnknownRelation();

        $klass = Arr::get($relation, 1);
        $model = new $klass();
        $type = Arr::path($relation, 0);
        $field = Arr::path($relation, 3, $this->primary_key);
        $foreign_key = Arr::path($relation, 2, $model->primary_key);

        $model_fields = array();
        if ($type !== self::STAT) {
            $model_fields = $model->query_columns_for_join();
            if ($this->query_type() == 'select')
                $this->db_query = call_user_func_array(array($this->db_query , 'select'), $model_fields);
            $this->db_query
                ->join(array($model->db_table, $model->module_name), 'LEFT')
                   ->on($model->query_field($foreign_key), '=', $this->query_field($field));
        }
        else {
            $model_fields[] = array(DB::expr('COUNT('.$model->query_field($model->primary_key).')'), $name);
            $query = $model::select_query($model_fields[0], array($foreign_key => $this->query_field($field)));
            $this->db_query->select(array($query, $name));
        }

        $this->with[$name] = array(
                $klass,
                Arr::path($model_fields, '*.1'),
                $model->_table_fields,
                $type
            );
    }

    /**
     * return filed name for query
     * @internal
     * @access private
     * @param $name string
     * @param $delimiter string
     * @param $escape bool
     * @throws Exception_Collection_ObjectNotSupported
     * @throws Base_Db_Exception_EmptyColumnName
     * @return object
     */
    private function query_field($name, $delimiter = '.', $escape = FALSE)
    {
        if ( ! $name)
            throw new Base_Db_Exception_EmptyColumnName();

        if ( ! is_object($name)){
            if ( ! $escape)
                return $this->module_name.$delimiter.$name;
            else
                return "`$this->module_name`$delimiter`$name`";
        }


        if (get_class($name) != 'Database_Expression')
           throw new Exception_Collection_ObjectNotSupported();
        return $name;
    }

    /**
     * checks if key contains special symbols to set different comparison keys
     *
     *@param $key - string
     *@return bool
     *@access private
     */
    private function sql_comparison($key)
    {
        return (bool)preg_match('/^\|\|\s|^>\s|^<\s|^<>\s|^\!\s/',$key, $matches);
    }

    /**
     * Helper function to create WHERE clause
     *
     * what for to rewrite?
     * <ul>
     *   <li>this function build more safe where condition for queries</li>
     *   <li>any field that are not describe in table ( see table_columns()) will be ignored</li>
     *   <li>each type for field will be cleaned and convert to their type in database </li>
     *   <li>do not required setting values for fields if you already have them in object. Just tell what field you need</li>
     * </ul>
     * can be changed in future, because supports only
     * <ul>
     *   <li> = comparison key ( WHERE `field` = `value`)</li>
     *   <li> IN ( WHERE `field` IN (...))</li>
     *   <li> DB expression ->e.g. WHERE `field` REGEXPR "..."</li>
     * </ul>
     * filter options sets throw array assoc or not
     * e.g. of not assoc array
     * <code>
     *   array('login', 'email');
     * </code>
     * in that case values will be get from current model object if they exists
     * e.g. of assoc array
     * <code>
     *   array(
     *       'login' => 'bla',
     *       'email' => 'email@site.com',
     *  );
     * </code>
     * Also values of fields can be array . In this case comparison key will be IN
     * Also supports DB::expr as value
     *
     * @uses Database_Query_Builder_Where functions
     * @param $filter array
     * @throws Exception_Collection_InvalidArray
     * @throws Base_Db_Exception_UnknownRelation
     * @return Base_Model
     */
    public function filter($filter)
    {

        if ( ! Arr::is_array($filter))
            throw new Exception_Collection_InvalidArray();

        if ( ! $this->db_query)
            $this->select();

        $system_filters = array();
        $fields = array();
        if ( ! Arr::is_assoc($filter)) {
            foreach ($filter as $field) {
                if ( ! array_key_exists($field, $this->_table_columns))
                    continue;
                $fields[$field] = Arr::get($this->data, $field);
            }
        }
        else {
            foreach ($filter as $key => $value) {
                if (in_array($key, $this->system_filters)) {
                    $system_filters[$key] = $value;
                    unset($filter[$key]);
                    continue;
                }
                $_key = $key;
                if ($this->sql_comparison($key))
                    $_key = Arr::get(explode(' ', $key), 1);
                if (array_key_exists($_key, $this->_table_columns)) {
                    $fields[$key] = $value;
                    unset($filter[$key]);
                    continue;
                }
            }
        }
        $this->db_query->where_open();
        foreach ($fields as $key => $value) {
            extract($this->sql_filter_fields($key, $value));
            $this->db_query->$clause($this->query_field($key), $comparison_key, $this->sanitize($key, $value));
        }
        $this->db_query->where_close_empty();

        if ((isset($system_filters['limit']) || (bool)preg_match('/LIMIT/', $this->db_query->compile()))
            && isset($system_filters['with'])
            && $this->query_type() === 'select') {
            $db = clone $this->db_query;

            if (array_key_exists('limit', $system_filters)) {
                $db->limit($system_filters['limit']);
                unset($system_filters['limit']);
            }
            if (array_key_exists('offset', $system_filters)) {
                $db->offset($system_filters['offset']);
                unset($system_filters['offset']);
            }

            $this->db_query
                ->reset()
                ->offset(NULL)
                ->select(Arr::get($this->select_args, 0))
                ->from(array($db, $this->module_name))
                ->cached(Arr::get($this->select_args, 1));

        }

        foreach($system_filters as $key => $value) {
             $this->system_filters($key, $value);
        }

        if ( ! $this->with)
            return $this;

        $this->db_query->where_open();
        foreach($filter as $key => $value) {
            extract($this->sql_filter_fields($key, $value));
            $filter_parts = explode('.', $key);
            $relation = Arr::get($this->with, $filter_parts[0]);
            if ( ! $relation)
                throw new Base_Db_Exception_UnknownRelation;

            $klass = new $relation[0];
            $key = $filter_parts[1];
            $this->db_query->$clause($klass->query_field($key), $comparison_key, $klass->sanitize($key, $value));
        }
        $this->db_query->where_close_empty();

        return $this;
    }

    /**
     * returns pure key and value which already prepared to insert into query
     * with provided comparison key etc...
     *
     * @param $key string
     * @param $value mixed
     * @throws Exception_Collection_ObjectNotSupported
     * @access private
     * @return array
     */
    private function sql_filter_fields($key, $value)
    {

        $comparison_key = '=';
        $clause = 'where';
        if ($this->sql_comparison($key)) {
            $key_parts = explode(' ', $key);
            switch ($key_parts[0]) {
                case '||':
                    $clause = 'or_where';
                    $key = $key_parts[1];
                    break;
                case '!' :
                    $comparison_key = '<>';
                    $key = $key_parts[1];
                    break;

                default:
                    $comparison_key = $key_parts[0];
                    $key = $key_parts[1];
                    break;
            }
        }
        switch(gettype($value)) {
            case 'array': {
                $comparison_key =$comparison_key === '<>' ? 'NOT IN' : 'IN';
                break;
            }
            case 'object': {
                if ($value instanceof Base_Model)
                {
                    if ( ! $value->db_query)
                        $value->select($value->primary_key);
                    $value = $value->db_query;
                }
                if ($value instanceof Database_Query_Builder_Select)
                {
                    $value = DB::select()->from(array($value,'t'.mt_rand()));
                    $comparison_key =$comparison_key === '<>' ? 'NOT IN' : 'IN';
                }
                else if ($value instanceof Database_Expression ) {
                    $comparison_key = '';
                }
                else {
                    throw new Exception_Collection_ObjectNotSupported();
                }
                break;
            }
            case 'string':
                if((bool)preg_match('/^[a-z_]+\.[a-z_]+$/', $value, $matches))
                {
                    $parts = explode('.', $value);
                    $relation = Arr::get($this->relations(), $parts[0]);
                    if ( ! $relation) {
                        $value = DB::expr($value);
                        break;
                    }
                    $klass = Arr::get($relation, 1);
                    $obj = new $klass;
                    debug($value);
                    if (in_array($parts[1], $obj->_table_fields))
                       $value = DB::expr($obj->query_field($parts[1],'.',  TRUE));

                }
                else if (in_array($value, $this->_table_fields)) {
                    $value = DB::expr($this->query_field($value, '.', TRUE));
                }
                break;
            case 'NULL': {
                $comparison_key = $comparison_key === '<>' ? 'NOT ' : 'IS';
                break;
            }
        }

        return array(
            'comparison_key' => $comparison_key,
            'key' => $key,
            'value' => $value,
            'clause' => $clause
        );
    }

    /**
     * set parameters for SELECT query
     *
     *
     * @param string|array $select_args can be <ul>
     *   <li>string - e.g. '*'</li>
     *   <li> single array - e.g. array('id','user_id') first field name, second his alias name in sql -> `id` AS `user_id`</li>
     *   <li> multidimensional array - e.g. array( array('id','user_id'), array('login','user_login')...)</li>
     * </ul>
     * @param null $limit
     * @param null $offset
     * @param bool|null $cache
     * @return Base_Model
     * @todo rewrite
     */
    public function select($select_args = '*', $limit = NULL, $offset = NULL, $cache = FALSE)
    {
        if ($this->_loaded)
            throw new Base_Db_Exception_LoadedModel;

        if ( is_string($select_args)) {
            $fields = $this->query_field($select_args);
            $this->db_query = DB::select($fields);
        }
        else if (is_array($select_args)) {
            $fields = array();
            if (Arr::is_assoc($select_args)) {
                foreach ($select_args as $field => $alias) {
                    $fields[] = array($this->query_field($field), $alias);
                }
                $this->db_query = call_user_func_array(array('DB', 'select'), $fields);
            }
            else {
                $fields = array(
                    $this->query_field(Arr::get($select_args, 0)),
                    Arr::get($select_args, 1)
                );
                $this->db_query = DB::select($fields);
            }
        }
        $this->select_args = array($fields, $cache);
        $this->db_query->from(array($this->db_table, $this->module_name));
        $this->db_query->limit($limit)->offset($offset);
        if ($cache)
            $this->db_query->cached($cache);
        return $this;
    }

    /**
     * sets parameters for INSERT query
     *
     * $fields can be sets as array
     * e.g. of not assoc array
     * <code>
     *   array('login', 'email');
     * </code>
     *
     * if you call function save() all values for fields will be automatically get from object
     * if they exists
     * @param array|null $fields
     * @return Base_Model
     */
    public function insert($fields = array())
    {
        if ($this->_loaded)
            throw new Base_Db_Exception_LoadedModel;
        $this->db_query = DB::insert($this->db_table, $fields?:$this->table_fields(TRUE));
        return $this;
    }

    /**
     * sets parameters for UPDATE query
     * @return Base_Model
     * @access public
     */
    public function update()
    {
        $this->db_query = DB::update(array($this->db_table, $this->module_name))
                            ->where($this->query_field($this->primary_key), '=', $this->sanitize($this->primary_key, $this->{$this->primary_key}));
        return $this;
    }

    /**
     * creates DELETE query object
     *
     * if $filter not specified will search by primary key
     * usage example
     * <code>
     * $model->destroy() // if we already have object
     * //or
     * Model_User::destroy($id);
     * //or
     * Model_User::destroy(array('login' => 'bla', ....));
     *</code>
     * @param null $filter
     * @throws Base_Db_Exception_NoRowEffected
     * @return bool
     * @access protected
     */
    protected function destroy($filter = NULL)
    {
        $this->db_query = DB::delete(array($this->db_table, $this->module_name));

        if (is_numeric($filter)) {
            $this->{$this->primary_key} = $filter;
            $filter = array($this->primary_key => $filter);
        }
        $ok = $this->filter($filter)->save();
        if ( ! $ok)
            throw new Base_Db_Exception_NoRowEffected;
        return TRUE;
    }

    /**
     * returns assoc array with all errors
     * @return array
     * @access public
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * add error message to errors list
     * @param $key string name of the field
     * @param $msg string message
     * @access public
     */
    public function add_error($key, $msg)
    {
        $this->errors[$key] = $msg;
    }

    /**
     * checks if value exists by some condition
     * @param array  $filter Base_Model filter method first parameter
     * @param int $limit
     * @param null $cache
     * @return bool
     * @access protected
     */
    protected function exists($filter, $limit = 1, $cache = NULL)
    {
        $this->select('*', $limit, NULL, $cache);
        if ( ! $filter) $filter = array($this->primary_key);
        return $this->filter($filter)->exec();
    }


    /**
     * make some additional operations before execute query
     * @access private
     * @throws Base_Db_Exception_UnknownDatabaseQueryType
     * @return object self
     */
    private function prepare_for_query()
    {
        switch ($this->query_type()) {
            case 'insert':
                $properties = Object::properties($this->db_query);
                $columns = Arr::get($properties, '_columns', $this->_table_fields);
                $values = Arr::get($properties, '_values');

                if ($columns && ! $values) {
                    $data = array();
                    foreach ($columns as $field) {
                        $data[] = $this->sanitize($field, $this->$field);
                    }
                    $this->db_query->values($data);
                }
                break;

            case 'update':
                foreach ($this->table_fields() as $field) {
                    $this->db_query->value($field, $this->sanitize($field, $this->{$field}));
                }
                break;

            case 'select':
            case 'delete':
                break;
            default:
                throw new Base_Db_Exception_UnknownDatabaseQueryType();

                break;
        }
        return $this;
    }

    /**
     * checks values of fields and convert to type of field
     *
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function sanitize($key, $value)
    {
        if (is_object($value))
            return $value;

        return Base_Db_Sanitize::value(
            Arr::path($this->_table_columns, $key . '.data_type'),
            $value,
            Arr::path($this->_table_columns, $key . '.type')
        );

    }

    /**
     * simple function to validate data before save
     *
     * <code>
     * $additional_rules = array(
     *      'pswd_confirmation' => array(
     *        'not_empty',
     *        array('equals', array($this->pswd_confirmation, $this->password, 'password'))
     *     ),
     *   );
     *   $this->validate($additional_rules);//check according rules
     * </code>
     *
     * Creating rules similar to Kohana's Validation
     * Also if some model has function rules() before saving data it will get from that function and validate data
     *
     * @param array $rules
     * @param array $data
     * @return bool
     */
    public function validate(array $rules = NULL, array $data = NULL)
    {
        $data = $data ? $data : $this->data;
        $rules = $rules ? $rules : array_intersect_key($this->rules(), $data );
        $validator = Validation::factory($data);
        foreach ($rules as $field_name => $_rules)
        {
            foreach ($_rules as $key => $rule) {
                if ($rule === 'unique')
                    $rule = array(array($this, 'unique_validation'), array(':validation', ':field'));

                if ( ! is_array($rule)) {
                    $validator->rule($key, $rule);
                    continue;
                }
                $validator->rule(
                        $key, Arr::get($rule, 0, NULL), Arr::get($rule, 1, NULL)
                );
            }
        }
        $validator->labels($this->labels());
        if ($validator->check())
            return TRUE;

        $errors = $validator->errors('', FALSE);
        if (Arr::is_assoc($errors))
            $this->errors = Arr::merge($this->errors, $errors);

        return FALSE;
    }

    /**
     * user function to make some operations before saving
     *
     * @access protected
     */
    protected function before_save()
    {
        //user manipulations
    }

    /**
     * check if model has primary key
     * @return bool
     */
    public function new_record()
    {
        return ! isset($this->{$this->primary_key});
    }

    /**
     * returns all table fields without field type and if value preset in model
     *
     * @param bool $skip_primary_key
     * @return array
     * @access public
     */
    public function table_fields($skip_primary_key = FALSE)
    {
        if ($this->data)
            $fields = array_keys(array_intersect_key($this->_table_columns, $this->data));
        else
            $fields = $this->_table_fields;
        if ( ! $skip_primary_key)
            unset($fields[$this->primary_key]);
        return $fields ;
    }

    /**
     * function that execute query with user callback functions
     *
     * @return bool
     * @access public
     */
    public function save()
    {
        if ( ! $this->query_type()) {
            if ($this->new_record())
                $this->insert($this->table_fields(true));
            else
                $this->update($this->table_fields(true));
        }

        if ( in_array($this->query_type(), array('insert', 'update'))){
            if ( ! Base_Db_Validation::check($this) || ! $this->validate())
                return FALSE;
        }

        $this->before_save();
        $this->prepare_for_query();

        $responce = $this->exec();

        $this->after_save();
        return $responce;
    }

    /**
     * gets table columns
     *
     * if model has method columns
     * sql query will not execute to get column name and it type
     * @return mixed
     */
    public function get_table_columns()
    {
        return $this->columns()? : self::table_columns();
    }

    /**
     * returns type of query 'select', 'update' ...
     * @return string
     */
    private function query_type()
    {
        if ( ! $this->db_query)
            return NULL;
        $klass_pieces = preg_split('/(?=[A-Z])/', get_class($this->db_query));
        return strtolower(end($klass_pieces));
    }

    /**
     * low level function to execute query
     * @return bool
     */
    protected function exec()
    {
        $this->db_query->as_assoc();
        if ($this->order && $this->query_type() === 'select')
            call_user_func_array(array($this->db_query, 'order_by'), $this->order);
        $result = $this->db_query->execute();
        $this->last_query = (string) $this->db_query;
        $responce = $this->parse_responce($result);
        if ($this->auto_clean)
            $this->clean();
        return $responce;
    }

    /**
     * append data to object according type of query
     * @param $result
     * @return bool
     * @access private
     */
    private function parse_responce($result)
    {
        switch ($this->query_type()) {
            case 'insert':
                $this->last_inserted_id = $result[0];
                $this->{$this->primary_key} = $result[0];
                $result = TRUE;
                break;
            case 'select':
                $_result = $this->parse_result($result);

                $klass = get_called_class();
                if ($this->count_total)
                    $this->count = $this->auto_count_total();

                foreach ($_result as $record) {
                    if ( ! Arr::is_array($record) && ! Arr::is_assoc($record))
                        break;
                    $obj =  new $klass();
                    foreach ($record as $key => $value) {
                        $obj->data[$key] = $value;
                    }
                    $obj->_loaded = count($obj->data) > 0;
                    $this->records[] = $obj;
                }
                $result = $result->count() > 0;
                break;
            case 'delete':
                $result = $result > 0;
                break;
            case 'update':
                $result = $result > 0 || empty($this->errors);
                break;
            default:
                $result = TRUE;
                break;
        }
        return $result;
    }

    /**
     * function returns proper array with values
     * @internal
     * @param object $result DB_Result
     * @return mixed
     */
    private function parse_result($result)
    {
        $this->count = $result->count();
        if ( ! $this->with )
            return $this->count == 1?array($result->current()):$result->as_array();

        $_result = array();

        foreach ($result->as_array() as $row) {
            $_key = $row[$this->primary_key];
            if ( ! isset($_result[$_key]))
                $_result[$_key] = array_combine($this->_table_fields, Arr::extract($row, $this->_table_fields));

            foreach ($this->with as $key => $value) {
                if ($value[3] === self::STAT){
                    $_result[$_key][$key] = $row[$value[1][0]];
                    continue;
                }
                $one_record = $value[3] === self::BELONGS_TO || $value[3] === self::HAS_ONE;
                if ( isset($_result[$_key][$key]) && (is_object($_result[$_key][$key]) && $one_record))
                    continue;
                $klass = $value[0];
                $obj = new $klass;
                foreach ($value[1] as $index_key => $with_field) {
                    $obj->data[$value[2][$index_key]] = $row[$with_field];
                }
                $obj->_loaded = count($obj->data) > 0;
                if ($one_record)
                    $_result[$_key][$key] = $obj;
                else
                    $_result[$_key][$key][] = $obj;

            }
        }

        $this->count = count($_result);
        return array_values($_result);

    }

    /**
     * function to calculate total rows in database column
     * @return mixed
     * @access private
     */
    private function auto_count_total()
    {
        $query = clone $this->db_query;
        $reflectionObject = new ReflectionObject($query);
        $object_properties = $reflectionObject->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($object_properties as $property) {
            $property->setAccessible(true);
            switch ($property->getName()) {
                case '_select':
                    $property->setValue($query, array(
                        array(DB::expr('COUNT('.$this->query_field($this->primary_key).')'), 'total_count'),
                    ));
                    break;
                case '_from':
                    $property->setValue($query, array(array($this->db_table, $this->module_name)));
                    break;
                case '_limit':
                    $property->setValue($query, NULL);
                    break;
                case '_limit':
                    $property->setValue($query, NULL);
                    break;
                case '_join':
                    $property->setValue($query, NULL);
                    break;
                case '_offset':
                    $property->setValue($query, NULL);
                    break;
                case '_sql':
                    $property->setValue($query, NULL);
                    break;
                default:
                    break;
            }
        }
        return $query->execute()->get('total_count');
    }

    /**
     * user callback function which calls after success executing query
     * @access protected
     * @return void
     */
    protected function after_save()
    {
        //user manipulations
    }

    /**
     * helper function to delete garbage after success query
     * @access private
     * @return void
     */
    private function clean()
    {
        $this->db_query = NULL;
        $this->count_total = FALSE;
        $this->with = array();
        $this->errors = array();
    }

    /**
     * helper function to append data to the object
     *
     * <code>
     * $params = array(
     *    'login' => 'blabla'
     *    ....
     * )
     * $model->update_params($params);
     * echo $model->login;// output 'blabla'
     * </code>
     * @param $array
     * @access public
     * @return object self
     */
    public function update_params($array)
    {
        foreach ($array as $key => $value) {
            if (in_array($key, $this->_table_fields))
                $value = $this->sanitize($key, $value);
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * user function to define database table columns
     * @static
     * @return array
     * @access protected
     */
    protected function columns()
    {
        return array();
    }

    /**
     * user function to define validation rules
     * @return array
     */
    public function rules()
    {
        return array();
    }

    /**
     * user function to define representative labels for table fields
     * @return array
     */
    public function labels()
    {
        return array();
    }

    /**
     * sets relations for external models to this model
     * @return array
     */
    protected function relations()
    {
        return array();
    }
    /**
     * returns last inserted id
     * @return null
     */
    public function last_inserted_id()
    {
        return $this->last_inserted_id;
    }

    /**
     * returns last executed query
     * @return null
     */
    public function last_query()
    {
        return $this->last_query;
    }

    /**
     * validate unique value required Validation object
     * @param $validation
     * @param $field
     */
    public function unique_validation($validation, $field)
    {
        $obj = clone $this;
        $result = $obj->exists(array($field));
        if (($this->new_record() && $result) || ($result && $this->id !== $obj->id)) {
            $validation->error($field, ' ' . tr("already exists"));
            return;
        }
    }

    /**
     * returns value of some item in meta_data field
     * @param $key (string)
     * @param $default mixed
     * @access public
     * @return mixed|null
     */
    public function meta_item($key, $default = NULL)
    {
         if (strpos($key, '.') !== FALSE)
            return Arr::path($this->meta_data(), $key, $default);
        return Arr::get($this->meta_data(), $key, $default);
    }

    /**
     * returns encoded data from  table column meta_data(json encoded string)
     * @access public
     * @throws Exception_Json
     * @throws Base_Db_Exception_MetaDataFieldMissing
     * @return array
     */
    public function meta_data()
    {
        if ( ! isset($this->meta_data))
            throw new Base_Db_Exception_MetaDataFieldMissing();

        if ($this->meta_data_cache)
            return $this->meta_data_cache;

        $data  = json_decode($this->meta_data, TRUE);
        if (json_last_error() != JSON_ERROR_NONE)
            throw new Exception_Json(NULL, json_last_error());

        if ( ! $data)
            return array();
        return $data;
    }

    /**
     * add some item to the meta_data field
     * @param $key - string
     * @param $value - mixed
     * @access public
     * @return void
     */
    public function set_meta_item($key, $value)
    {
        $meta_data = $this->meta_data();
        $meta_data[$key] = $value;
        $this->meta_data_cache[$key] = $value;
        $this->data['meta_data'] = json_encode($meta_data);
    }

    /**
     * returns human readable name of some model
     *
     * @return string
     */
    public function representative_name()
    {
        return strtolower(str_replace('_', ' ', $this->module_name));
    }
}
