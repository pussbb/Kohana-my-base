
<?php
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
echo $form->open( Url::site('users/login'), array('class' => 'form-horizontal'));
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
    'attr' => array( 'class' => 'input-xlarge')
));


echo $form->form_action(array(
    'buttons' => array(
        array('submit', __('login'), array( 'class' => 'btn btn-primary', 'type' => 'submit'))
    )
));
echo $form->close();
return;
?>
<form class="form-horizontal">
    <fieldset>
        <legend>Log in </legend>
        <div class="control-group">
            <label class="control-label" for="input01">login</label>
            <div class="controls">
                <div class="input-prepend">
                    <span class="add-on">@</span><input class="span2" id="prependedInput" size="16" type="text">
                </div>
                <p class="help-block">valid email address</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="input02">password</label>
            <div class="controls">
                <input type="password" class="input-xlarge" id="input02">
                <p class="help-block">forgot password restore</p>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo __('login');?></button>
        </div>
    </fieldset>
</form>