<?php defined('SYSPATH') or die('No direct script access.');

$form = new Pretty_Form(array(
    'errors' => $errors,
    'template' => 'twitter_bootstrap',
));

echo $form->open( URL::site('users/login'), array('class' => 'form-vertical', 'id' => 'loginform'));

echo '<p>';
  echo tr('Enter username and password to continue.');
echo '</p>';

echo $form->input(array(
    'name' => 'email',
    'template' => 'input_prepend',
    'char' => '<b>@</b>',
));

echo $form->password(array(
    'name' => 'pswd',
    'template' => 'input_prepend',
    'icon' => 'lock',
));


echo $form->form_actions(array(
    'template' => 'form_actions_login',
    'buttons' => array(
        array('submit', tr('Login'), array( 'class' => 'btn btn-primary', 'type' => 'submit'))
    )
));
echo $form->close();

