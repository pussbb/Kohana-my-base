<?php defined('SYSPATH') or die('No direct script access.');

class Date extends Kohana_Date {

    // which day does the week start on (0 - 6)
    const WEEK_START = 2;

    public static function formated($date)
    {
        $format = Kohana::$config->load('common.date');
        if ( ! $format)
        {
            $format =  "F j, Y, g:i a";
        }
        return date($format, strtotime($date));
    }

    public static function today_if_null($date = null)
    {
        return is_string($date) ? strtotime($date) : (is_int($date) ? $date : time());
    }

    public static function start_of_month($date = null)
    {
        $time = Date::today_if_null($date);
        return gmmktime(0, 0, 0, date('m', $time), 2 , date('Y', $time));
    }

    public static function month_days($date = null, $format = 'Y-m-d')
    {
        $day = Date::start_of_month($date);
        $end_of_month = Date::end_of_month($date);
        $result = array();

        while( $day < $end_of_month ) 
        {
            $result[] = date($format, $day);
            $day = $day + Date::DAY;
        }
        return $result;
    }

    public static function end_of_month($date = null)
    {
        $time = Date::today_if_null($date);
        return gmmktime(25, 0, 0, date('m', $time), date('t', $time), date('Y', $time));
    }

    public static function start_of_week($date = null)
    {
        $time = Date::today_if_null($date);
        $start = gmmktime(0, 0, 0, date('m', $time), (date('d', $time)+Date::WEEK_START)-date('w', $time), date('Y', $time));
        if($start > $time) $start -= Date::WEEK;
        return $start;
    }

    public static function end_of_week($date = null)
    {
        $time = Date::today_if_null($date);
        return Date::start_of_week($time) + Date::WEEK - 1;
    }

    public static function week_days($date = null)
    {
        $time = Date::today_if_null($date);
        $output = array();

        $startofweek = Date::start_of_week($date);
        $endofweek = Date::end_of_week($date);

        $day = $startofweek;

        while( $day < $endofweek ) {
            array_push($output, date("D", $day));
            $day = $day + Date::DAY;
        }
        return $output;
    }

}
