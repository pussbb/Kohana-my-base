<?php defined('SYSPATH') or die('No direct script access.');

/**
 * main class to execute some external console app
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
    private static $config = NULL;
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
      if (Kohana::$is_windows)
            throw new Exception_Tools('Sorry but your platform currently not supported');
      if ( ! self::can_call('proc_open'))
           throw new Exception_Tools('Your system does not support to call proc_open');
      $klass = get_called_class();
      $klass::check();
      return call_user_func_array(array($klass::instance(), $name), $args);
    }

    /**
     * checks if function exists
     * @param $func_name string
     */
    public static function can_call($func_name = 'exec')
    {
        return function_exists($func_name);
    }
    /**
     * get config item
     * @param $key string
     * @param $default string
     * @static
     */
    public static function config($key, $default = NULL)
    {
        self::$config = self::$config ?:Kohana::$config->load("tools");
        return Arr::path(self::$config->as_array(), $key, $default);
    }
    /**
     * check if external app installed
     * @param $cmd string
     * @param $pattern string reg expresion
     * @static
     */
    public static function app_exists($cmd, $pattern)
    {
        exec($cmd, $result);
        return (bool)preg_match($pattern, implode('', $result));
    }
    /**
     * run external app 
     * @param $cmd string
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
        $this->stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $this->stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        return proc_close($proc) == 0;
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

        if( ! file_exists($source))
            return FALSE;

        if ( ! file_exists($destination))
            return TRUE;

        if (filemtime($source) > filemtime($destination))
            return TRUE;
        return FALSE;
    }
    /**
     * @ignore
     */
    public static function writable($dir)
    {
        if( ! is_writable($dir))
            throw new Exception_Tools("You don't have permission to write in  $destination");
    }
    /**
     * returns error wich retrun external app
     */
    public function error()
    {
      return $this->stderr?:$this->stdout;
    }
}