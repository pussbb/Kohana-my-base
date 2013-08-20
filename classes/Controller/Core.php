<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Core template wich extends functionality of Controller_Template
 *
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com(alexnevpryaga@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category template
 * @subpackage template
 * @see Controller_Base_Core
 */

class Controller_Core extends Controller_Base_Core {

    /**
     *  check Acl access (true means do the check)
     * @var bool
     */
    protected $check_access = TRUE;

    /**
     * sets language by default
     * @return void
     * @access protected
     */
    protected function set_language()
    {
        $lang = $this->request->param('lang');
        URL::set_lang_code($lang);
        $language = Language::get($lang);
        Language::set($language);
        Gettext::lang($language->locale);
    }

    /**
     * init base template and this
     */
    public function before()
    {
        parent::before();
        $this->set_language();
    }

    /**
     * check access for current request
     * @throws HTTP_Exception_403
     */
    protected function check_access()
    {
        if ( ! $this->request->is_initial()
              || ! $this->check_access
              || Acl::instance()->allowed($this->request_structure()))
            return TRUE;

        if ( ! Auth::instance()->logged_in() && ! $this->request->is_ajax()){
            Cookie::set('auth_required_url', $this->request->url().http_build_query($this->request->query()) );
            return self::redirect($this->config_item('user_login_uri'));
        }
        else {
            throw new HTTP_Exception_403(tr('Access deny'));
        }

    }

}
