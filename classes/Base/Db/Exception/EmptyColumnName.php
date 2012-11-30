<?php defined('SYSPATH') or die('No direct script access.');
/**
 * DB Exception item not found in database table
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category database
 * @subpackage exception
 */
class Base_Db_Exception_EmptyColumnName extends Exception {

    protected $message = "Database table column must not be empty";

    protected $code= 103;

}
