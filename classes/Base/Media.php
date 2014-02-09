<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class to add javascripts and CSS to the main template
 *
 * @package Kohana-my-base
 * @copyright 2014 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category template
 * @subpackage template
 */

class Base_Media extends Singleton {

    /**
     * configuration settings
     *
     * @var array|null
     * @access private
     */
    private $config = NULL;

    /**
     * containce all css files
     * wich need to add to the template
     *
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
     *
     * @var string
     * @access private
     */
    private $inline_style = array();

    /**
     * Variable with inline javascript
     * in template will added like this
     * <code>
     * <script type="text/javascript">
     *      var some_var = value;
     * </script>
     * </code>
     * before tag </head>
     *
     * @var string
     * @access private
     */
    private $inline_script = array();

    /**
     * contain all javascript files which,
     * will be included to the template
     * @var array
     * @access private
     */
    private $scripts = array();

    /**
     * @var array
     */
    private static $media_handlers = array(
        'css' => array(),
        'js' => array(),
    );

    /**
     *
     */
    const POSITION_HEAD = 1;
    /**
     *
     */
    const POSITION_FOOTER = 2;

    /**
     * @var array
     */
    private $processed_bundles = array();

    /**
     * @var array
     */
    public static $known_media_types = array('css', 'js', 'coffee', 'less');

    /**
     * @var array
     */
    private $js_templates = array();

    /**
     * Initialize configuration settings
     * and auto load default bundle
     *
     * @ignore
     */
    public function __construct()
    {
        $this->config = Kohana::$config->load('media')->as_array();
        $this->bundle('default');
    }

    /**
     * get value from config
     *
     * @param $key
     * @return mixed
     * @access private
     */
    private function config($key, $default = NULL)
    {
        $parts = explode('.', $key);
        $data = $this->config;
        foreach($parts as $part) {
            $data = isset($data[$part]) ? $data[$part] : $default;
            if ( ! is_array($data))
                return $data;
        }
        return $data;
    }

    /**
     * @param $type
     * @param $func
     */
    public static function register_media_handler($type, $func)
    {
        Base_Media::$media_handlers[$type][] = $func;
    }

    /**
     * Append collection of css and js files
     *
     * @param $name
     * @return void
     */
    public function bundle($name)
    {
        $bundle = Arr::get($this->config, $name);
        if ( ! $bundle )
            return;

        if (($depends = Arr::get($bundle, 'depends')))
        {
            unset($bundle['depends']);
            $depends = is_array($depends) ? $depends : explode(',' ,$depends);
            foreach(array_map('trim', $depends) as $dependency)
            {
                if (in_array($dependency, $this->processed_bundles))
                    continue;
                $this->bundle($dependency);
                $this->processed_bundles[] = $dependency;
            }
        }
        $this->processed_bundles[] = $name;
        foreach($bundle as $key => $items) {
            foreach($items as $name => $data) {
                if (is_numeric($name)) {
                    $name = $data;
                    $data = NULL;
                }
                $this->append($key, $name, $data);
            }
        }

    }

