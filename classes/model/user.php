<?php defined('SYSPATH') or die('No direct script access.');

class Model_User extends Model
{


    protected $order = array('email', 'DESC');

    protected static function _columns ()
    {
        return array(
            'email' => array(
                'type' => 'string',
                'max' => 434,
            ),
        );
    }

    public function relations()
    {
        return array(
            'access_rules' => array(
                Model::HAS_MANY,
                'Model_Access_Rule',
                'role_id',
                'role_id'
            ),
            'total_rules' => array(
                Model::STAT,
                'Model_Access_Rule',
                'role_id',
                'role_id'
            ),
        );
    }

    public function rules()
    {
        return array(
            'login' => array(
                'not_empty',
            ),
            'email' => array(
                array('email', array($this->email, TRUE)),
                'email_domain',
                'unique',
            ),
            'password' => array(
                'not_empty',
                array('min_length', array($this->password, 6)),
            )
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

    public function registration_rules()
    {
        return array(
            'pswd_confirmation' => array(
                'not_empty',
                array('equals', array($this->pswd_confirmation, $this->password, 'password'))
            ),
//            'terms_of_use' => array(
//                'not_empty'
//            ),
        );
    }

    public function before_save()
    {
        if ($this->new_record())
            $this->password = md5($this->password);
    }

    public function register()
    {
        $this->insert(array('login', 'email', 'password', 'api_key'));
        $this->api_key = uniqid();
        $this->role_id = Model_Access_Rule::ROLE_USER;
        $this->meta_data = json_encode(array());

        if ( ! $this->validate($this->registration_rules()))
            return FALSE;
        return $this->save();
    }

}
