<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'coffeescript' => array(
        'source_path' => DOCROOT.'coffee_scripts'.DIRECTORY_SEPARATOR,
        'dest_path' => DOCROOT.'media'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR,
    ),
    'less' => array(
        'source_path' => DOCROOT.'less'.DIRECTORY_SEPARATOR,
        'dest_path' => DOCROOT.'media'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR
    ),
    'eightpack' => array(
        'jsmin' => '/opt/eightpack_bin/jsmin  --aggressive '
    )
);