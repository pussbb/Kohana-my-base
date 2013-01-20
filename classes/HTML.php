<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 */

class HTML extends Kohana_HTML {


        /**
         * Creates a image link.
         *
         *     echo HTML::img('media/img/logo.png', array('alt' => 'My Company'));
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
}
