<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Set the exception handler to use the Error module
 *
 * TODO watch for solution (currently Kohana always shows here excpetions)
 * Go back to the previous exception handler
 */

set_exception_handler(array('Error', 'handler'));

Kohana::$config->attach(new Config_File);

Gettext::init();

if ( Kohana::$environment != Kohana::PRODUCTION) {
    try {
        Tools_Coffeescript::check();
        Base_Media::register_media_handler('js', 'Tools_Coffeescript::build_if_needed');
    }
    catch(Exception_Tools_Missing $e) {}
    catch(Exception_Tools $e) {}

    try {
        Tools_Less::check();
        Base_Media::register_media_handler('css', 'Tools_Less::build_if_needed');
    }
    catch(Exception_Tools_Missing $e) {}
    catch(Exception_Tools $e) {}
}

if ( ! function_exists('debug') )
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

Route::set('www', '((<lang>)(/)(<controller>)(/<action>(/<id>)))', array(
  'lang' => Base_Language::uri_check_codes(),
  ))
    ->defaults(array(
        'lang' => NULL,
        'controller' => 'Welcome',
        'action'     => 'index',
    ));
