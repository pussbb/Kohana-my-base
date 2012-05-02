<?php

echo '<div class="control-group '. ($error ? ' error' : '').'">';
//     if ( $label){
//         echo Form::label($name, $label, array('class' => 'control-label'));
//     }
    echo '<div class="controls">';
        echo '<label class="checkbox">';
                echo Form::checkbox($name, $value, !is_null($value),$attr);
                echo $label;

        echo '</label>';
    if ( $error)
        echo '<span class="help-inline">'. $error .'</span>';
    if ( $info){
        echo '<p class="help-block">'. $info .'</p>';
    }
    echo '</div>';
echo '</div>';
