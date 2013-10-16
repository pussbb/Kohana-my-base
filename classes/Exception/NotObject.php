<?php defined('DOCROOT') or die('No direct script access.');
/**
 * not object
 *
 * @package Kohana-my-base
 * @copyright 2013 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category exceptions
 */
class Exception_NotObject extends Exception {

   protected $message = "Variable must be an object, something else was given";

}
