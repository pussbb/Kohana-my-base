<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'coffeescript' => array(
        'source_path' => 'coffee_scripts',
        'dest_path' => DOCROOT.'media'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR,
    ),
    'less' => array(
        'source_path' => 'less',
        'dest_path' => DOCROOT.'media'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR
    ),
    'eightpack' => array(
        'jsmin' => '/opt/eightpack_bin/jsmin  --aggressive ',
        'cssmin' => '/opt/eightpack_bin/cssmin ',
        'cssbeautify' => '/opt/eightpack_bin/cssbeautify ',
        'jsbeautify' => '/opt/eightpack_bin/jsbeautify '
    )
);