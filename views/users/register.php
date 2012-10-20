
<div  id="authbox" class="reg-form">
<?php

$general = Arr::get($errors, 'general');

if ( $general)
{
    echo '<div class="alert alert-error">
        <a class="close" data-dismiss="alert" href="#">×</a>
                <h4 class="alert-heading">'.tr('Warning').'!</h4>
                '.$general.'
                </div>';
}

$form = new Pretty_Form(array(
    'errors' => $errors,
    'template' => 'twitter_bootstrap',
));
echo $form->open( Url::site('users/register'), array('class' => 'form-horizontal'));
echo $form->legend(tr('Registration'));
echo $form->input(array(
    'name' => 'login',
    'label' => tr('Nickname'),
    'template' => 'input_prepend',
    'icon' => 'user',
    'attr' => array( 'class' => 'input-xlarge' ),
    'info' => tr('Your nickname')
));

echo $form->input(array(
    'name' => 'email',
    'template' => 'input_prepend',
    'char' => '<b>@</b>',
    'label' => tr('Email address'),
    'attr' => array( 'class' => 'input-xlarge'),
));

echo $form->password(array(
    'name' => 'pswd',
    'label' => tr('Password'),
    'template' => 'input_prepend',
    'icon' => 'lock',
    'attr' => array( 'class' => 'input-xlarge'),
    'info' => tr('Must be at least 6 characters long')
));

echo $form->password(array(
    'name' => 'pswd_confirmation',
    'label' => tr('Password confirmation'),
    'template' => 'input_prepend',
    'icon' => 'lock',
    'attr' => array( 'class' => 'input-xlarge'),
    'info' => tr('Must be the same as password')
));

echo $form->form_actions(array(
    'buttons' => array(
        array('submit', tr('Register'), array( 'class' => 'btn btn-primary', 'type' => 'submit'))
    )
));
echo $form->close();
return;
?>
</div>