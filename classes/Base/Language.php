<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class to manipulate with user Lang
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */
class Base_Language {

    /**
     * set current lang
     * @param $language (model object)
     * @static
     */
    public static function set($language)
    {
        Session::instance()->set('language', $language);
    }
    /**
     * get current lang
     * @param mixed $lang
     * @return language (model object)
     * @static
     */
    public static function get($lang = NULL)
    {
        $language = Session::instance()->get('language');
        if ( ! is_object($language) && ! $lang) {
            return self::get_default();
        }
        elseif ( ! is_object($language) ||
          (is_object($language) && ($lang && $language->code != $lang))) {
            try {
                return self::get_lang($lang);
            } catch(Exception $e) {
            }
        }
        return $language;
    }
    /**
     * get default lang
     * @return default language (model object)
     * @static
     */
    public static function get_default()
    {
        $code = Kohana::$config->load('site.default_language');
        return self::get_lang($code);
    }

    /**
     * get lang
     * @param string $code language code
     * @return language (model object)
     * @static
     */
    private static function get_lang($code)
    {
        if ($code)
            $filter = array('code' => $code);
        else
            $filter = array('locale' => I18n::lang());
        $language = Model_Language::find($filter);
        self::set($language);
        return $language;
    }

    /**
     * get all available languages code
     * @return array
     * @static
     */
    public static function all_codes()
    {
        $langs = Model_Language::find_all(array(), NULL, NULL, TRUE)->records;
        return array_filter(Collection::pluck($langs, 'code'));
    }

    /**
     * php reg expr to match lang codes
     * @return array
     * @static
     */
    public static function uri_check_codes()
    {
        return '('.implode('|', self::all_codes()).')';
    }
}
