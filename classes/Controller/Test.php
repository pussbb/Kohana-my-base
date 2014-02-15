<?php
/**
 * Created by PhpStorm.
 * User: pussbb
 * Date: 2/15/14
 * Time: 10:44 PM
 */

class Controller_Test extends Controller_Core {
    protected $check_access = FALSE;

    public function action_index()
    {
        $db = Database::instance();
        $tables = $db->query(Database::SELECT, 'SHOW TABLES;');
        foreach($tables as $key => $table) {
            $table = array_values($table)[0];
            $singular = Inflector::singular($table, 1);
            $model_name = Helper_Model::class_name($singular);
            $file = implode(DIRECTORY_SEPARATOR, explode('_', $model_name));

            if (Kohana::find_file('classes', $file))
                continue;

            $file = APPPATH.'classes'.DIRECTORY_SEPARATOR.$file.EXT;
            Dir::create_if_need(File::dirname($file));
            $view = View::factory('model_templates/default', array(
                'model_name' => $model_name,
                'table' => $table,
            ));
            file_put_contents($file, $view->render());

        }
        var_dump(Database::instance()->list_columns('news'));
    }
}
