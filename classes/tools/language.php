<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category tools
 * @subpackage tools
 */

class Tools_Language extends Tools {


    public static function parse_source()
    {
        self::check();
        $base_dir = I18n::base_dir();
        Dir::create_if_need($base_dir);
        $template = $base_dir.'template.po';
        shell_exec('(find "'.DOCROOT.'" -type f  -iname "*.php" | xargs xgettext -D '.DOCROOT.' -o '.$template.' -L PHP -d="'.I18n::$domain.'" -p '.$base_dir.' --force-po --no-wrap --keyword="tr" --keyword="__" --keyword="_" --from-code="UTF-8") 2>&1', $output);

        if ( ! $output)
            throw new Exception_Tools('parsing sources failed \n '.$output);

        File::sed($template, '/Content-Type: text\/plain; charset=CHARSET/', 'Content-Type: text/plain; charset=UTF-8');
        foreach (Model_Language::find_all()->records as $language) {
            $tr_file = I18n::absolute_file_path($language->locale);
            if ( ! file_exists($tr_file)) {
                $dir = I18n::tr_path($language->locale);
                Dir::create_if_need($dir);
                shel_exec('msginit --no-translator --locale='.$language->locale.' --input='.$template.' -o '.$tr_file , $output);
                if ( ! $output)
                    throw new Exception_Tools('init translation failed \n '.$output);
            }
            else {
                exec('msgmerge --no-wrap -U  '.$tr_file.' '.$template, $output);
                if ( ! $output)
                    throw new Exception_Tools('updating translation failed \n '.$output);
            }
        }
    }

    public static function check()
    {
        if ( ! self::can_call('shell_exec'))
            throw new Exception_Tools('Your system does not support to call shell_exec');
        if (Kohana::$is_windows)
            throw new Exception_Tools('Sorry but your platform currently not supported');
        if ( ! self::app_exists('xgettext -V', '/xgettext \(GNU gettext-tools\)/'))
            throw new Exception_Tools('Gettext tools not installed. Details http://en.wikipedia.org/wiki/Gettext');
    }
}