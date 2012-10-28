<?php

/**
 * Class ....
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com(alexnevpryaga@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

 class Error extends Kohana_Exception {

    protected static $custom_view_file = NULL;
    /**
     * Creates a new translated exception.
     *
     *     throw new Kohana_Exception('Something went terrible wrong, :user',
     *         array(':user' => $user));
     *
     * @param   string          $message    error message
     * @param   array           $variables  translation variables
     * @param   integer|string  $code       the exception code
     * @param   Exception       $previous   Previous exception
     * @return  void
     */
    public function __construct($message = "", array $variables = NULL, $code = 0, Exception $previous = NULL)
    {
        if (PHP_SAPI == 'cli')
          self::_print($this);
        parent::__construct($message, $variables, $code, $previous);
    }

    private static function _print(Exception $e)
    {
        echo "\n".Kohana_Exception::text($e)."\n";
        exit(1);
    }

     /**
     * Exception handler, logs the exception and generates a Response object
     * for display.
     *
     * @uses    Kohana_Exception::response
     * @param   Exception  $e
     * @return  boolean
     */
    public static function handler(Exception $e)
    {
      try {

        if (Request::$current !== NULL
                  && Request::current()->is_ajax() === TRUE
                  && Kohana::$environment !== Kohana::PRODUCTION) {
            self::_print($e);
        }

        if (Kohana::$environment === Kohana::PRODUCTION && ! self::$custom_view_file)
          Kohana_Kohana_Exception::$error_view = self::get_view_file($e);
        elseif (self::$custom_view_file)
            Kohana_Kohana_Exception::$error_view = self::$custom_view_file;

        parent::handler($e);

      } catch(Exception $e) {
          /**
            * Things are going *really* badly for us, We now have no choice
            * but to bail. Hard.
            */
          // Clean the output buffer if one exists
          ob_get_level() AND ob_clean();

          // Set the Status code to 500, and Content-Type to text/plain.
          header('Content-Type: text/plain; charset='.Kohana::$charset, TRUE, 500);

          echo Kohana_Exception::text($e);

          exit(1);
      }
    }

    private static  function get_view_file($e)
    {
      $view_files = array(
        get_class($e).'/'. $e->getCode(),
        get_class($e),
        $e->getCode(),
        'default'
      );
      foreach($view_files as $view_file)
      {
          $_view_file = 'errors/'.$view_file;
          if ((bool)Kohana::find_file('views', $_view_file))
            return $_view_file;
      }
    }
 }