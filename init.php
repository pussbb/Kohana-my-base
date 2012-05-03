<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Set the exception handler to use the Error module
 */
set_exception_handler(array('Error', 'handler'));

if ( ! function_exists('debug'))
{
    function debug($var, $exit = FALSE)
    {
        echo '<pre>';
            print_r($var); // or var_dump($var);
        echo '</pre>';
        ! $exit ?: exit;
    }
}
