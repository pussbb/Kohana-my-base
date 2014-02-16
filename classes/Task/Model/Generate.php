<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Task to generate model from database tables
 * @package Kohana-my-base
 * @copyright 2014 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category Minion
 * @subpackage Model
 * @see  Base_ACL
 */

class Task_Model_Generate  extends Minion_Task {

    private function directories() {
        $dirs = Kohana::include_paths();
        $skip_dirs = array(
            DOCROOT,
            MODPATH.'database'.DIRECTORY_SEPARATOR,
            MODPATH.'minion'.DIRECTORY_SEPARATOR,
            MODPATH.'orm'.DIRECTORY_SEPARATOR,
            MODPATH.'auth'.DIRECTORY_SEPARATOR,
            SYSPATH,
        );

        $result= array();
        foreach($dirs as $dir) {
            if (in_array($dir, $skip_dirs))
                continue;
            $result[] = $dir;
        }
        return $result;
    }

    /**
     * This is a demo task
     *
     * @return null
     */
    protected function _execute(array $params)
    {
        $dirs = $this->directories();
        $tables = Database::instance()->list_tables();
        foreach($tables as $key => $table) {
            $singular = Inflector::singular($table, 1);
            $model_name = Helper_Model::class_name($singular);
            $file = implode(DIRECTORY_SEPARATOR, explode('_', $model_name));

            if (Kohana::find_file('classes', $file))
                continue;

            Minion_CLI::write("Found new database table '$table'");
            $ready = Minion_CLI::read("Do you whant to create model '$model_name'", array('y', 'n'));
            if ($ready==='n')
                continue;

            $view = View::factory('model_templates/default', array(
                'model_name' => $model_name,
                'table' => $table,
            ));

            $ready = Minion_CLI::read("View file content ", array('y', 'n'));
            if ($ready==='y')
                Minion_CLI::write("\n".$view->render());

            Minion_CLI::write("Choose folder where file should be saved:");

            $choice = array();
            foreach($dirs as $key => $dir) {
                $key += 1;
                $choice[] = $key;
                Minion_CLI::write(" [ $key ] - $dir");
            }

            $ready = Minion_CLI::read("Please make your choice", $choice);
            $dir = Arr::get($dirs, intval($ready) - 1, 0);

            $file = $dir.'classes'.DIRECTORY_SEPARATOR.$file.EXT;
            Minion_CLI::write("Creating file $file");
            Dir::create_if_need(File::dirname($file));

            file_put_contents($file, $view->render());
        }
    }

}
