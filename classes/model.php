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

        $kclass_pieces = preg_split('/(?=[A-Z])/', get_called_class());
        $this->db_table = strtolower( end($kclass_pieces));
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
            $kclass->where(array($kclass->primary_key => $arguments));
        }
        if ( Arr::is_array($arguments))
        {
            $kclass->where($arguments);
        }

        $result = $kclass->exec();
        if ( ! $result->valid())
            throw new Kohana_Exception('record_not_found');
        return $kclass;
    }

    public function where($arguments)
    {
        if ( ! Arr::is_array($arguments))
                throw new Kohana_Exception('must be an array');

        if (Arr::is_assoc($arguments))
        {
            $this->db_query->where_open();
            foreach($arguments as $key => $value)
            {
                $this->db_query->where($key, '=', $value);
            }
            $this->db_query->where_close();
        }
    }

    public function select($select_args = '*')
    {

        $this->db_query = DB::select(!Arr::is_array($select_args)?$select_args:extract($select_args))
                ->from($this->db_table);
        return $this->db_query;
    }

    public function insert($fields = NULL)
    {

        $this->db_query = DB::insert($this->db_table, $fields);
        return $this->db_query;
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
        if ( $result->count() > 0)
            return TRUE;
        return FALSE;
    }

    protected function before_exec()
    {

        switch ($this->query_type)
        {
            case 'insert':
            case 'update':
                $properties  =$this->get_private_properties ($this->db_query);
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

    private function get_private_properties($obj)
    {
        $props = array();
        $reflecionObject = new ReflectionObject($obj);
        foreach ($reflecionObject->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED) as $propiedad)
        {
            $propiedad->setAccessible(true);
            $props[$propiedad->getName()] = $propiedad->getValue($obj);
        }
        return $props;
    }

    public function exec($is_assoc = TRUE)
    {
        if ( ! $this->db_query)
                return;

        $kclass_pieces = preg_split('/(?=[A-Z])/', get_class($this->db_query));
        $this->query_type = strtolower( end($kclass_pieces));

        $this->before_exec();

        if ( $is_assoc)
            $this->db_query->as_assoc();
        else
            $this->db_query->as_object();

        $result = $this->db_query->execute();


        return $this->after_exec($result);
    }

    protected  function after_exec($result)
    {
        switch ($this->query_type)
        {
            case 'insert':
                $this->last_inserted_id = $result[0];
                $result = TRUE;
                break;
            case 'select':
                if ($result->count() === 1){
                    $item  = $result->current();
                    if ( ! Arr::is_array($item) && ! Arr::is_assoc($item))
                        break;

                    foreach($item as $key => $value) {
                        $this->$key = $value;
                    }
                }
                else {
                    $this->total_count = $result->count();
                    $this->records = $result->as_array();
                }
                break;
            default:
                break;

        }
        return $result;
    }

}