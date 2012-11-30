<?php defined('DOCROOT') or die('No direct script access.');
/**
 * sets human readable message for external console applications
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category exceptions
 * @subpackage tools
 */
class Exception_Tools extends Exception {

    public function __construct($message = "",  $code = 0, Exception $previous = NULL)
    {
        Error::$custom_view_file = "errors/tools";
        Error::handler(new Exception($message, $code, $previous));
        Error::$custom_view_file = NULL;
    }
}
