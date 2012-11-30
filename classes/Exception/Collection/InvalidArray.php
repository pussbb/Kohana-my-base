<?php defined('DOCROOT') or die('No direct script access.');
/**
 *
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category exceptiopns
 * @subpackage collection
 */
class Exception_Collection_InvalidArray extends Exception {

   protected $message = "Variable must be array, something else was given";

   protected $code= 204;

}
