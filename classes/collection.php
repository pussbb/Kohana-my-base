<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2 
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

class Collection {

	public static function hash(array $collection, $key)
	{
		$result = array();
		foreach ($collection as $item) {
			$result[$item->$key] = $item;
		}
		return $result;
	}

	public static function for_select(array $collection, $key, $primary_key = 'id')
	{
		$result = array();
		foreach ($collection as $item) {
			$result[$item->$primary_key] = $item->$key;
		}
		return $result;
	}

	/**
	 * Retrieves muliple single-key values from a list of object.
	 */
	public static function pluck(array $collection, $key)
	{
		$result = array();
		foreach ($collection as $item) {
			try {
				$result[] = $item->$key;
			} catch (Exception $e) {
				$result[] = NULL;
			}
		}
		return $result;
	}
}
