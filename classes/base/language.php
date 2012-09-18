<?php defined('SYSPATH') or die('No direct script access.');

class Base_Language {
    public static function set($language)
    {
        Session::instance()->set('language', $language);
    }

    public static function get()
    {
        $language = Session::instance()->get('language');
        return $language ?: self::get_default();
    }

    public static function get_default()
    {
    	$code = Kohana::$config->load('site.default_language');
    	if ($code)
    		$filter = array('code' => $code);
    	else
    		$filter = array('locale' => I18n::lang());
        $language = Model_Language::find($filter);
        Session::instance()->set('language', $language);
        return $language;
    }

    public static function parse_source()
    {
        $base_dir = I18n::base_dir();
        Dir::create_if_need($base_dir);
        $template = $base_dir.'template.po';
        $k = exec('(find "'.DOCROOT.'" -type f  -iname "*.php" | xargs xgettext -D '.DOCROOT.' -o '.$template.' -L PHP -d="'.I18n::$domain.'" -p '.$base_dir.' --force-po --no-wrap --keyword="tr" --keyword="__" --keyword="_" --from-code="UTF-8") 2>&1');
        File::sed($template, '/Content-Type: text\/plain; charset=CHARSET/', 'Content-Type: text/plain; charset=UTF-8');
        foreach (Model_Language::find_all()->records as $language) {
            $tr_file = I18n::absolute_file_path($language->locale); 
            if ( ! file_exists($tr_file)) {
                $dir = I18n::tr_path($language->locale);
                Dir::create_if_need($dir);
                exec('msginit --no-translator --locale='.$language->locale.' --input='.$template.' -o '.$tr_file );
            }
            else {
                exec('msgmerge --no-wrap -U  '.$tr_file.' '.$template);
            }
        }
    }
}