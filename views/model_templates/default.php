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

$rules = "array(\n"."\n$ident_end)";

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
