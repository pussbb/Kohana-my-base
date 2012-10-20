<?php

echo '<div class="control-group '. ($error ? ' error' : '').'">';
    if ( $label){
        echo Form::label($name, $label, array('class' => 'control-label'));
    }
    echo '<div class="controls">';
          echo '<div class="input-prepend">';
                $add_on = '';
                if (isset($icon))
                  $add_on = '<i class="icon-'.$icon.'"></i>';
                elseif (isset($char))
                    $add_on = $char;
                echo '<span class="add-on">'.$add_on.'</span>';
                echo Form::input($name, $value, $attr);
          echo '</div>';
    if ( $error)
        echo '<span class="help-inline">'. $error .'</span>';
    if ( $info){
        echo '<p class="help-block">'. $info .'</p>';
    }
    echo '</div>';
echo '</div>';