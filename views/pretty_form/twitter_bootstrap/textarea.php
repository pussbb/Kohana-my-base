<?php

echo '<div class="control-group '. ($error ? ' error' : '').'">';
    if ( $label){
        echo Form::label($name, $label, array('class' => 'control-label'));
    }
    echo '<div class="controls">';
    if ( $error)
        echo '<span class="help-inline">'. $error .'</span>';
    echo Form::textarea($name, $value, $attr);
    if ( $info){
        echo '<p class="help-block">'. $info .'</p>';
    }
    echo '</div>';
echo '</div>';
