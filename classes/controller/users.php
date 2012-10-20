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

class Controller_Users extends Controller_Core {

    public function action_logout()
    {
        Auth::instance()->logout();
        $this->redirect('/');
    }

    public function action_login()
    {
        $this->errors = array();
        $this->set_filename('users/auth');
        $this->append_media('auth');

        if ( ! $_REQUEST)
            return ;

        $model = new Model_User(array(
            'email' => Arr::get($_REQUEST, 'email'),
            'password' => Arr::get($_REQUEST, 'pswd'),
        ));
        if ( ! $model->login())
            $this->errors = $model->errors();
        else
            $this->redirect_user('login_success_uri');
    }

    private function redirect_user($condition)
    {
        $uri = Kohana::$config->load("site.$condition");
        $this->redirect($uri?:Kohana::$base_url);
    }

    public function action_recovery()
    {
        return $this->redirect_user('login_success_uri');
    }

    public function action_register()
    {
        $this->errors = array();

        if ( ! $_REQUEST)
            return ;

        $model = new Model_User(array(
            'login' => Arr::get($_REQUEST, 'login'),
            'email' => Arr::get($_REQUEST, 'email'),
            'password' => Arr::get($_REQUEST, 'pswd'),
//            'terms_of_use' => Arr::get($_REQUEST, 'terms_of_use'),
            'pswd_confirmation' => Arr::get($_REQUEST, 'pswd_confirmation'),
        ));
        if ( ! $model->register())
        {
            $this->errors = $model->errors();
        }
        else
        {
            $this->redirect_user('register_success_uri');
        }
    }

    public function action_account_info()
    {
      if ( $this->is_ajax())
          $this->render_partial();
    }

    public function action_settings()
    {
        $this->render_nothing();
    }
}