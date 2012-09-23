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


    protected function parse_source()
    {
        self::check();
        $base_dir = I18n::base_dir();
        Dir::create_if_need($base_dir);
        $template = $base_dir.'template.po';
        $ok = $this->exec('(find "'.DOCROOT.'" -type f  -iname "*.php" | xargs xgettext -D '.DOCROOT.' -o '.$template.' -L PHP -d="'.I18n::$domain.'" -p '.$base_dir.' --force-po --no-wrap --keyword="tr" --keyword="__" --keyword="_" --from-code="UTF-8") 2>&1');

        if ( ! $ok)
            throw new Exception_Tools('parsing sources failed \n '.$this->error());

        File::sed($template, '/Content-Type: text\/plain; charset=CHARSET/', 'Content-Type: text/plain; charset=UTF-8');
        foreach (Model_Language::find_all()->records as $language) {
            $tr_file = I18n::absolute_file_path($language->locale);
            if ( ! file_exists($tr_file)) {
                $dir = I18n::tr_path($language->locale);
                Dir::create_if_need($dir);
                $ok = $this->exec('msginit --no-translator --locale='.$language->locale.' --input='.$template.' -o '.$tr_file);
                if ( ! $ok)
                    throw new Exception_Tools('init translation failed \n '.$this->error());
            }
            else {
                $ok = $this->exec('msgmerge --no-wrap -U  '.$tr_file.' '.$template);
                if ( ! $ok)
                    throw new Exception_Tools("updating translation failed \n ".$this->error());
            }
        }
    }

    public static function check()
    {
        if ( ! self::app_exists('xgettext -V', '/xgettext \(GNU gettext-tools\)/'))
            throw new Exception_Tools('Gettext tools not installed. Details http://en.wikipedia.org/wiki/Gettext');
    }
}