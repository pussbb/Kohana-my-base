<?php defined('SYSPATH') or die('No direct script access.');

class Auth extends Singleton{

    public function authorize($user = '')
    {
        Session::instance()->set('user','blabla');
    }

    public function current_user()
    {

    }

    public function logout()
    {
        Session::instance()->destroy();
    }

    public function logged_in()
    {
        return Session::instance()->get('user');
    }
}