<?php defined('SYSPATH') or die('No direct script access.');

class ACL extends Singleton{

    public function allowed($core, $user=null)
    {
        //$request
        ///echo '<pre>';
        //var_dump($core);exit;
        return TRUE;
    }
}