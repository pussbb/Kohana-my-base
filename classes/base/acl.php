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
