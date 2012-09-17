<?php defined('SYSPATH') or die('No direct script access.');

class Base_Language {
    public static function set($language)
    {
        Session::instance()->set('language', $language);
    }

    public static function get()
    {
        $language = Session::instance()->get('language');
        return $language ?: self::get_default();
    }

    public static function get_default()
    {
    	$code = Kohana::$config->load('site.default_language');
    	if ($code)
    		$filter = array('code' => $code);
    	else
    		$filter = array('locale' => I18n::lang());
        $language = Model_Language::find($filter);
        Session::instance()->set('language', $language);
        return $language;
    }
}