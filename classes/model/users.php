<?php defined('SYSPATH') or die('No direct script access.');

class Model_Users extends Model
{
    public function login()
    {
        $this->password = md5($this->password);
        $this->select();
        $this->where(array('email', 'password'));
        $result = $this->exec();
        if ( ! $result->valid())
        {
            $this->add_error('general', __('user_not_found_or_wrong_password'));
            return FALSE;
        }
        Auth::instance()->authorize($this);
        return TRUE;
    }

    public function validate_login()
    {
        if ( ! Valid::email($this->email))
            $this->add_error('email', __('must_be_valid_email'));
        if ( ! Valid::not_empty($this->password))
            $this->add_error('pswd', __('must_be_your_valid_password'));
        if ($this->errors())
            return FALSE;

        return TRUE;
    }

    public function validate_registration()
    {
        if ( ! Valid::not_empty($this->login))
            $this->add_error('user_name', __('must_be_not_empty'));
        if ( ! Valid::email($this->email))
            $this->add_error('email', __('must_be_valid_email'));
        if ( ! Valid::not_empty($this->terms_of_use))
            $this->add_error('terms_of_use', __('you_must_accept_terms_of_use'));
        if ( ! Valid::min_length($this->password, 6))
            $this->add_error('pswd', __('must_be_at_least_6_characters_long'));
        if ( ! Valid::equals($this->pswd_confirmation, $this->password))
            $this->add_error('pswd_confirmation', __('password_confirmation_doesn_match'));

        if ($this->errors())
            return FALSE;

        if ($this->exists(array('login', 'email')))
        {
            $this->add_error('general', __('user_already_exists'));
            return FALSE;
        }
        return TRUE;
    }

    public function register()
    {
        $this->insert(array('login', 'email', 'password', 'api_key'));
        $this->api_key = uniqid();
        $this->password = md5($this->password);
        $this->role_id = self::ROLE_USER;

        $result = $this->exec();
        return $result;
    }

}
