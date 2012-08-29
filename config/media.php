<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'core' => array(
        'static_uri' => 'uri',
        'uri' => 'media/',
        'path' => DOCROOT.'media'.DIRECTORY_SEPARATOR,
        'coffeescript' => array(
            'source_path' => DOCROOT.'coffee_scripts'.DIRECTORY_SEPARATOR,
            'dest_path' => DOCROOT.'media'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR,
            'minify' => TRUE, // uses php class JSMin
        ),
        'less' => array(
            'source' => DOCROOT.'coffee_scripts'.DIRECTORY_SEPARATOR,
            'dest' => DOCROOT.'media'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR
        ),
    ),
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
);