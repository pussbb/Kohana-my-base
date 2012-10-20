<?php

echo '<div class="control-group '. ($error ? ' error' : '').'">';
    if ( $label){
        echo Form::label($name, $label, array('class' => 'control-label'));
    }
    echo '<div class="controls">';
          echo '<div class="input-prepend">';
                echo '<span class="add-on"><i class="icon-lock"></i></span>';
                echo Form::input($name, $value, $attr);
          echo '</div>';
    if ( $error)
        echo '<span class="help-inline">'. $error .'</span>';
    if ( $info){
        echo '<p class="help-block">'. $info .'</p>';
    }
    echo '</div>';
echo '</div>';