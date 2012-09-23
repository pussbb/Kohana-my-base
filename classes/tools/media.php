<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Execute some external usefull tools (beautify and minize)
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


  /**
  * minize js(files with .min.js will be skiped)
  *
  * <code>
  *  $files = Dir::files(DOCROOT.'media/js', 'js');
  *   foreach($files as $file) {
  *     Tools_Media::jsmin($file, 'min');
  *   }
  * </code>
  * @param $file string file name
  * @param $prefix string prefix for minimized file if null rewrites origin
  * @return void
  */
    protected function jsmin($file, $prefix = 'min')
    {
        if ($prefix && preg_match('/\.'.$prefix.'\.js/', $file))
            return;
        $dest_file = dirname($file).DIRECTORY_SEPARATOR.basename($file, '.js').".$prefix.js";
        $this->eightpack_exec('jsmin', $file, $dest_file);
    }

  /**
  * minize css (files with .min.js will be skiped)
  *
  * <code>
  *  $files = Dir::files(DOCROOT.'media/css', 'css');
  *   foreach($files as $file) {
  *     Tools_Media::cssmin($file, 'min');
  *   }
  * </code>
  * @param $file string file name
  * @param $prefix string prefix for minimized file if null rewrites origin
  * @return void
  */
    protected function cssmin($file, $prefix = 'min')
    {
        if ($prefix && preg_match('/\.'.$prefix.'\.js/', $file))
            return;
        $dest_file = dirname($file).DIRECTORY_SEPARATOR.basename($file, '.css').".$prefix.css";
        $this->eightpack_exec('cssmin', $file, $dest_file);
    }


    /**
    * minize media files css and js (files with .min.js will be skiped)
    *
    * <code>
    *  $files = Dir::files(DOCROOT.'media');
    *   foreach($files as $file) {
    *     Tools_Media::minimize($file, 'min');
    *   }
    * </code>
    * @param $file string file name
    * @param $prefix string prefix for minimized file if null rewrites origin
    * @return void
    */
    protected function minimize($file, $prefix = 'min')
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if ($extension == 'css')
          return $this->cssmin($file, $prefix);
        if ($extension == 'js')
          return $this->jsmin($file, $prefix);
    }

    /**
    * execute console application and write changes to the file
    *
    * @param $file string file name
    * @param $prefix string prefix for minimized file if null rewrites origin
    * @return void
    */
    private function eightpack_exec($app, $source_file, $dest_file = NULL)
    {
      $ok = $this->exec(self::config("eightpack.$app")." $source_file");
      if ( ! $ok)
          throw new Exception_Tools($this->error());
      $file = $dest_file?:$source_file;
      file_put_contents($file, $this->stdout);
    }

    /**
    * beautify media files css and js (files with .min.js will be skiped)
    *
    * <code>
    *  $files = Dir::files(DOCROOT.'media');
    *   foreach($files as $file) {
    *     Tools_Media::beautify($file, 'min');
    *   }
    * </code>
    * @param $file string file name
    * @param $prefix string prefix to skip file (minimized not need to format)
    * @return void
    */
    protected function beautify($file, $prefix = 'min')
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if ($extension == 'css')
          return $this->css_beautify($file, $prefix);
        if ($extension == 'js')
          return $this->js_beautify($file, $prefix);
    }

    /**
    * beautify media files css(files with .min.js will be skiped)
    *
    * <code>
    *  $files = Dir::files(DOCROOT.'media', 'css');
    *   foreach($files as $file) {
    *     Tools_Media::css_beautify($file, 'min');
    *   }
    * </code>
    * @param $file string file name
    * @param $prefix string prefix to skip file (minimized not need to format)
    * @return void
    */
    protected function css_beautify($file, $prefix = 'min')
    {
        if ($prefix && preg_match('/\.'.$prefix.'\.css/', $file))
            return;
        $this->eightpack_exec('cssbeautify', $file);
    }

    /**
    * beautify media files js(files with .min.js will be skiped)
    *
    * <code>
    *  $files = Dir::files(DOCROOT.'media', 'js');
    *   foreach($files as $file) {
    *     Tools_Media::js_beautify($file, 'min');
    *   }
    * </code>
    * @param $file string file name
    * @param $prefix string prefix to skip file (minimized not need to format)
    * @return void
    */
    protected function js_beautify($file, $prefix = 'min')
    {
        if ($prefix && preg_match('/\.'.$prefix.'\.js/', $file))
            return;
        $this->eightpack_exec('jsbeautify', $file);
    }
    /**
     * checks if less compiler is installed
     * @static
     * @throw Exception_Tools
     */
    public static function  check()
    {
    }
}