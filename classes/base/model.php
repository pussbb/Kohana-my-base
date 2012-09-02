<?php defined('SYSPATH') or die('No direct script access.');

class Base_Model extends Kohana_Model
{

    public $records = array();
    public $per_page = NULL;
    public $count = NULL;

    protected $order = array();
    protected $primary_key = 'id';
    protected $db_table = NULL;
    protected $db_query = NULL;
    protected $last_inserted_id = NULL;
    protected $validate = TRUE;
    protected $auto_clean = TRUE;

    private $bare_table_name = NULL;
    private $errors = array();
    private $last_query = NULL;
    private $data = array();
    private $system_filters = array(
        'limit', //limit of rows
        'offset', //offset ...
        'with', // query with join of known relation
        'total_count', // for select will added total_count to count all rows if limit set
    );

    private $db_query_count = NULL;

    const BELONGS_TO = 1;
    const HAS_MANY = 2;
    const HAS_ONE = 3;

    public function __construct($params = NULL)
    {
        if (Arr::is_array($params))
        {
            $this->data = $params;
        }
        if (is_numeric($params))
        {
            $this->data[$this->primary_key] = $params;
        }
        $this->db_table = self::db_table_name();
        $this->bare_table_name = self::db_table_name('_', FALSE);
    }

    public function __destruct()
    {
        $this->clean();
        $this->data = NULL;
        $this->records = NULL;
    }

    public static function module_name($glue = '')
    {
        $kclass_pieces = explode('_', get_called_class());
        unset($kclass_pieces[0]);
        return implode($glue, $kclass_pieces);
    }

    public static  function db_table_name($glue = '_', $plural = TRUE)
    {
        $table_name = strtolower(self::module_name($glue));
        if ( ! $plural)
            return $table_name;
        return Inflector::plural($table_name);
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($this->$name))
            return $this->data[$name];
        else
            throw new Kohana_Exception('property_not_exists_' . $name);
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    public function __call($name, $arguments) {
        if ( method_exists($this, $name))
            return;
        if ( method_exists($this->db_query, $name))
            return  call_user_func_array($this->db_query->$name,  $arguments);
    }

    public static function __callStatic($name, $arguments) {
        $kclass_name = get_called_class();
        switch($name) {
            case 'destroy':
                $kclass = new $kclass_name;
                return $kclass->destroy($arguments[0]);
                break;
            case 'exists':
                $kclass = new $kclass_name;
                return $kclass->exists(Arr::get($arguments, 0), Arr::get($arguments, 1), Arr::get($arguments, 2));
                break;
            default:
                break;
        }
    }

    public function __toString()
    {
        $string = (string)$this->db_query;
        if ($string)
            return $string;
        return $this->last_query;
    }

    public function __toArray()
    {
        return $this->data;
    }

    public static function find($filter, $cache = NULL)
    {
        $kclass_name = get_called_class();
        $kclass = new $kclass_name;
        if (is_numeric($filter)) {
            $filter = array($kclass->primary_key => $filter);
        }
        $result = $kclass::find_all($filter, 1, NULL, $cache);
        if ( ! isset($result->{$kclass->primary_key}))
            throw new Exception('record_not_found', 10);
        $result->records = array();
        $result->count = 1;
        return $result;
    }

    public static function find_all($filter = array(), $limit = NULL, $offset = NULL, $cache = NULL)
    {
        $kclass_name = get_called_class();
        $kclass = new $kclass_name();
        $kclass->select('*', $limit, $offset, $cache);
        $kclass->filter($filter)->exec();
        return $kclass;
    }

    public static function table_columns($table = NULL)
    {
        if ( ! $table)
        {
            $kclass_name = get_called_class();
            $table = $kclass_name::db_table_name();
        }
        $columns = Kohana::cache($table.'_columns');
        if ( ! $columns ) {
            $columns = Database::instance()->list_columns($table);
            foreach($columns as $key => $values) {
                if (Arr::get($values, 'character_maximum_length'))
                    $columns[$key]['max'] = $values['character_maximum_length'];
            }
            Kohana::cache($table.'_columns', $columns , 3600 * 24 * 30);
        }
        return $columns;
    }

