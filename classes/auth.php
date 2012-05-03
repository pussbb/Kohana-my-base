<?php defined('SYSPATH') or die('No direct script access.');

class Auth extends Singleton{

    public function authorize(&$user)
    {
        Session::instance()->set('auth', array('current_user' => $user));
    }

    public function current_user()
    {
        $user = Arr::get(Session::instance()->get('auth'), 'current_user');
        return is_object($user) ? clone $user: NULL;
    }

    public function logout()
    {
        Session::instance()->destroy();
    }

    public function logged_in()
    {
        return ! is_null(Session::instance()->get('auth'));
    }
}