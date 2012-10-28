<?php
$user = Auth::instance()->current_user();


$email = "someone@somewhere.com";
$default = "http://placehold.it/40x40";
$size = 40;

$grav_url = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $user->email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
             ?>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span2">
            <img src="<?php echo $grav_url; ?>" alt="" />
        </div>
        <div class="span10">
            <table class="table table-condensed">
            <tbody>
            <tr>
                <td><?php echo tr('Login');?></td>
                <td><?php echo $user->login;?></td>
            </tr>
            <tr>
                <td><?php echo tr('Email');?></td>
                <td><?php echo $user->email?></td>
                </tr>
            </tbody>
            </table>
        </div>
    </div>
</div>