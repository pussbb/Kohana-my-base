<?php defined('SYSPATH') or die('No direct script access.');

class Model_User extends Model
{

    public function _columns ()
    {
        return array(
            'email' => array(
                'type' => 'string',
                'max' => 434,
            ),
        );
    }

    public function rules()
    {
        return array(
            'email' => array(
                'not_empty',
                'email',
            ),
        );
    }

    public function login()
    {
        $this->password = md5($this->password);

        if ( ! $this->validate_login())
            return FALSE;

        $result = $this->select('*', 1)->filter(array('email', 'password'))->exec();
        if ( ! $result)
        {
            $this->add_error('general', __('user_not_found_or_wrong_password'));
            return FALSE;
        }
        Auth::instance()->authorize($this);
        return TRUE;
    }

    private function validate_login()
    {
        if ( ! Valid::email($this->email))
            $this->add_error('email', __('must_be_valid_email'));
        if ( ! Valid::not_empty($this->password))
            $this->add_error('pswd', __('must_be_your_valid_password'));
        if ($this->errors())
            return FALSE;

        return TRUE;
    }

    public function register()
    {
        $this->insert(array('login', 'email', 'password', 'api_key'));
        $this->api_key = uniqid();
        $this->password = md5($this->password);
        $this->role_id = Model_Access_Rules::ROLE_USER;
        $this->meta_data = json_encode(array());

        return $this->save();

    }

}
