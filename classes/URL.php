<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 */

class URL extends Kohana_URL {


    private static $lang_code = NULL;
    /**
     * generates url for site with lang code if it present in query
     * @param string $uri
     * @param mixed $protocol
     * @param bool $index
     * @static
     * @access public
     * @return string
     */
    public static function site($uri = '', $protocol = TRUE, $index = TRUE)
    {

        if ($uri && self::$lang_code) {
            if (strpos($uri, self::$lang_code.'/') === FALSE)
                $uri = self::$lang_code.'/'.$uri;
        }

        return parent::site($uri, $protocol, $index);

    }

    public static function set_lang_code($code)
    {
        if ( ! $code ) {
            self::$lang_code = NULL;
            return;
        }
        if (in_array($code, Base_Language::all_codes()))
            self::$lang_code = $code;
    }

    /**
     * generates url for site with lang code if it present in query
     * @param string $uri
     * @param mixed $protocol
     * @param bool $index
     * @static
     * @access public
     * @return string
     */
    public static function _site($uri = '', $protocol = TRUE, $index = TRUE)
    {
        return parent::site($uri, $protocol, $index);
    }

}
