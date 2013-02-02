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

        Tools_Language::parse_source();
        $this->render_nothing();
    }
}