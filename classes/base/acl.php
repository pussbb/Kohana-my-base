<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 */
class Base_ACL extends Singleton{

    /**
     * @param $core
     * @return bool
     */
    public function allowed($core)
    {
        $user = Auth::instance()->current_user();
        if ( ! is_object($user))
            $role_id = Model_Access_Rule::ROLE_GUEST;
        else
            $role_id = $user->role_id;

        $current_request = $core->current_request_structure();

        return Model_Access_Rule::exists(array(
            'role_id' => $role_id,
            'directory' => Arr::get($current_request, 0),
            'controller' => self::dbexpr(Arr::get($current_request, 1)),
            'action' => self::dbexpr(Arr::get($current_request, 2)),
        ), 1, 30000);
    }

    /**
     * @static
     * @param $value
     * @return mixed
     */
    private static function dbexpr($value)
    {
        return DB::expr('REGEXP "('.$value.'|\\\\*)"');
    }
}
