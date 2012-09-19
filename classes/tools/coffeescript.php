<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2 
 * @link https://github.com/pussbb/Kohana-my-base
 * @category tools
 * @subpackage tools
 */

class Tools_CoffeeScript extends Tools {

    /**
     * compiles coffee script if needed
     * @param $file_name
     * @param null $files
     * @throws Kohana_Exception
     * @access private
     */
    public static function build_if_needed($file_name, $files = NULL)
    {
        $source_path = Kohana::$config->load('media.core.coffeescript.source_path');
        $dest_path = Kohana::$config->load('media.core.coffeescript.dest_path');
        $destination = $dest_path.$file_name.'.js';
        $join = '';
        $source = '';
        $compile = FALSE;

        if (Arr::is_array($files)) {
            $join = ' -j '. $file_name.'.js';
            foreach($files as $file) {
                $_source = $source_path.$file.'.coffee';
                if ( ! $compile)
                    $compile = self::need_compile($_source, $destination);
                $source .= ' ' . $_source;
            }
        }
        else {
            $source = $source_path.$file_name.'.coffee';
            $compile = self::need_compile($source, $destination);
        }

        if( ! $compile)
            return;

        $output_dir = pathinfo($destination, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;

        if ( ! file_exists($output_dir) && ! is_dir($output_dir))
        {
            mkdir($output_dir);
            chmod($output_dir, 0777);
        }
        $cmd = 'coffee -l -o '. $output_dir .' '.$join.' -c '.$source.'  2>&1';

        $output = shell_exec($cmd);

        if ( ! $output)
            return;

        throw new Exception_CoffeeScript("coffescript compiler output for $destination \n $output");
    }

}