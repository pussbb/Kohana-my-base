<?php defined('SYSPATH') or die('No direct script access.');
/**
 * DB Exception if model loaded
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category database
 * @subpackage exception
 */
class Base_Db_Exception_LoadedModel extends Exception {

    protected $message = "Method cannot be called on loaded objects";

    protected $code= 108;

}
