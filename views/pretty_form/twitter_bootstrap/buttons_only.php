<?php defined('SYSPATH') or die('No direct script access.');

foreach($buttons as $button)
{
    echo Form::button(
        Arr::get($button, 0),
        Arr::get($button, 1),
        Arr::get($button, 2)
    );
}
?>
