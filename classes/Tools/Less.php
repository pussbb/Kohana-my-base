<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class to compile less file into css file
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category tools
 * @subpackage tools
 */

class Tools_Less extends Tools {

    /**
     * compiles less file if needed
     * @param $file_name string
     * @throws Exception_Tools
     * @access private
     */
    protected function build_if_needed($file_name)
    {
        $source_path = $this->config('less.source_path');
        $dest_path = $this->config('less.dest_path');
        $destination = $dest_path.$file_name.'.css';
        $source = Kohana::find_file($source_path, $file_name, 'less');

        if ( ! self::need_compile($source, $destination))
            return;
        $output_dir = pathinfo($destination, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        Dir::create_if_need($output_dir);

        $cmd = 'lessc '.$source;

        try {
            $this->exec($cmd);
        } catch (Exception_Tools_Output $e) {}


        if( ! is_writable(dirname($destination)))
            throw new Exception_Tools("You don't have permission to write in  $destination");
        file_put_contents($destination, $this->stdout);

    }

    /**
     * checks if less compiler is installed
     * @static
     * @throw Exception_Tools
     */
    public static function check()
    {
        parent::check();
        if ( ! self::app_exists('lessc --v', '/lessc \d{0,}\.\d{0,}.\d{0,}/'))
            throw new Exception_Tools_Missing('Less compiler not installed. Please visit http://lesscss.org/');
    }


}