    private function system_filters($key, $value)
    {
        switch ($key) {
            case 'limit':
                $this->db_query->limit((int)$value);
                break;
            case 'offset':
                $this->db_query->offset((int)$value);
                break;
            case 'total_count':
                //$this->db_query->select(array(DB::expr('COUNT(*)'), 'total_count'));
                break;
            case 'with':
                break;
            default:
                # code...
                break;
        }
    }

    public function filter($filter)
    {
        if ( ! Arr::is_array($filter))
                throw new Kohana_Exception('must be an array');

        $table_columns = $this->table_columns();
        if ( ! Arr::is_assoc($filter))
        {
            $fields = array();
            foreach($filter as $field)
            {
                if ( ! array_key_exists($filed, $table_columns))
                    continue;
                $fields[$field] = $this->$field;
            }
            $filter = $fields;
        }
        else
        {
            //skip fields that are not in table
            //and if it's a system append them
            foreach ($filter as $key => $value) {
                if ( array_key_exists($key, $table_columns))
                    continue;
                if ( in_array($key, $this->system_filters))
                    $this->system_filters($key, $filter[$key]);
                unset($filter[$key]);
            }
        }

        if ( ! array_filter($filter))
            return $this;

        $this->db_query->where_open();
        foreach($filter as $key => $value) {
           $comparison_key = '=';
           if (in_array($key, $this->system_filters))
           {
                $this->system_filters($key, $value);
                continue;
           }
           if ( Arr::is_array($value)) {
               if (! $value)
                   continue;
               $comparison_key = 'IN';
           }
           if (is_object($value)) {
                if(get_class($value) != 'Database_Expression')
                    throw new Exception("Error Processing Request", 1);
                ///if (preg_match('/REGEXP/', $value->value()))
                $comparison_key = '';   
           }
           $this->db_query->where($key, $comparison_key, $this->sanitize($key, $value));
        }
        $this->db_query->where_close();
        return $this;
    }

    public function select($select_args = '*', $limit = NULL, $offset = NULL, $cache = NULL)
    {
        if ( ! Arr::is_array($select_args)) {
            $this->db_query = DB::select($this->bare_table_name.'.'.$select_args);
        }
        else
        {
            $fields = array();
            if (Arr::is_array(Arr::get($select_args, 0))) {
                foreach ($select_args as $item) {
                    $fields[] = array(
                        $this->bare_table_name.'.'.Arr::get($item, 0),
                        Arr::get($item, 1)
                    );
                }
                $this->db_query = call_user_func_array(array('DB', 'select'), $fields);
            }
            else {
                $fields = array(
                    $this->bare_table_name.'.'.Arr::get($select_args, 0),
                    Arr::get($select_args, 1)
                );
                $this->db_query = DB::select($fields);
            }
        }

        $this->db_query->from(array($this->db_table, $this->bare_table_name));
        $this->db_query->limit($limit)->offset($offset);
        if ($cache)
            $this->db_query->cached($cache);
        return $this;
    }

    public function insert($fields = NULL)
    {
        $this->db_query = DB::insert($this->db_table, $fields);
        return $this;
    }

    public function update()
    {
        $this->db_query = DB::update($this->db_table)->where($this->primary_key, '=',$this->{$this->primary_key});
        return $this;
    }

    protected function destroy($filter = NULL)
    {
        $this->db_query = DB::delete($this->db_table);
        if ( ! $filter )
            $filter = array($this->primary_key);
        return $this->filter($filter)->save();
    }

    public function errors()
    {
        return $this->errors;
    }

    public function add_error($key, $msg)
    {
        $this->errors[$key] = $msg;
    }

    protected function exists($filter = NULL, $limit = 1, $cache = NULL)
    {
        $this->select('*', $limit, NULL, $cache);
        if ( ! $filter )
            $filter = array($this->primary_key);

        return $this->filter($filter)->exec();
    }

