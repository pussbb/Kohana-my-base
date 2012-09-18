<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com(alexnevpryaga@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2 
 * @link https://github.com/pussbb/Kohana-my-base
 */

class Controller_Languages extends Controller_Core {

	protected $check_access = FALSE;

	public function action_update()
	{
		if (Kohana::$environment !== Kohana::DEVELOPMENT)
			throw new Kohana_HTTP_Exception_403();

		$base_dir = I18n::base_dir();
		Dir::create($base_dir);
		$template = $base_dir.'template.po';
		$k = exec('(find "'.DOCROOT.'" -type f  -iname "*.php" | xargs xgettext -D '.DOCROOT.' -o '.$template.' -L PHP -d="'.I18n::$domain.'" -p '.$base_dir.' --force-po --no-wrap --keyword="tr" --keyword="__" --keyword="_" --from-code="UTF-8") 2>&1');
		foreach (Model_Language::find_all()->records as $language) {
			$dir = I18n::tr_path($language->locale);
			Dir::create($dir);
			exec('msginit --no-translator --locale='.$language->locale.' --input='.$template.' -o '.$dir.I18n::$domain.'.po');
		}
		$this->render_nothing();
	}
}