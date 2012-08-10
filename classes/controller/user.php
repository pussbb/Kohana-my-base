<?php defined('SYSPATH') or die('No direct script access.');

class Controller_User extends Controller_Core {

    public function action_logout()
    {
        Auth::instance()->logout();
        $this->redirect('/');
    }

    public function action_login()
    {
        $this->view->errors = array();

        if ( ! $_REQUEST)
            return ;

        $model = new Model_Users(array(
            'email' => Arr::get($_REQUEST, 'email'),
            'password' => Arr::get($_REQUEST, 'pswd'),
        ));
        if ( ! $model->login())
        {
            $this->view->errors = $model->errors();
        }
        else
        {
            $this->redirect_user('login_success_uri');
        }
    }

    private function redirect_user($condition)
    {
        $uri = Kohana::$config->load('user.'.$condition);
        $this->redirect($uri?:Kohana::$base_url);
    }

    public function action_register()
    {
        $this->view->errors = array();

        if ( ! $_REQUEST)
            return ;

        $model = new Model_Users(array(
            'login' => Arr::get($_REQUEST, 'user_name'),
            'email' => Arr::get($_REQUEST, 'email'),
            'password' => Arr::get($_REQUEST, 'pswd'),
            'terms_of_use' => Arr::get($_REQUEST, 'terms_of_use'),
            'pswd_confirmation' => Arr::get($_REQUEST, 'pswd_confirmation'),
        ));
        if ( ! $model->validate_registration() || ! $model->register())
        {
            $this->view->errors = $model->errors();
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