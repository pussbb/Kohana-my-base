<?php defined('SYSPATH') or die('No direct script access.');

class Model_Access_Rules extends Model
{

    public function get_rules($role_id = NULL)
    {
        $this->select();
        $this->role_id = $role_id;
        $this->where(array('role_id'));

        $result = $this->exec();

        if ( ! $result->valid())
        {
            return array();
        }
        return $result->as_array();
    }

}
