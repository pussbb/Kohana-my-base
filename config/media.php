<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'core' => array(
        'static_uri' => 'static.local',
        'uri' => 'media/',
        'path' => DOCROOT.'media'.DIRECTORY_SEPARATOR,
        'default_position' => Base_Media::POSITION_HEAD,
    ),
);
/*
Example
'default' => array(
        'depends' => 'somebundle1, anotherbundle' or array('somebundle1'...)
        'css' => array(

            'bootstrap/bootstrap.min' => 'screen', //media_query
            'bootstrap/bootstrap-responsive.min' =>  array(
                'media' => 'screen',
                'type' => 'text/css',
            ),
            'main' =>  array(
                'media' => 'screen',
                'type' => 'text/css',
                'files' => array( list of files )
            ),,
        ),
        'js' => array(
            'jquery/jquery.min',
            'bootstrap/bootstrap.min',
            'ui_lib' => array(
                'files' => array(
                    'lib/pseudo_ajax_load_progress',
                    'lib/inline_alert',
                    ),
            ),
        ),
    ),

*/
