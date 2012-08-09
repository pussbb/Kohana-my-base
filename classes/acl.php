<?php defined('SYSPATH') or die('No direct script access.');

class ACL extends Singleton{

    public function allowed($core)
    {
        $user = Auth::instance()->current_user();
        if ( ! is_object($user))
            $role_id = Model_Access_Rules::ROLE_GUEST;
        else
            $role_id = $user->role_id;

        $current_request = $core->current_request_structure();

        try {
            Model_Access_Rules::find(array(
                    'role_id' => $role_id,
                    'directory' => Arr::get($current_request, 0),
                    'controller' => Arr::get($current_request, 1),
                    'action' => Arr::get($current_request, 2),
            ), 30000);
            return TRUE;
        } catch(Exception $e) {}
        return  FALSE;
    }

}
