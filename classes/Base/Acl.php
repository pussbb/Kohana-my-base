<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Base class to check user permission for some request
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category access
 * @subpackage access
 */

class Base_Acl extends Singleton {

    /**
     * Checks if user allowed to view this page
     *
     * @param $request_structure
     * @internal param $core (object) instance of Core template
     * @return bool on success returns TRUE otherwise FALSE
     */
    public function allowed($request_structure)
    {
        $user = Auth::instance()->current_user();
        if ( is_null($user))
            $role_id = Model_Access_Rule::ROLE_GUEST;
        else
            $role_id = array(Model_Access_Rule::ROLE_USER, $user->role_id);

        return Model_Access_Rule::exists(array(
            'role_id' => $role_id,
            'directory' => Arr::get($request_structure, 0),
            'controller' => self::dbexpr($request_structure[1]),
            'action' => self::dbexpr($request_structure[2]),
        ), 1, 30000);
    }

    /**
     * @ignore
     * @static
     * @param $value
     * @return mixed
     */
    protected static function dbexpr($value)
    {
        return DB::expr('REGEXP "('.$value.'|\\\\*)"');
    }
}
