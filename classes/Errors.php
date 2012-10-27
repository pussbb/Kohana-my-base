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

 class Errors extends Kohana_Exception{
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
    {var_dump($this);
exit;
        if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']))
          $this->_print();
        parent::__construct($message, $variables, $code, $previous);
    }

    private function _print()
    {
        echo "\n".Kohana_Exception::text(this)."\n";
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
    {exit;
      if (Kohana::$environment === Kohana::PRODUCTION) {
          $type = get_class($e);
          $code = $e->getCode();
         // self::$error_view = Kohana::find_file('views', 'errors/' . strtolower($type) . '/' . $code);"errors/500";
          $file =  Kohana::find_file('views', 'errors/' . strtolower($type) . '/' . $code);
          $file = !empty($file) ? $file : Kohana::find_file('views', 'errors/' . strtolower($type));
          $file = !empty($file) ? $file : Kohana::find_file('views', 'errors/' . strtolower($code));
          Kohana_Kohana_Exception::$error_view = !empty($file) ? $file : Kohana::find_file('views', 'errors/default');
         
      } var_dump(Kohana_Kohana_Exception::$error_view);exit;
      parent::handler($e);
    }

    public static function shutdown_handler()
    {
        if ( ! Kohana::$_init)
        {
            // Do not execute when not active
            return;
        }

        try
        {
            if (Kohana::$caching === TRUE AND Kohana::$_files_changed === TRUE)
            {
                // Write the file path cache
                Kohana::cache('Kohana::find_file()', Kohana::$_files);
            }
        }
        catch (Exception $e)
        {
            // Pass the exception to the handler
            Kohana_Exception::handler($e);
        }

        if (Kohana::$errors AND $error = error_get_last() AND in_array($error['type'], Kohana::$shutdown_errors))
        {
            // Clean the output buffer
            ob_get_level() AND ob_clean();

            // Fake an exception for nice debugging
            Errors::handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

            // Shutdown now to avoid a "death loop"
            exit(1);
        }
    }
 }