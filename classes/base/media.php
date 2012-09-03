<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Kohana-my-base
 * Attemp to create module with classes for Kohana framework,
 * with main goal make developing web applications more easily(as for me)
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com(alexnevpryaga@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2 
 * @link https://github.com/pussbb/Kohana-my-base
 */

class Base_Media extends Singleton{

    /**
     * @var null
     */
    private $config = NULL;
    /**
     * @var array
     */
    private $styles = array();
    /**
     * @var string
     */
    private $inline_style = '';
    /**
     * @var string
     */
    private $inline_script = '';
    /**
     * @var array
     */
    private $scripts = array();
    /**
     * @var null
     */
    private $path = NULL;

    /**
     *
     */
    public function __construct()
    {
        $this->config = Kohana::$config->load('media');
        $this->bundle('default');
    }

    /**
     * @param $key
     * @return mixed
     */
    private function config($key)
    {
         if (strpos($key, '.') !== FALSE)
            return Arr::path($this->config, $key);
        return Arr::get($this->config, $key);
    }

    /**
     * @param $name
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
     * @param $key
     * @param $name
     * @param null $media
     * @param bool $check
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
     * @param $name
     * @param $prefix
     * @return string
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
     * @param $uri
     * @return mixed
     */
    private function is_url($uri)
    {
        return Valid::url($uri);
    }

    /**
     * @param $file_name
     * @param $prefix
     * @return mixed|string
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
     * @param $file_name
     * @param null $media
     * @param bool $check
     */
    public function append_style($file_name, $media = NULL, $check = FALSE)
    {
        if ($check && ! $this->find_file($file_name, 'css'))
            return;
        $this->styles[$this->resource($file_name, 'css')]= $media;
    }

    /**
     * @param $css
     */
    public function append_inline_style($css)
    {
        if ( ! $css)
            return;
        $this->inline_style .= $css;
    }

    /**
     * @param $file_name
     * @param bool $check
     */
    public function append_script($file_name, $check = FALSE)
    {
        $files = NULL;
        if (Arr::is_array($file_name)) {
            $files = Arr::get($file_name, 'files');
            $file_name = Arr::get($file_name, 'name');
        }

        if ( Kohana::$environment != Kohana::PRODUCTION) {
            $this->coffeescript($file_name, $files);
        }
        if ($check && ! $this->find_file($file_name, 'js'))
            return;
        $this->scripts[]= $this->resource($file_name, 'js');
    }

    /**
     * @param $js
     */
    public function append_inline_script($js)
    {
        if ( ! $js)
            return;
        $this->inline_script .= $js;
    }

    /**
     * @return array
     */
    public function styles()
    {
        return $this->styles;
    }

    /**
     * @return string
     */
    public function inline_style()
    {
        if ( ! $this->inline_style)
            return;
        return "\n<style type=\"text/css\">\n$this->inline_style\n</style>\n";
    }

    /**
     * @return array
     */
    public function scripts()
    {
        return array_unique($this->scripts);
    }

    /**
     * @return string
     */
    public function inline_script()
    {
        if ( ! $this->inline_script)
            return;
        return "\n<script type=\"text/javascript\">\n$this->inline_script\n</script>\n";
    }

    /**
     * @param $source
     * @param $destination
     * @return bool
     */
    private function need_compile($source, $destination)
    {

        if( ! file_exists($source))
            return FALSE;

        if ( ! file_exists($destination))
            return TRUE;

        if (filemtime($source) > filemtime($destination))
            return TRUE;
        return FALSE;
    }

    /**
     * @param $file_name
     * @param null $files
     * @throws Kohana_Exception
     */
    private function coffeescript($file_name, $files = NULL)
    {
        $source_path = $this->config('core.coffeescript.source_path');
        $dest_path = $this->config('core.coffeescript.dest_path');
        $destination = $dest_path.$file_name.'.js';
        $join = '';
        $source = '';
        $compile = FALSE;

        if (Arr::is_array($files)) {
            $join = ' -j '. $file_name.'.js';
            foreach($files as $file) {
                $_source = $source_path.$file.'.coffee';
                if ( ! $compile)
                    $compile = $this->need_compile($_source, $destination);
                $source .= ' ' . $_source;
            }
        }
        else {
            $source = $source_path.$file_name.'.coffee';
            $compile = $this->need_compile($source, $destination);
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
            return $this->minize_script($destination);

        throw new Kohana_Exception(
            __("coffescript_compiler_output_for :file : :output", array(
                ':file' => $destination,
                ':output' => $output,
            ))
        );
    }

    /**
     * @param $file
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
