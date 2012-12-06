<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Base class to manage roles for user
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com(alexnevpryaga@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category access
 * @subpackage access
 */

class Base_Auth extends Singleton {

    /**
     * remember just logged in user
     * @param $user (object)
     */
    public function authorize(&$user)
    {
        Session::instance()->set('auth', array('current_user' => $user));
    }

    /**
     * get current logged in user
     * @return object|null if in session not valid object returns NULL otherwise object
     */
    public function current_user()
    {
        $user = Arr::get(Session::instance()->get('auth'), 'current_user');
        return is_object($user) && $user instanceof Model ? $user : NULL;
    }

    /**
     * destroy session for current logged in user
     * @return void
     */
    public function logout()
    {
        Session::instance()->destroy();
    }

    /**
     * checks if user logged in or just a visitor
     * @return bool if user loggged in  return TRUE otherwise FALSE
     */
    public function logged_in()
    {
        $user = $this->current_user();
        return ! is_null($user) && (is_object($user) && $user instanceof Model);
    }

    /**
     * checks if user has role
     *
     * <code>
     *
     *      Auth::has_role(Model_Access_Role::ROLE_ADMIN);
     *      //or
     *      Auth::instance()->has_role(Model_Access_Role::ROLE_ADMIN);
     *
     * </code>
     * @param $user_role
     * @see Model_Access_Role constants
     * @return bool
     */
    public function has_role($user_role)
    {
        return $this->current_user()->role_id === $user_role;
    }

    /**
     * checks if user is admin
     * @return bool
     */
    public function is_admin()
    {
        return $this->has_role(Model_Access_Role::ROLE_ADMIN);
    }

}
