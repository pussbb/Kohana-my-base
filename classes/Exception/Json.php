<?php defined('DOCROOT') or die('No direct script access.');
/**
 * sets human readable message for json_last_error
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category exceptions
 * @subpackage json
 */

class Exception_Json extends Exception {
    /**
     * sets human readable message for json_last_error
     * @ignore
     * @constructor
     * @access public
     * @return void
     */
    public function __construct($message , $code)
    {
        switch($code)
        {
          case JSON_ERROR_NONE:
              $message = ' - no errors';
              break;
          case JSON_ERROR_DEPTH:
              $message = ' Reached the maximum depth of the stack';
              break;
          case JSON_ERROR_STATE_MISMATCH:
              $message = ' Underflow or the modes mismatch';
              break;
          case JSON_ERROR_CTRL_CHAR:
              $message = ' Unexpected control character found';
              break;
          case JSON_ERROR_SYNTAX:
              $message = ' Syntax error, malformed JSON';
              break;
          case JSON_ERROR_UTF8:
              $message = ' Malformed UTF-8 characters, possibly incorrectly encoded';
              break;
          default:
              $message = ' Unknown error';
              break;
        }

        parent::__construct($message);
    }

}


