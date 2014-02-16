<?php defined('SYSPATH') OR die('No direct access allowed.');

$columns = Database::instance()->list_columns($table);

$labels = "array(";
$ident = $columns ? str_repeat(' ', 12) : '';
$ident_end = str_repeat(' ', 8);

foreach(array_keys($columns) as $column) {
    $labels .= "\n$ident'$column' => tr('$column'),";
}
$labels .= "\n$ident_end)";
//print_r($columns);

$relations = "array(\n"."\n$ident_end)";

$rules = "array(";

foreach($columns as $name => $data) {
    if (preg_match('/auto_increment/i', Arr::get($data, 'extra', ''), $_val))
        continue;
    $_rules = array();
    if (Arr::get($data, 'is_nullable'))
        $_rules[] = "\n$ident    'not_empty',";
    switch ($data['type']) {
        case 'int':
            $_rules[] = "\n$ident    'decimal',";
            break;
        case 'string':
            if (isset($data['character_maximum_length']))
                $max = $data['character_maximum_length'];
                $_rules[] = "\n$ident    array('max_length', array(\$this->$name, $max)),";
            break;
        default:
            break;
    }
    if ( ! $_rules )
        continue;
    $rules_str = implode($_rules);
    $rules .= "\n$ident'$name' => array($rules_str\n$ident),";
}

$rules .= "\n$ident_end)";


echo "<?php defined('SYSPATH') OR die('No direct access allowed.');

class $model_name extends Base_Model {

    protected \$table_name = '$table';

    public function relations()
    {
        return $relations;
    }

    public function rules()
    {
        return $rules;
    }

    public function labels()
    {
        return $labels;
    }
}

";
