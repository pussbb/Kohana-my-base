
<div class="reg-form">
<?php
debug($errors);
$general = Arr::get($errors, 'general');

if ( $general)
{
    echo '<div class="alert alert-error">
        <a class="close" data-dismiss="alert" href="#">×</a>
                <h4 class="alert-heading">'.__('warning').'!</h4>
                '.$general.'
                </div>';
}

$form = new Pretty_Form(array(
    'errors' => $errors,
    'template' => 'twitter_bootstrap',
));
echo $form->open( Url::site('user/register'), array('class' => 'form-horizontal'));
echo $form->legend(__('registration'));
echo $form->input(array(
    'name' => 'user_name',
    'label' => 'login',
    'attr' => array( 'class' => 'input-xlarge' ),
    'info' => __('your_login')
));

echo $form->input(array(
    'name' => 'email',
    'template' => 'input_for_mail',
    'label' => __('email_address'),
    'attr' => array( 'class' => 'input-xlarge'),
    'info' => __('valid_email_adrress')
));

echo $form->password(array(
    'name' => 'pswd',
    'label' => __('password'),
    'attr' => array( 'class' => 'input-xlarge'),
    'info' => __('at_least_6_characters')
));

echo $form->password(array(
    'name' => 'pswd_confirmation',
    'label' => __('password_confirmation'),
    'attr' => array( 'class' => 'input-xlarge'),
    'info' => __('at_least_6_characters')
));

echo $form->checkbox(array(
    'name' => 'terms_of_use',
    'label' => __('email_address'),
    'attr' => array(),
    'info' => __('valid_email_adrress')
));

echo $form->form_action(array(
    'buttons' => array(
        array('submit', __('register'), array( 'class' => 'btn btn-primary', 'type' => 'submit'))
    )
));
echo $form->close();
return;
?>
</div>