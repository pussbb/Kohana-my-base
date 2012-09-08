<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class wich adds some extra functions to Date class in Kohana
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2 
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

class Base_Date extends Kohana_Date {

    /**
     *which day does the week start on (0 - 6)
     */
    const WEEK_START = 2;

    /**
     * print date in specific format(from config site)
     * @static
     * @param $date
     * @return string foremated date
     */
    public static function format($date)
    {
        $format = Kohana::$config->load('site.date_formart');
        if (!$format) {
            $format = "F j, Y, g:i a";
        }
        return date($format, strtotime($date));
    }

    /**
     * convert date to a timestamp
     *
     * if valid otherwise current timestamp
     * @static
     * @param null $date
     * @return int
     */
    public static function today_if_null($date = null)
    {
        return is_string($date) ? strtotime($date) : (is_int($date) ? $date : time());
    }

    /**
     * Get the timestamp for first day of the month
     * @static
     * @param null $date
     * @return int
     */
    public static function start_of_month($date = null)
    {
        $time = Date::today_if_null($date);
        return gmmktime(0, 0, 0, date('m', $time), 2, date('Y', $time));
    }

    /**
     * Get all days for month
     * @static
     * @param null $date
     * @param string $format
     * @return array
     */
    public static function month_days($date = null, $format = 'Y-m-d')
    {
        $day = Date::start_of_month($date);
        $end_of_month = Date::end_of_month($date);
        $result = array();

        while ($day < $end_of_month) {
            $result[] = date($format, $day);
            $day = $day + Date::DAY;
        }
        return $result;
    }

    /**
     * Get the timestamp for last day of month
     * @static
     * @param null $date
     * @return int
     */
    public static function end_of_month($date = null)
    {
        $time = Date::today_if_null($date);
        return gmmktime(25, 0, 0, date('m', $time), date('t', $time), date('Y', $time));
    }

    /**
     * Get the timestamp for first day of week
     * @static
     * @param null $date
     * @return int
     */
    public static function start_of_week($date = null)
    {
        $time = Date::today_if_null($date);
        $start = gmmktime(0, 0, 0, date('m', $time), (date('d', $time) + Date::WEEK_START) - date('w', $time), date('Y', $time));
        if ($start > $time)
            $start -= Date::WEEK;
        return $start;
    }

    /**
     * Get the timestamp for the last day of week
     * @static
     * @param null $date
     * @return int
     */
    public static function end_of_week($date = null)
    {
        $time = Date::today_if_null($date);
        return Date::start_of_week($time) + Date::WEEK - 1;
    }

    /**
     * get days of week
     * @static
     * @param null $date
     * @return array
     */
    public static function week_days($date = null)
    {
        $time = Date::today_if_null($date);
        $output = array();

        $startofweek = Date::start_of_week($date);
        $endofweek = Date::end_of_week($date);

        $day = $startofweek;

        while ($day < $endofweek) {
            array_push($output, date("D", $day));
            $day = $day + Date::DAY;
        }
        return $output;
    }

}
