<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class to add javascripts and CSS to the main template
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2 
 * @link https://github.com/pussbb/Kohana-my-base
 * @category template
 * @subpackage template
 */

class Base_Media extends Singleton{

    /**
     * configuration settings
     * @var array|null
     * @access private
     */
    private $config = NULL;

    /**
     * containce all css files
     * wich need to add to the template
     * @var array
     * @access private
     */
    private $styles = array();

    /**
     * Variable with inline css
     * in template will added like this
     * <code>
     * <style type="type/css">
     *      body {css_rule: value};
     * </style>
     * </code>
     * before tag </head>
     * @var string
     * @access private
     */
    private $inline_style = '';

    /**
     * Variable with inline javascript
     * in template will added like this
     * <code>
     * <script type="text/javascript">
     *      var some_var = value;
     * </script>
     * </code>
     * before tag </head>
     * @var string
     * @access private
     */
    private $inline_script = '';

    /**
     * contain all javascript files which,
     * will be included to the template
     * @var array
     * @access private
     */
    private $scripts = array();

    /**
     * Initialize configuration settings
     * and auto load default bundle
     */
    public function __construct()
    {
        $this->config = Kohana::$config->load('media');
        $this->bundle('default');
    }

    /**
     * get value from config
     * @param $key
     * @return mixed
     * @access private
     */
    private function config($key)
    {
         if (strpos($key, '.') !== FALSE)
            return Arr::path($this->config, $key);
        return Arr::get($this->config, $key);
    }

    /**
     * Append colection of css and js files
     * @param $name
     * @return void
     */
    public function bundle($name)
    {
        $bundle = Arr::get($this->config, $name, array());
        if ( ! $bundle)
            return;
        foreach (Arr::get($bundle, 'css') as $file => $media) {
            $this->append_style($file, $media);
        }
        foreach (Arr::get($bundle, 'js') as $file) {
            $this->append_script($file);
        }
    }

    /**
     * append media type to the list
     * @param $key type css or js
     * @param $name file name without file extension .css or .js
     * @param string|null $media for css files only e.g. 'screen'
     * @param bool $check
     * @access public
     */
    public function append($key, $name, $media = NULL, $check = FALSE)
    {
        if (Arr::is_array($key))
        {
            foreach ($key as $_key) {
                $this->append($_key, $name, $media ,$check);
            }
            return;
        }
        switch($key) {
            case 'css':
            case 'style':
                $this->append_style($name, $media, $check);
                break;
            case 'js':
            case 'script':
                $this->append_script($name, $check);
            default;
                break;
        }
    }

    /**
     * function to find media file
     *
     * example
     * <code>
     * <?php
     *  $file = Media::find_file('jquery', 'js);
     *  // or
     *  $file = Media_Base::instance()->find_file('jquery', 'js);
     * ?>
     * </code>
     * will search file DOCROOT.'media/js/jquery.js'
     * @param $name file name without file extension .css or .js
     * @param $prefix css or js
     * @return string|null full path to the file or null if not found
     * @access public
     */
    public function find_file($name, $prefix)
    {
        $path = $this->config('core.path');
        $file = $path.$prefix.DIRECTORY_SEPARATOR.$name.'.'.$prefix;
        if (file_exists($file))
            return $file;
        return Kohana::find_file('media',$name, $prefix);
    }

    /**
     * checks if valid url
     * @param $uri
     * @return bool TRUE if url is valid
     * @access private
     */
    private function is_url($uri)
    {
        return Valid::url($uri);
    }

    /**
     * return full url for media file
     *
     * if appended media already has a valid url (http://....)
     * this functions keep that media
     * if stattic:// was specified at the begining of string
     * function return a url with static url wich must specified in config
     * e.g. 'static://juery' -> 'http://static.local/js/jquery.js/'
     * @param $file_name
     * @param $prefix
     * @return string
     * @access private
     */
    private function resource($file_name, $prefix)
    {
        if ($this->is_url($file_name))
            return $file_name;
        if ( strpos('static://', $file_name) === TRUE)
            return str_replace('static://', $this->config('core.static_uri').$prefix.'/', $file_name.'.'.$prefix);
        return Url::base(TRUE,TRUE).$this->config('core.uri').$prefix.'/'.$file_name.'.'.$prefix;
    }

    /**
     * add css file to the list
     * @param $file_name file name without file extension .css or .js
     * @param string|null $media type for css  e.g. 'screen'
     * @param bool $check if TRUE first check file if its a remote it always will be FALSE
     * @access public
     */
    public function append_style($file_name, $media = NULL, $check = FALSE)
    {
        if ($check && ! $this->find_file($file_name, 'css'))
            return;
        $this->styles[$this->resource($file_name, 'css')]= $media;
    }

    /**
     * adds inline css
     * @param string $css
     * @access public
     */
    public function append_inline_style($css)
    {
        if ( ! $css)
            return;
        $this->inline_style .= $css;
    }

    /**
     * add javasript file to the list
     *
     * also tries to find coffee script file and compile them to js file
     * if needed
     * @param $file_name file name without file extension .css or .js
     * @param bool $check if TRUE first check file if its a remote it always will be FALSE
     * @access public
     */
    public function append_script($file_name, $check = FALSE)
    {
        $files = NULL;
        if (Arr::is_array($file_name)) {
            $files = Arr::get($file_name, 'files');
            $file_name = Arr::get($file_name, 'name');
        }

        if ( Kohana::$environment != Kohana::PRODUCTION) {
            Tools_CoffeeScript::build_if_needed($file_name, $files);
        }
        if ($check && ! $this->find_file($file_name, 'js'))
            return;
        $this->scripts[]= $this->resource($file_name, 'js');
    }

    /**
     * add inline javascript
     * @param string $js
     * @access public
     */
    public function append_inline_script($js)
    {
        if ( ! $js)
            return;
        $this->inline_script .= $js;
    }

    /**
     * get all appended css files
     * @return array
     * @access public
     */
    public function styles()
    {
        return $this->styles;
    }

    /**
     * return formatted string for inline style
     *
     * all included inline styles already wrapped in tag <style>
     * @return string
     * @access public
     */
    public function inline_style()
    {
        if ( ! $this->inline_style)
            return;
        return "\n<style type=\"text/css\">\n$this->inline_style\n</style>\n";
    }

    /**
     * return a list with included javascript files
     * @return array
     */
    public function scripts()
    {
        return array_unique($this->scripts);
    }

    /**
     * return formatted string for inline javascript
     *
     * all included inline script already wrapped in tag <script>
     * @return string
     * @access public
     */
    public function inline_script()
    {
        if ( ! $this->inline_script)
            return;
        return "\n<script type=\"text/javascript\">\n$this->inline_script\n</script>\n";
    }


    /**
     * minize javascript script using JSMin
     * @see  JSMin
     * @param $file
     * @access private
     */
    private function minize_script($file)
    {
        if (Kohana::$environment == Kohana::PRODUCTION)
            return;
        if ( ! $this->config('core.coffeescript.minify'))
            return;
        $jsmin = Kohana::find_file('vendor', 'jsmin-php/jsmin');
        if ( ! $jsmin)
            return;
        include_once $jsmin;
        file_put_contents($file, JSMin::minify(file_get_contents($file)));
    }
}
