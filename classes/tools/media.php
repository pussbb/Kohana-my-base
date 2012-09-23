<?php defined('SYSPATH') or die('No direct script access.');

/**
 * http://ariya.ofilabs.com/2011/10/javascript-tools-for-continuous-integration.html
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
        $dest_file = dirname($file).DIRECTORY_SEPARATOR.basename($file, '.js').".$prefix.js";
        $this->eightpack_exec('jsmin', $file, $dest_file);
    }
/*
$files = Dir::files(DOCROOT.'media/css', 'css');
foreach($files as $file) {
    Tools_Media::cssmin($file, 'min');
}

*/
    protected function cssmin($file, $prefix = NULL)
    {
        if ($prefix && preg_match('/\.'.$prefix.'\.js/', $file))
            return;
        $dest_file = dirname($file).DIRECTORY_SEPARATOR.basename($file, '.css').".$prefix.css";
        $this->eightpack_exec('cssmin', $file, $dest_file);
    }

    private function eightpack_exec($app, $source_file, $dest_file = NULL)
    {
      $ok = $this->exec(self::config("eightpack.$app")." $source_file");
      if ( ! $ok)
          throw new Exception_Tools($this->error());
      $file = $dest_file?:$source_file;
      file_put_contents($file, $this->stdout);
    }

    protected function css_beautify($file, $prefix = NULL)
    {
        if ($prefix && preg_match('/\.'.$prefix.'\.css/', $file))
            return;
        $this->eightpack_exec('cssbeautify', $file);
    }

    protected function js_beautify($file, $prefix = NULL)
    {
        if ($prefix && preg_match('/\.'.$prefix.'\.js/', $file))
            return;
        $this->eightpack_exec('jsbeautify', $file);
    }
    public static function  check()
    {
    }
}