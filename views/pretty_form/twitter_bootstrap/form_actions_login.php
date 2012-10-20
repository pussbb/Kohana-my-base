<?php defined('SYSPATH') or die('No direct script access.');?>
<div class="form-actions">
    <span class="pull-left">
        <a href="#" class="flip-link" id="to-recover"><?php echo tr('Lost password?');?></a>
    </span>
    <span class="pull-right">
    <?php
        foreach($buttons as $button)
        {
            echo Form::button(
                Arr::get($button, 0),
                Arr::get($button, 1),
                Arr::get($button, 2)
            );
        }
    ?>
    </span>
</div>
