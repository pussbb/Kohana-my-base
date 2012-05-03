<?php defined('SYSPATH') or die('No direct script access.');

class ACL extends Singleton{

    public function allowed($core)
    {
        $user = Auth::instance()->current_user();
        if ( ! is_object($user))
            return FALSE;
        return TRUE;
    }
}