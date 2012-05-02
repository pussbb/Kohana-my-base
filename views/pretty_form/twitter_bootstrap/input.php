<?php

echo '<div class="control-group '. ($error ? ' error' : '').'">';
    if ( $label){
        echo Form::label($name, $label, array('class' => 'control-label'));
    }
    echo '<div class="controls">';
          $attr['class'] = Arr::get($attr, 'class') . ' input-xlarge' ;
          echo Form::input($name, $value, $attr);
    if ( $info){
        echo '<p class="help-block">'.Text::auto_p($error) . $info .'</p>';
    }
    echo '</div>';
echo '</div>';