<?php defined('SYSPATH') or die('No direct script access.');?>

<div id="authbox" class="row-fluid">
<?php
      $general = Arr::get($errors, 'general');

      if ( $general)
      {
          echo '<div class="alert alert-error">
              <a class="close" data-dismiss="alert" href="#">×</a>
                      <h4 class="alert-heading">'.tr('Warning').'!</h4>
                      '.$general.'
                      </div>';
      }

    echo View::factory('users/login', get_defined_vars())->render();
    echo View::factory('users/recovery', get_defined_vars())->render();
?>

</div>
