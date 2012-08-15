<?php
    echo View::factory('layout/header', get_defined_vars())->render();
    echo '<div class="container">';
        echo $content;
    echo '</div> <!-- /container -->';
    echo View::factory('layout/footer', get_defined_vars())->render();
?>
