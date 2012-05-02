<?php

echo '<div class="control-group '. ($error ? ' error' : '').'">';
    if ( $label){
        echo Form::label($name, $label, array('class' => 'control-label'));
    }
    echo '<div class="controls">';
          echo Form::input($name, $value, $attr);
    if ( $error)
        echo '<span class="help-inline">'. $error .'</span>';
    if ( $info){
        echo '<p class="help-block">'. $info .'</p>';
    }
    echo '</div>';
echo '</div>';