    /**
     * append media type to the list
     *
     * @param $key type css or js
     * @param $name file name without file extension .css or .js
     * @param string|null $media for css files only e.g. 'screen'
     * @param bool $check
     * @access public
     */
    public function append($key, $name, $data = NULL, $check = FALSE, $position = NULL)
    {
        if (is_array($key))
        {
            foreach ($key as $_key) {
                $this->append($_key, $name, NULL ,$check, $position);
            }
            return;
        }
        switch($key) {
            case 'css':
            case 'style':
                $this->append_style($name, $data, $check, $position);
                break;
            case 'js':
            case 'script':
                $this->append_script($name, $data, $check, $position);
                break;
            case 'coffee':
            case 'coffeescript':
                $this->append_coffee_script($name, $data, $check, $position);
                break;
            case 'less':
            case 'lesscss':
                $this->append_less($name, $data, $check, $position);
                break;
            default:
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
     *
     * @param $name string file name without file extension .css or .js
     * @param $prefix  string css or js
     * @return string|null full path to the file or null if not found
     * @access public
     */
    public function find_file($name, $prefix)
    {
        $path = $this->config('core.path');
        $file = Text::reduce_slashes($path.$prefix.DIRECTORY_SEPARATOR.$name.'.'.$prefix);
        if (file_exists($file))
            return $file;
        return Kohana::find_file('media',$name, $prefix);
    }

    /**
     * checks if valid url
     *
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
     * function return a url with static url which must specified in config
     * e.g. 'static://juery' -> 'http://static.local/js/jquery.js/'
     *
     * @param $file_name
     * @param $prefix
     * @return string
     * @access private
     */
    private function resource($file_name, $prefix)
    {
        $static = strpos($file_name, 'static://');

        if ($this->is_url($file_name) && $static === FALSE)
            return $file_name;

        $file = $file_name.'.'.$prefix;
        $path = $prefix.'/';

        if ( $static !== FALSE)
            return str_replace('static://', '//'.$this->clean_path($this->config('core.static_uri')).'/'.$path, $file);

        return URL::base(TRUE,TRUE).$this->clean_path($this->config('core.uri').$path.$file);
    }

    /**
     * return full url for media file without // in path
     *
     * @param $file
     * @return string
     * @access private
     */
    public function clean_path($file)
    {
        return Text::reduce_slashes($file);
    }

    /**
     * @param $position
     * @return int
     */
    private function get_position($position){
        if ( ! $position )
            return $this->config('defaut_position', Base_Media::POSITION_HEAD);
        return intval($position);
    }

    /**
     * add css file to the list
     *
     * @param $file_name file name without file extension .css or .js
     * @param string|null $media type for css  e.g. 'screen'
     * @param bool $check if TRUE first check file if its a remote it always will be FALSE
     * @access public
     */
    public function append_style($file_name, $data = NULL, $check = FALSE, $position = NULL, $prefix = 'css')
    {
        foreach(Arr::get(self::$media_handlers, $prefix, array()) as $handler) {
            call_user_func($handler, $file_name, $data);
        }

        if ($check && ! $this->find_file($file_name, $prefix))
            return;

        if (is_string($data)) {
            $data = array(
                'media' => $data,
                'rel' => "stylesheet",
            );
        } elseif (is_array($data)) {
            if (isset($data['files']))
                unset($data['files']);
        }  elseif ( ! $data ) {
            $data = array(
                'type' => 'text/css',
                'rel' => 'stylesheet',
            );
        }

        $this->styles[$this->get_position($position)][$this->resource($file_name, $prefix)] = array_filter($data);

    }

    /**
     * add javascript file to the list
     *
     * also tries to find coffee script file and compile them to js file
     * if needed
     *
     * @param $file_name file name without file extension .css or .js
     * @param bool $check if TRUE first check file if its a remote it always will be FALSE
     * @param null $files
     * @return void
     * @access public
     */
    public function append_script($file_name, $data = NULL,  $check = FALSE, $position = NULL, $prefix = 'js')
    {

        foreach(Arr::get(self::$media_handlers, $prefix, array()) as $handler) {
            call_user_func($handler, $file_name, Arr::get((array)$data, 'files', array()));
        }

        if ($check && ! $this->find_file($file_name, $prefix))
            return;

        if (is_string($data)) {
            $data = array(
                'type' => $data,
            );
        } elseif (is_array($data)) {
            if (isset($data['files']))
                unset($data['files']);
        } elseif ( ! $data ) {
            $data = array(
                'type' => 'text/javascript',
            );
        }
        $this->scripts[$this->get_position($position)][$this->resource($file_name, $prefix)]= $data;
    }

    /**
     * @param $file_name
     * @param null $data
     * @param bool $check
     * @param null $position
     */
    public function append_coffee_script($file_name, $data = NULL,  $check = FALSE, $position = NULL)
    {
        if ( ! $data || ! is_array($data))
            $data =  array( 'type' => 'text/coffeescript',);
        elseif (is_array($data))
            $data['type'] = 'text/coffeescript';
        $this->append_script($file_name, $data, $check, $position, 'coffee');
    }

    /**
     * @param $file_name
     * @param null $data
     * @param bool $check
     * @param null $position
     */
    public function append_less($file_name, $data = NULL,  $check = FALSE, $position = NULL)
    {
        if ( ! $data || ! is_array($data))
            $data =  array( 'rel' => 'stylesheet/less', 'type' => 'text/css');
        elseif (is_array($data))
            $data['rel'] = 'stylesheet/less';
        $this->append_style($file_name, $data, $check, $position, 'less');
    }

    /**
     * adds inline css
     *
     * @param string $css
     * @access public
     */
    public function append_inline_css($css, $position = NULL)
    {
        $position = $this->get_position($position);
        if ( ! isset($this->inline_style[$position]))
            $this->inline_style[$position] = '';
        $this->inline_style[$position] .= $css;
    }


    /**
     * add inline javascript
     *
     * @param string $js
     * @access public
     */
    public function append_inline_script($js, $position = NULL)
    {
        $position = $this->get_position($position);
        if ( ! isset($this->inline_script[$position]) )
            $this->inline_script[$position] = '';
        $this->inline_script[$position] .= $js;
    }

    /**
     * add javascript variable
     *
     * @param string $js
     * @access public
     */
    public function append_js_var($name, $value, $position = NULL)
    {
        $position = $this->get_position($position);
        if ( ! isset($this->inline_script[$position]))
            $this->inline_script[$position] = '';
        $this->inline_script[$position] .= 'var '.$name.' = '.json_encode($value).";\n";
    }


    /**
     * @param $name
     * @param $data
     * @param null $position
     */
    public function append_js_template($name, $data, $position = NULL)
    {
        $position = $this->get_position($position);
        $this->js_templates[$position][$name] = $data;
    }

    public function js_templates($position)
    {
        return Arr::get($this->js_templates, $this->get_position($position), array());
    }

    /**
     * get all appended css files
     *
     * @return array
     * @access public
     */
    public function styles($position = NULL)
    {
        return Arr::get($this->styles, $position, array());
    }

    /**
     * return formatted string for inline style
     *
     * all included inline styles already wrapped in tag <style>
     *
     * @return string
     * @access public
     */
    public function inline_style($position = NULL)
    {
        $inline = Arr::get($this->inline_style, $this->get_position($position));
        return $inline ? "\n<style type=\"text/css\">\n ".$inline." \n</style>\n" : '';
    }

    /**
     * return a list with included javascript files
     *
     * @return array
     */
    public function scripts($position = NULL)
    {
        return Arr::get($this->scripts, $this->get_position($position), array());
    }

    /**
     * return formatted string for inline javascript
     *
     * all included inline script already wrapped in tag <script>
     *
     * @return string
     * @access public
     */
    public function inline_script($position = NULL)
    {
        $inline = Arr::get($this->inline_script, $this->get_position($position));
        return $inline ? "\n<script type=\"text/javascript\">\n ".$inline." \n</script>\n" : '';
    }

    /**
     *
     * @param $position
     * @return string
     */
    public function render($position) {
        return View::factory("media/html", array('position' => $position))->render();
    }
}
