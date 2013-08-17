<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class to check user(registered user, or just visitor ...)
 * <code>
 *      Auth::is_admin();
 *      //or
 *      Auth::instance()->is_admin();
 * </code>
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category access
 * @see Base_Auth
 * @subpackage access
 */

class Auth extends Base_Auth {

    /**
    * @internal
    */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(array(Base_Auth::instance(), $name),  $arguments);
    }
}
