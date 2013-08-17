<?php defined('SYSPATH') or die('No direct script access.');

/**
 * main class to execute some external console app
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category tools
 * @subpackage tools
 */

class Tools extends Singleton {

    /**
     * config
     */
    public static $config = NULL;
     /**
     * stdout stream
     */
    protected $stdout = NULL;
     /**
     * stderr stream
     */
    protected  $stderr = NULL;

    /**
     * @ignore
     */
    public static function __callStatic($name, $args)
    {
      $klass = get_called_class();
      return call_user_func_array(array($klass::instance(), $name), $args);
    }

    public static function check()
    {
      if (Kohana::$is_windows)
            throw new Exception_Tools('Sorry but your platform currently not supported');
      if ( ! self::can_call('proc_open') )
           throw new Exception_Tools('Your system does not support to call proc_open');
    }

    /**
     * checks if function exists
     * @param $func_name string
     * @return bool
     * @static
     */
    public static function can_call($func_name = 'exec')
    {
        return function_exists($func_name);
    }
    /**
     * get config item
     * @param $key string
     * @param $default string
     * @return mixed
     * @static
     */
    public function config($key, $default = NULL)
    {
        return Arr::path(self::$config, $key, $default);
    }
    /**
     * check if external app installed
     * @param $cmd string
     * @param $pattern string reg expresion
     * @static
     * @return bool
     */
    public static function app_exists($cmd, $pattern)
    {
        exec($cmd, $result);
        return (bool)preg_match($pattern, implode('', $result));
    }

    public static function append_command_option($command, array $cmd_options)
    {
        foreach($cmd_options as $option => $value) {
            $value = $value ? escapeshellarg($value).' ' : '';
            $command .= "$option $value";
        }
        return $command;
    }

    /**
     * run external app
     * @param $cmd string
     * @return bool
     */
    public function exec($cmd)
    {
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
        stream_set_blocking($pipes[2], 0);
        $this->stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $this->stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $return_value = proc_close($proc);

        if ($this->stderr)
            throw new Exception_Tools_ErrorOutput(Text::strip_ansi_color($this->stderr));
/*
        if ($this->stdout)
            throw new Exception_Tools_ErrorOutput($this->stdout);*/
        return $return_value;
    }

    /**
     * checks if coffee script exists
     *
     * Returns TRUE if coffee script exists and he is newer than javascript script
     * or if javascript file does not exists
     *
     * @param $source full path to cofee script file
     * @param $destination full path to javascript file
     * @return bool
     * @access private
     */
    protected static function need_compile($source, $destination)
    {
        clearstatcache();

        if( ! file_exists($source))
            return FALSE;

        if ( ! file_exists($destination))
            return TRUE;

        if ((filemtime($source) > filemtime($destination))
            || (fileatime($source) > fileatime($destination)))
            return TRUE;
        return FALSE;
    }
    /**
     * @ignore
     */
    public static function writable($dir)
    {
        if( ! is_writable($dir))
            throw new Exception_Tools("You don't have permission to write in  $dir");
    }
    /**
     * returns error wich retrun external app
     */
    public function error()
    {
        return $this->stderr?:$this->stdout;
    }
}

Tools::$config = Kohana::$config->load("tools")->as_array();
