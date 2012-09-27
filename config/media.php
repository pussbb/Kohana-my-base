<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'core' => array(
        'static_uri' => 'uri',
        'uri' => 'media/',
        'path' => DOCROOT.'media'.DIRECTORY_SEPARATOR,
    ),
    'default' => array(
        'css' => array('1' => ''),
        'js' => array('jquery/jquery.min',),
    ),
);
/*
Example
'default' => array(
        'css' => array(
            'bootstrap/bootstrap.min' => '',
            'bootstrap/bootstrap-responsive.min' => '',
            'main' => '',
        ),
        'js' => array(
            'jquery/jquery.min',
            'bootstrap/bootstrap.min',
            array(
                'name' => 'ui_lib',
                'files' => array(
                    'lib/pseudo_ajax_load_progress',
                    'lib/inline_alert',
                ),
            ),
        ),
    ),

*/