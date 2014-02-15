<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Compile cafeescript into js file
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category tools
 * @subpackage tools
 */

class Tools_Coffeescript extends Tools {

    /**
     * compiles coffee script if needed
     *
     * @param $file_name
     * @param null $files
     * @throws Exception_Tools
     * @return void
     * @access protected
     */
    protected function build_if_needed($file_name, $files = NULL)
    {
        $source_path = $this->config('coffeescript.source_path');
        $dest_path = $this->config('coffeescript.dest_path');
        $destination = $dest_path.$file_name.'.js';
        $join = '';
        $source = '';
        $compile = FALSE;

        if (Arr::is_array($files)) {
            $join = ' -j '. $file_name.'.js';
            foreach($files as $file) {
                $_source = Kohana::find_file($source_path, $file, 'coffee');
                if (is_link($_source))
                  $_source = readlink($_source);

                if (self::need_compile($_source, $destination))
                    $compile = TRUE;
                $source .= ' ' . $_source;
            }
        }
        else {
            $source = Kohana::find_file($source_path, $file_name, 'coffee');
            if (is_link($source))
              $source = readlink($source);
            $compile = self::need_compile($source, $destination);
        }

        if( ! $compile)
            return;

        $output_dir = pathinfo($destination, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        Dir::create_if_need($output_dir);

        $cmd = 'coffee -m -o '. $output_dir .' '.$join.' -c '.$source;

        $this->exec($cmd);
    }

    /**
     * checks if coffeescript compiler is installed
     *
     * @static
     * @throw Exception_Tools
     */
    public static function check()
    {
        parent::check();
        if ( ! self::app_exists('coffee -v', '/CoffeeScript version \d\.\d\.\d/'))
            throw new Exception_Tools_Missing('Coffee script compiler not installed. Please visit http://coffeescript.org');
    }

}
