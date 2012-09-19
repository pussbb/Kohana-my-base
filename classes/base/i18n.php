<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class adds extra functionality to Kohana_I18n to support tr from gettext
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2 
 * @link https://github.com/pussbb/Kohana-my-base
 * @category template
 * @subpackage template
 */
class Base_I18n extends Kohana_I18n {

	/**
	 * @var  string   target language: en-us, es-es, zh-cn, etc
	 */
	public static $lang = 'en-EN';

	/**
	 * @var  string   target language: en-us, es-es, zh-cn, etc
	 */
	public static $domain = 'my_site';

	/**
	 * @var  string   target language: en-us, es-es, zh-cn, etc
	 */
	public static $encoding = 'UTF-8';

	/**
	 * set language
	 */
	public static function lang($lang = 'en-EN')
	{
		$lang = parent::lang($lang);
		setlocale (LC_ALL, $lang.'.'.I18n::$encoding);
		bindtextdomain (I18n::$domain, self::base_dir());
		textdomain (I18n::$domain);
	    bind_textdomain_codeset(I18n::$domain, I18n::$encoding);
	    return $lang;
	}

	public static function tr_path($lang = 'en-EN')
	{
		$pieces = array(
			'locale',
			Arr::get(explode('-', $lang), 0),
			'LC_MESSAGES'
		);
		return DOCROOT.implode(DIRECTORY_SEPARATOR, $pieces).DIRECTORY_SEPARATOR;
	}

	public static function base_dir()
	{
		return DOCROOT.'locale'.DIRECTORY_SEPARATOR;
	}

	public static function absolute_file_path($lang = 'en-EN', $ext = 'po')
	{
		return self::tr_path($lang).self::$domain.'.'.$ext;
	}

	public static function gettext_enabled()
	{
		return function_exists('gettext');
	}
}
