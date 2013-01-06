<?php defined('SYSPATH') or die('No direct script access.');?>

<div id="authbox" class="row-fluid">
<?php
    echo View::factory('users/login', get_defined_vars())->render();
    echo View::factory('users/recovery', get_defined_vars())->render();
?>

</div>
