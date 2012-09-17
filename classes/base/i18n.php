<?php defined('SYSPATH') or die('No direct script access.');

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
	 * 
	 */
	public static function lang($lang = 'en-EN')
	{
		$lang = parent::lang($lang);
		setlocale (LC_ALL, $lang.'.'.I18n::$encoding);
		bindtextdomain (I18n::$domain, self::base_dir());
		textdomain (I18n::$domain);
	    bind_textdomain_codeset(I18n::$domain, I18n::$encoding);
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
}
