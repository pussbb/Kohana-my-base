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

class Tools_Less extends Tools {

    /**
     * compiles coffee script if needed
     * @param $file_name
     * @param null $files
     * @throws Kohana_Exception
     * @access private
     */
    public static  function build_if_needed($file_name)
    {
        self::check();
        $source_path = self::config('less.source_path');
        $dest_path = self::config('less.dest_path');
        $destination = $dest_path.$file_name.'.css';
        $source = $source_path.$file_name.'.less';

        if ( ! self::need_compile($source, $destination))
            return;
        $output_dir = pathinfo($destination, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        Dir::create_if_need($output_dir);

        $cmd = 'lessc '.$source;

        $proc = proc_open(
            $cmd,
            array(
                array('pipe','r'),
                array('pipe','w'),
                array('pipe','w')
            ),
            $pipes,
            NULL
        );
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        if ( proc_close($proc) == 0)
        {
            file_put_contents($destination, $stdout);
            return;
        }

        throw new Exception_Tools("less compiler output for $destination \n $stderr");
    }

    public static function check()
    {
        if ( ! self::can_call())
           throw new Exception_Tools('Your system does not support to call exec');
        if ( ! self::app_exists('lessc --v', '/lessc \d{0,}\.\d{0,}.\d{0,}/'))
            throw new Exception_Tools('Less compiler not installed. Please visit http://lesscss.org/');
    }


}