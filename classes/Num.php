<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * get form http://pastie.org/981503
 *
 *
 * @category extra
 * @subpackage extra
 */
class Num extends Kohana_Num {

    /**
     * Returns a human readble number
     *
     * @param  int    a number
     * @param  int    thousands
     * @return string
     */
    public static function human($val, $thousands = 0)
    {
        if($val >= 1000)
        {
            $val = Num::human($val / 1000, ++$thousands);
        }
        else
        {
            $unit = array('','k','mil','t','p','e','z','y');
            $val = round($val,2) . ' ' . $unit[$thousands];
        }
        return $val;
    }
}
