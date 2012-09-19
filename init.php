<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Set the exception handler to use the Error module
 */
set_exception_handler(array('Error', 'handler'));

if (Kohana::$environment == Kohana::PRODUCTION)
{
    Kohana_Kohana_Exception::$error_view = "errors/500";
}

if ( ! function_exists('tr'))
{
	function tr($string, array $values = NULL)
	{
        if (I18n::gettext_enabled()) 
    		return vprintf(gettext($string), $values);

        preg_match_all('/%(?:\d+\$)?[+-]?(?:[ 0]|\'.{1})?-?\d*(?:\.\d+)?[bcdeEufFgGosxX]/', $str, $matches, PREG_PATTERN_ORDER);
        return __($string, array_combine($matches, $values));
	}
}

if ( ! function_exists('debug'))
{
    function debug()
    {
        $exit = FALSE;
        $args = func_get_args();
        if (is_bool(end($args)) && count($args) > 1)
            $exit = array_pop($args);
        echo call_user_func_array('Debug::vars', $args);
        if ($exit)
            exit();
    }
}
