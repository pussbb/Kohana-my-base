<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 */

class URL extends Kohana_URL {

  public static function site($uri = '', $protocol = TRUE, $index = TRUE)
  {
    if ($uri && (is_object(Request::current()) && Request::current()->param('lang')))
      $uri = Language::get()->code.'/'.$uri;

    return parent::site($uri, $protocol, $index);
    
  }
}