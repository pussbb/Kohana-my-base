<?php defined('SYSPATH') or die('No direct script access.');

class ACL extends Singleton{

    public function allowed($core)
    {
        $user = Auth::instance()->current_user();
        if ( ! is_object($user))
            return FALSE;

        if ($user->role_id == Model_Users::ROLE_ADMIN)
        {
            return TRUE;
        }

        $access_model = new Model_Access_Rules();
        $rules = $access_model->get_rules($user->role_id);
        $current_path = implode('/', $core->current_request_structure());
        foreach ($rules as $rule)
        {
            $accepted_path = implode('/', array(
                $rule['directory'],
                $rule['controller'],
                $rule['action'],
            ));
            if ($accepted_path == $current_path)
            {
                return TRUE;
            }
        }
        return FALSE;
    }

}
