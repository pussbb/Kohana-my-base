<?php defined('SYSPATH') or die('No direct script access.');

class Model extends Kohana_Model
{
    private $data = array();
    protected $primary_key = 'id';
    protected $db_table = NULL;
    protected $db_query = NULL;
    protected $last_inserted_id = NULL;
    private $errors = NULL;
    protected $auto_clean = TRUE;
    public $last_query = NULL;

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
    }


    public static function module_name($glue = '')
    {
        $kclass_pieces = explode('_', get_called_class());
        unset($kclass_pieces[0]);
        return implode($glue, $kclass_pieces);
    }

    public static  function db_table_name($glue = '_')
    {
        return strtolower(self::module_name($glue));
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
            throw new Kohana_Exception('property not exists' . $name);
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    public function __call($name, $arguments) {
        if ( method_exists($this, $name))
            return;//call_user_func_array($this, $name, $arguments);
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
        unset($result->records);
        unset($result->count);
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

    public function filter($filter)
    {
        if ( ! Arr::is_array($filter))
                throw new Kohana_Exception('must be an array');

        if ( ! Arr::is_assoc($filter))
        {
            $fields = array();
            foreach($filter as $field)
            {
                $fields[$field] = $this->$field;
            }
            $filter = $fields;
        }

        if ( ! array_filter($filter))
            return $this;

        $this->db_query->where_open();
        foreach($filter as $key => $value)
        {
           $comparison_key = '=';
           if ( Arr::is_array($value))
           {
               if (! $value)
                   continue;
               $comparison_key = 'IN';
           }
           $this->db_query->where($key, $comparison_key, $value);
        }
        $this->db_query->where_close();
        return $this;
    }

    public function select($select_args = '*', $limit = NULL, $offset = NULL, $cache = NULL)
    {
        $select_args = !Arr::is_array($select_args) ? $select_args : extract($select_args);
        $this->db_query = DB::select()->from($this->db_table);
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
        $this->db_query = DB::insert($this->db_table);
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

    protected function exists($filter = NULL, $limit = NULL, $cache = NULL)
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
                        $data[] = $this->$field;
                    }
                    $this->db_query->values($data);
                }
                break;
            default:
                break;
        }
    }

    protected function before_save()
    {
        //user manipulations
    }

    public function save()
    {
        if ( ! $this->db_query)
                return;
        $this->prepare_for_query();
        $this->before_save();
        $responce = $this->exec();
        $this->after_save();
        return $responce;
    }

    private function query_type()
    {
        $kclass_pieces = preg_split('/(?=[A-Z])/', get_class($this->db_query));
        return strtolower(end($kclass_pieces));
    }

    protected function exec()
    {
        $this->db_query->as_assoc();
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
                    ///break;
                }
                $kclass = get_called_class();
                $this->total_count = $result->count();
                $records = array();
                foreach($result->as_array() as $record) {
                    if ( ! Arr::is_array($record) && ! Arr::is_assoc($record))
                        break;
                    $records[] = new $kclass($record);
                }
                $this->records = $records;
                if ($result->count() == 0 ) {
                    $result = FALSE;
                }
                else {
                    $result = TRUE;
                }
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
    }

    public function update_params($array)
    {
        foreach($array as $key => $value)
        {
            $this->$key = $value;
        }
    }
}