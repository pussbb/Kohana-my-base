<?php defined('SYSPATH') or die('No direct script access.');

class HTML extends Kohana_HTML {

    public static function img($image_url, $attributes = array())
    {
        if (strpos($image_url, '://') === FALSE)
        {
            $image_url = Url::site_root() . '/media/' . $image_url;
        }
        $attributes['src'] = $image_url;
        array_unshift($attributes, 'img');
        return self::tag($attributes);
    }
}
