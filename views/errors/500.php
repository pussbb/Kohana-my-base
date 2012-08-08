<?php defined('SYSPATH') or die('No direct script access.');

if ( ! is_array($error))
{
    $error = array(
        'message' => mb_ucfirst(__('houston_we_have_a_problem')),
    );
}
else 
{
    $error['message'] = mb_ucfirst(__('houston_we_have_a_problem'));
}
include Kohana::find_file('views', 'errors/default');
?>