    private function get_private_properties($obj)
    {
        $properties = array();
        $reflecionObject = new ReflectionObject($obj);
        $object_properties = $reflecionObject->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED);
        foreach ($object_properties as $property)
        {
            $property->setAccessible(true);
            $properties[$property->getName()] = $property->getValue($obj);
        }
        return $properties;
    }

    private function prepare_for_query()
    {
        switch ($this->query_type())
        {
            case 'insert':
            case 'update':
                $properties = $this->get_private_properties($this->db_query);
                $columns = Arr::get($properties, '_columns');
                $values = Arr::get($properties, '_values');
                if ( $columns && ! $values)
                {
                    $data = array();
                    foreach($columns as $field)
                    {
                        $data[] = $this->sanitize($field, $value);
                    }
                    $this->db_query->values($data);
                }
                break;
            default:
                break;
        }
    }

    private function sanitize($key, $value)
    {
        if (is_object($value))
             return $value;
        $table_columns = $this->get_table_columns();
        $type = Arr::path($table_columns, $key.'.type');
        if ($type)
            return Base_Db_Sanitize::value($type, $value);
        return $value;     
    }
    public function validate(array $rules = NULL,array $data = NULL)
    {
        $data = $data?$data:$this->data;
        $rules = $rules?$rules:$this->rules();
        $validator = Validation::factory($data);
        foreach ($rules as $key => $rules)
        {
            foreach ($rules as $rule)
            {
                if ($rule === 'unique')
                    $rule = array(array($this, 'unique_validation'), array(':validation', ':field'));

                if ( ! is_array($rule)) {
                    $validator->rule($key, $rule);
                    continue;
                }
                $validator->rule(
                    $key,
                    Arr::get($rule, 0, NULL),
                    Arr::get($rule, 1, NULL)
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

    protected function before_save()
    {
        //user manipulations
    }

    public function new_record()
    {
        return ! isset($this->{$this->primary_key});
    }

    private function table_fields()
    {
        $table_columns = $this->get_table_columns();
        foreach($this->data as $key => $value) {
            if ( ! array_key_exists($key, $table_columns))
                continue;
            $keys[] = $key;
        }
        return array_filter($keys);
    }

    public function save()
    {
        if ( ! $this->query_type())
        {
            if ( $this->new_record())
                $this->insert($this->table_fields());
            else
                $this->update($this->table_fields());
        }

        if ( ! Base_Db_Validation::check($this) || ! $this->validate())
            return FALSE;

        $this->before_save();
        $this->prepare_for_query();

        $responce = $this->exec();
        $this->after_save();
        return $responce;
    }

    public function get_table_columns()
    {
        return $this->columns()?:self::table_columns();
    }

    private function query_type()
    {
        $kclass_pieces = preg_split('/(?=[A-Z])/', get_class($this->db_query));
        return strtolower(end($kclass_pieces));
    }

    protected function exec()
    {
        $this->db_query->as_assoc();
        if ($this->order)
            call_user_func_array(array($this->db_query, 'order_by'), $this->order);
        $result = $this->db_query->execute();
        $this->last_query = (string) $this->db_query;
        $responce = $this->parse_responce($result);
        if ( $this->auto_clean)
            $this->clean();
        return $responce;
    }

    private function parse_responce($result)
    {
        switch ($this->query_type())
        {
            case 'insert':
                $this->last_inserted_id = $result[0];
                $result = TRUE;
                break;
            case 'select':
                if ($result->count() == 1) {
                    $this->update_params($result->current());
                }
                $kclass = get_called_class();
                $this->count = $result->count();
                foreach($result->as_array() as $record) {
                    if ( ! Arr::is_array($record) && ! Arr::is_assoc($record))
                        break;
                    $this->records[] = new $kclass($record);
                }
                $result = $result->count() > 0;
                break;
            case 'delete':
            case 'update':
                $result = $result > 0;
                break;
            default:
                $result = TRUE;
                break;
        }
        return $result;
    }

    protected function after_save()
    {
        //user manipulations
    }

    private function clean()
    {
        $this->db_query = NULL;
        $this->errors = array();
    }

    public function update_params($array)
    {
        foreach($array as $key => $value)
        {
            $this->$key = $value;
        }
    }

    public function columns()
    {
        return array();
    }

    public function rules()
    {
        return array();
    }

    public function labels()
    {
      return array();
    }

    public function last_inserted_id()
    {
        return $this->last_inserted_id;
    }

    public function last_query()
    {
        return $this->last_query;
    }

    public function unique_validation($validation, $field)
    {
        $kclass = clone $this;
        if ($kclass->exists(array($field))){
            $validation->error($field, ' '.__("already exists"));
            return;
        }
    }
}