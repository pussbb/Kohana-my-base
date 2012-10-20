<?php defined('SYSPATH') or die('No direct script access.');

$form = new Pretty_Form(array(
    'errors' => $errors,
    'template' => 'twitter_bootstrap',
));

echo $form->open( Url::site('users/recovery'), array('class' => 'form-vertical', 'id' => 'recoverform'));

echo '<p>';
  echo tr('Enter your e-mail address below and we will send you instructions how to recover a password.');
echo '</p>';

echo $form->input(array(
    'name' => 'email',
    'template' => 'input_prepend',
    'char' => '<b>@</b>',
));

echo $form->form_actions(array(
    'template' => 'form_actions_recovery',
    'buttons' => array(
        array('submit', tr('Recover'), array( 'class' => 'btn btn-primary', 'type' => 'submit'))
    )
));
echo $form->close();

