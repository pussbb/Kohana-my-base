<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Parse php files for translatable items and creates or update tr files
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category tools
 * @subpackage tools
 */

class Tools_Language extends Tools {

    /**
     * parsing php files for translatable items and creates or update tr files
     */
    protected function parse_source()
    {
        self::check();
        $base_dir = Gettext::base_dir();
        Dir::create_if_need($base_dir);
        $template = $base_dir.'template.po';
        $ok = $this->exec('(find "'.DOCROOT.'" -type f  -iname "*.php" | xargs xgettext -D '.DOCROOT.' -o '.$template.' -L PHP -d="'.Gettext::$domain.'" -p '.$base_dir.' --force-po --no-wrap --keyword="tr" --from-code="UTF-8") 2>&1');

        if ( ! $ok)
            throw new Exception_Tools('parsing sources failed \n '.$this->error());

        File::sed($template, '/Content-Type: text\/plain; charset=CHARSET/', 'Content-Type: text/plain; charset=UTF-8');
        foreach (Model_Language::find_all()->records as $language) {
            $tr_file = Gettext::absolute_file_path($language->locale);
            if ( ! file_exists($tr_file)) {
                $dir = Gettext::tr_path($language->locale);
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

    /**
     * compile gettext source translations files
     */
    protected function compile_translations()
    {
        self::check();
        foreach (Model_Language::find_all()->records as $language)
        {
            $tr_file = Gettext::absolute_file_path($language->locale);
            if ( ! file_exists($tr_file))
                continue;

            $output = Gettext::tr_path($language->locale).Gettext::$domain.'.mo';
            $ok = $this->exec("msgfmt -cv -o $output $tr_file");
            if ( ! $ok)
                throw new Exception_Tools("compiling translation failed \n ".$this->error());
        }
    }

    /**
     * checks if gettext tools is installed
     * @static
     * @throw Exception_Tools
     */
    public static function check()
    {
        if ( ! self::app_exists('xgettext -V', '/xgettext \(GNU gettext-tools\)/'))
            throw new Exception_Tools_Missing('Gettext tools not installed. Details http://en.wikipedia.org/wiki/Gettext');
    }
}