<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Kohana-my-base
 * Attemp to create module with classes for Kohana framework,
 * with main goal make developing web applications more easily(as for me)
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com(alexnevpryaga@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2 
 * @link https://github.com/pussbb/Kohana-my-base
 */

class Base_Auth extends Singleton {

    /**
     * @param $user
     */
    public function authorize(&$user)
    {
        Session::instance()->set('auth', array('current_user' => $user));
    }

    /**
     * @return null
     */
    public function current_user()
    {
        $user = Arr::get(Session::instance()->get('auth'), 'current_user');
        return is_object($user) ? clone $user : NULL;
    }

    /**
     *
     */
    public function logout()
    {
        Session::instance()->destroy();
    }

    /**
     * @return bool
     */
    public function logged_in()
    {
        return !is_null($this->current_user());
    }

    /**
     * @param $user_role
     * @return mixed
     */
    public function has_role($user_role)
    {
        return $this->current_user()->role_id = $user_role;
    }

    /**
     * @return mixed
     */
    public function is_admin()
    {
        return $this->has_role(Model_Access_Role::ROLE_ADMIN);
    }

}