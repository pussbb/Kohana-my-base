<?php defined('SYSPATH') or die('No direct script access.');

class Model extends Kohana_Model
{

    private $data = array();
    protected $primary_key = 'id';
    protected $db_table = NULL;
    protected $db_query = NULL;
    protected $query_type = NULL;
    protected $query_str = NULL;
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
        $kclass_pieces = explode('_', get_called_class());
        unset($kclass_pieces[0]);
        $this->db_table = strtolower( implode('_',$kclass_pieces));
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data))
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


    public static function find($arguments)
    {
        $kclass_name = get_called_class();
        $kclass = new $kclass_name();
        $kclass->select()->limit(1);
        if ( is_numeric($arguments))
        {
            $kclass->filter(array($kclass->primary_key => $arguments));
        }
        if ( Arr::is_array($arguments))
        {
            $kclass->filter($arguments);
        }

        $result = $kclass->save();
        if ( ! $result->valid())
            throw new Kohana_Exception('record_not_found');
        return $kclass;
    }

    public static function find_all($arguments)
    {
        if ( ! $arguments || ! Arr::is_array($arguments) || ! Arr::is_assoc($arguments))
            throw new Exception('must be assoc array');
 
        $kclass_name = get_called_class();
        $kclass = new $kclass_name();
        $kclass->select();
        $result = $kclass->filter($arguments)->save();
        return $kclass;
    }

    public function filter($arguments)
    {
        if ( ! Arr::is_array($arguments))
                throw new Kohana_Exception('must be an array');

        if ( ! Arr::is_assoc($arguments))
        {
            $fields = array();
            foreach($arguments as $field)
            {
                $fields[$field] = $this->$field;
            }
            $arguments = $fields;
        }

        if ( ! array_filter($arguments))
            return $this;

        $this->db_query->where_open();
        foreach($arguments as $key => $value)
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

    
    public function select($select_args = '*')
    {
        $select_args = !Arr::is_array($select_args) ? $select_args : extract($select_args);
        $this->db_query = DB::select()->from($this->db_table);
        return $this->db_query;
    }

    public function insert($fields = NULL)
    {
        $this->db_query = DB::insert($this->db_table, $fields);
        return $this->db_query;
    }

    public function update()
    {
        $this->db_query = DB::insert($this->db_table);
        return $this->db_query;
    }

    public function destroy($filter = NULL)
    {
        $this->db_query = DB::delete($this->db_table);
        return $this->filter(array($this->primary_key))->save();
    }

    public static function destroy_where($filter = NULL)
    {
        $kclass = get_called_class();
        $records = $kclass::find_all($filter)->records;
        if ( ! $records)
            return;
        foreach($records as $record) {
            $record->delete();
        }
    }

    public function errors()
    {
        return $this->errors;
    }

    public function add_error($key, $msg)
    {
        $this->errors[$key] = $msg;
    }

    public function exists($where)
    {
        $query = DB::select()->from($this->db_table);
        $fields = $where;
        if ( ! Arr::is_assoc($where))
        {
            $fields = array();
            foreach($where as $field)
            {
                $fields[$field] = $this->$field;
            }
        }
        $query->where_open();
        foreach($fields as $key => $value)
        {
           $query->where($key, '=', $value);
        }
        $query->where_close();
        $result = $query->as_assoc()->execute();
        return $result->count() > 0;
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
        switch ($this->query_type)
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

    public function save($is_assoc = TRUE)
    {
        if ( ! $this->db_query)
                return;

        $kclass_pieces = preg_split('/(?=[A-Z])/', get_class($this->db_query));
        $this->query_type = strtolower(end($kclass_pieces));

        $this->prepare_for_query();
        $this->before_save();

        if ( $is_assoc)
            $this->db_query->as_assoc();
        else
            $this->db_query->as_object();

        $result = $this->db_query->execute();
        $this->last_query = (string) $this->db_query;
        if ( $this->auto_clean)
            $this->clean();

        return $this->parse_responce($result);
    }

    private function parse_responce($result)
    {
        switch ($this->query_type)
        {
            case 'insert':
                $this->last_inserted_id = $result[0];
                $result = TRUE;
                break;
            case 'select':
                $this->total_count = $result->count();
                $this->records = $result->as_array();
                if ($result->count() === 1){
                    $item  = $result->current();
                    if ( ! Arr::is_array($item) && ! Arr::is_assoc($item))
                        break;
                    foreach($item as $key => $value) {
                        $this->$key = $value;
                    }
                }
                break;
            case 'delete':
            case 'update':
                return $result > 0;
            default:
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