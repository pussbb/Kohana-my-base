<?php defined('SYSPATH') or die('No direct script access.');

class Model_Access_Rules extends Model
{
    const ROLE_GUEST = 0;
    const ROLE_USER = 1;
    const ROLE_ADMIN = 2;

    public static function roles_collection($translation = FALSE)
    {
        return array(
            self::ROLE_GUEST => $translation ? __('guest') : 'guest',
            self::ROLE_USER => $translation ? __('user') : 'user',
            self::ROLE_ADMIN => $translation ? __('admin') : 'admin',
        );
    }

    public function check_access()
    {
        $this->select()->cached(56565656565);
        $result = $this->filter(array('role_id', 'directory', 'controller', 'action'))->exec();
        return $result->valid();
    }
}
