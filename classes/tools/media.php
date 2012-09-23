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

class Tools_Media extends Tools {


/*
$files = Dir::files(DOCROOT.'media/js', 'js');
foreach($files as $file) {
    Tools_Media::jsmin($file, 'min');
}

*/
    protected function jsmin($file, $prefix = NULL)
    {
        if ($prefix && preg_match('/\.'.$prefix.'\.js/', $file))
            return;
        $ok = $this->exec(self::config('eightpack.jsmin')." $file");
        if ( ! $ok)
            throw new Exception_Tools($this->stdout);
        Tools::writable($file);
        file_put_contents(dirname($file).DIRECTORY_SEPARATOR.basename($file, '.js').".$prefix.js", $this->stdout);
    }


    public static function  check()
    {
    }
}