<?php defined('DOCROOT') or die('No direct script access.');
/**
 * sets human readable message for external console applications
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */
class Exception_Tools extends Error {

    protected static $custom_view_file = "errors/tools";

}