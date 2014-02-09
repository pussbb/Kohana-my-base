<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

class HTML extends Kohana_HTML {


        /**
         * Creates a image link.
         *
         *     echo HTML::image('img/logo.png', array('alt' => 'My Company'));
         *
         * @param   string  $file       file name
         * @param   array   $attributes default attributes
         * @param   mixed   $protocol   protocol to pass to URL::base()
         * @param   boolean $index      include the index page
         * @return  string
         * @uses    URL::base
         * @uses    HTML::attributes
         */
        public static function image($file, array $attributes = NULL, $protocol = TRUE, $index = FALSE)
        {
            if (strpos($file, '://') === FALSE)
            {
                    $file = URL::_site('media/'.$file, $protocol, $index);
            }
            return parent::image($file, $attributes, $protocol, $index);
        }


    /**
     * Creates a script link.
     *
     *     echo HTML::script('media/js/jquery.min.js');
     *
     * @param   string  $file       file name
     * @param   array   $attributes default attributes
     * @param   mixed   $protocol   protocol to pass to URL::base()
     * @param   boolean $index      include the index page
     * @return  string
     * @uses    URL::base
     * @uses    HTML::attributes
     */
    public static function script($file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
    {
        if (strpos($file, '://') === FALSE)
        {
            // Add the base URL
            $file = URL::site($file, $protocol, $index);
        }

        // Set the script link
        $attributes['src'] = $file;

        // Set the script type
        if ( ! isset($attributes['type']) )
            $attributes['type'] = 'text/javascript';

        return '<script'.HTML::attributes($attributes).'></script>';
    }
}
