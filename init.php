<?php defined('SYSPATH') OR die('No direct access allowed.');
Gettext::$gettext_enabled = Gettext::gettext_enabled();
/**
 * Set the exception handler to use the Error module
 *
 * TODO watch for solution (currently Kohana always shows here excpetions)
 * Go back to the previous exception handler
 */

set_exception_handler(array('Error', 'handler'));

if ( ! function_exists('tr'))
{
    /**
    * translate string via gettext
    * or Kohana's translation function
    *
    * @param $string string
    * @param $values array
    * @return string
    */

    function tr($string, array $values = NULL)
    {
        if (Gettext::$gettext_enabled)
            return vsprintf(gettext($string), $values);

        preg_match_all('/%(?:\d+\$)?[+-]?(?:[ 0]|\'.{1})?-?\d*(?:\.\d+)?[bcdeEufFgGosxX]/', $str, $matches, PREG_PATTERN_ORDER);
        return __($string, array_combine($matches, $values));
    }
}

if ( ! function_exists('debug'))
{
    /**
    * helper function to print dump of var and exit if needed
    * @param mixed
    */
    function debug()
    {
        $exit = FALSE;
        $args = func_get_args();
        if (is_bool(end($args)) && count($args) > 1)
            $exit = array_pop($args);
        echo call_user_func_array('Debug::vars', $args);
        if ($exit) exit(0);
    }
}

Kohana::$config->attach(new Config_File);

Route::set('www', '((<lang>)(/)(<controller>)(/<action>(/<id>)))', array(
  'lang' => Language::uri_check_codes(),
  ))
    ->defaults(array(
        'lang' => NULL,
        'controller' => 'Welcome',
        'action'     => 'index',
    ));
