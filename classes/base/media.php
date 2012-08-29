<?php defined('SYSPATH') or die('No direct script access.');

class Base_Media extends Singleton{

    private $config = NULL;
    private $styles = array();
    private $inline_style = '';
    private $inline_script = '';
    private $scripts = array();
    private $path = NULL;

    public function __construct()
    {
        $this->config = Kohana::$config->load('media');
        $this->bundle('default');
    }

    private function config($key)
    {
         if (strpos($key, '.') !== FALSE)
            return Arr::path($this->config, $key);
        return Arr::get($this->config, $key);
    }

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

    public function find_file($name, $prefix)
    {
        $path = $this->config('core.path');
        $file = $path.$prefix.DIRECTORY_SEPARATOR.$name.'.'.$prefix;
        if (file_exists($file))
            return $file;
        return Kohana::find_file('media',$name, $prefix);
    }

    private function is_url($uri)
    {
        return Valid::url($uri);
    }

    private function resource($file_name, $prefix)
    {
        if ($this->is_url($file_name))
            return $file_name;
        if ( strpos('static://', $file_name) === TRUE)
            return str_replace('static://', $this->config('core.static_uri').$prefix.'/', $file_name.'.'.$prefix);
        return Url::base(TRUE,TRUE).$this->config('core.uri').$prefix.'/'.$file_name.'.'.$prefix;
    }

    public function append_style($file_name, $media = NULL, $check = FALSE)
    {
        if ($check && ! $this->find_file($file_name, 'css'))
            return;
        $this->styles[$this->resource($file_name, 'css')]= $media;
    }

    public function append_inline_style($css)
    {
        if ( ! $css)
            return;
        $this->inline_style .= $css;
    }

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

    public function append_inline_script($js)
    {
        if ( ! $js)
            return;
        $this->inline_script .= $js;
    }

    public function styles()
    {
        return $this->styles;
    }

    public function inline_style()
    {
        if ( ! $this->inline_style)
            return;
        return "\n<style type=\"text/css\">\n$this->inline_style\n</style>\n";
    }

    public function scripts()
    {
        return array_unique($this->scripts);
    }

    public function inline_script()
    {
        if ( ! $this->inline_script)
            return;
        return "\n<script type=\"text/javascript\">\n$this->inline_script\n</script>\n";
    }

    private function need_compile($source, $destination)
    {
        if( ! file_exists($source))
            return FALSE;
        if ( ! file_exists($destination))
            return TRUE;
        if (file_exists($source) && filemtime($source) >= filemtime($destination))
            return FALSE;
        return TRUE;
    }

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
                $compile &= $this->need_compile($_source, $destination);
                $source .= ' ' . $_source;
            }
            if ( ! file_exists($destination))
                $compile = TRUE;
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
            return;

        throw new Kohana_Exception(
            __("coffescript_compiler_output_for :file : :output", array(
                ':file' => $destination,
                ':output' => $output,
            ))
        );
    }
}
