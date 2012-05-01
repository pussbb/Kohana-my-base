<?php defined('SYSPATH') or die('No direct script access.');

class CoffeeScript
{
    public static function build_if_needed($name)
    {
        $js_name = DOCROOT.'media/js/'.$name.'.js';
        $cs_name = DOCROOT.'coffee_scripts/'.$name.'.coffee';

        if( ! file_exists($cs_name))
            return;
        if (file_exists($js_name) && filemtime($js_name) >= filemtime($cs_name))
            return;

        
        $output_dir = DOCROOT.'media/js/' . pathinfo($name, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;

        if ( ! file_exists($output_dir) && ! is_dir($output_dir))
        {
            mkdir($output_dir);
            chmod($output_dir, 0777);
        }

        $output = shell_exec('coffee -l -o '. $output_dir .' -c '.$cs_name.'  2>&1');

        if ( ! $output)
            return;

        throw new Kohana_Exception(
            __("coffescript_compiler_output_for :file : :output", array(
                ':file' => $js_name,
                ':output' => $output,
            ))
        );
    }
}
