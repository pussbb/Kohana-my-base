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
 */

class Controller_Core extends Controller_Template {

     /**
     * base template file
     *
     * but remember, in parent class it will become View object
     * @var string
     */
    public $template = 'layout/template';

    /**
     * for children it's recommended to change this prefixes
     * if needed IN CONSTRUCTOR (to not damage changes in parent class)
     * @var array
     */
    protected $bundles = array('');

    /**
     *  check Acl access (true means do the check)
     * @var bool
     */
    protected $check_access = TRUE;
    /**
     * Kohana View object
     * @var null
     */
    public $view = null;

    /**
     * @var null
     */
    private $filename = null;

    /**
     * sets language by default
     * @return void
     * @access protected
     */
    protected function set_language()
    {
        $lang = $this->request->param('lang');
        $language = Language::get($lang)->locale;
        GetText::lang($language);
    }

    /**
     * init base template and this
     */
    public function before()
    {
        parent::before();
        $this->set_language();
        $this->check_access();

        $this->view = new View();
        $this->template->set(array('content' => NULL, 'keywords'=> NULL, 'description'=> NULL, 'title'=> NULL));
    }

    /**
     * sets current file name wich will be render
     * @param $filename
     */
    public function set_filename($filename)
    {
        if (!$filename)
            return;
        $this->filename = explode('/', $filename);
    }

    /**
     * check access for current request
     * @throws HTTP_Exception_403
     */
    protected function check_access()
    {
        if (!$this->request->is_initial() ||
                !$this->check_access ||
                Acl::instance()->allowed($this))
            return;

        if ($this->request->is_ajax())
            throw new HTTP_Exception_403(__('access_deny'));

        if (!Auth::instance()->logged_in()) {
            $this->redirect('users/login');
            return;
        }
        else {
            throw new HTTP_Exception_403(__('access_deny'));
        }
    }

    /**
     * set keywords for page
     * @param $keywords
     */
    public function set_keywords($keywords)
    {
        $this->template->keywords = $keywords;
    }

    /**
     * sets description for current page
     * @param $description
     */
    public function set_description($description)
    {
        $this->template->description = $description;
    }

    /**
     * sets favicon
     * @param $icon_name
     */
    public function set_favicon($icon_name)
    {
        $this->template->favicon = $icon_name;
    }

    /**
     * sets title for current page
     * @param $title
     */
    public function set_title($title)
    {
        if ($this->template->title && !$title)
            return;
        $default_title = Kohana::$config->load('site.title');
        $delimiter = $title ? ' | ' : '';
        if ($default_title) {
            $title .= $delimiter . $default_title;
        }
        $this->template->title = $title;
    }

    /**
     * returns request in array(dir, controller, action)
     * @return array
     */
    public function current_request_structure()
    {
        return array_filter(array(
                    $this->request->directory(),
                    $this->request->controller(),
                    $this->request->action(),
                ));
    }

    /**
     * append css file
     * @param $name
     * @param string $media
     */
    public function append_css($name, $media = '')
    {
        Media::append_style($name, $media);
    }

    /**
     * append javascript file
     * @param $name
     */
    public function append_js($name)
    {
        Media::append_script($name);
    }

    /**
     * try to append css and js for file
     *
     * <code>
     *  $this->add_media('welcome.index');
     * </code>
     * will append
     * ./css/welcome.index.css
     * ./js/welcome.index.js
     * @param $file_name
     * @param null $media
     * @param bool $check_file
     */
    public function append_media($file_name, $media = NULL, $check_file = FALSE)
    {
        Media::append(array('css', 'js'), $file_name, $media, $check_file);
    }

    /**
     * register css and js for current request structure
     *
     * tries to add default files
     * in format directory/controller.action.js
     * and directory/controller.action.css
     * @param array $request_struct
     */
    private function media_by_default($request_struct = array())
    {
        $structure = $this->current_request_structure();
        $directory = NULL;
        if (Arr::get($structure, 0)) {
            $directory = array_shift($structure) . '/';
        }
        $file_name = $directory . implode('.', $structure);
        $this->append_media($file_name, NULL, TRUE);
    }

    /**
     * checks if request method is DELETE
     * @return bool
     */
    public function is_delete()
    {
        return $this->request->method() === 'DELETE';
    }

    /**
     * checks if request is ajax
     * @return mixed
     */
    public function is_ajax()
    {
        return $this->request->is_ajax();
    }

    /**
     * renders only view file name without template
     * @param string $file
     * @param array $view_data
     * @return mixed
     */
    public function render_partial($file = '', $view_data = array())
    {
        $this->check_auto_render();
        $this->auto_render = FALSE;
        $this->set_filename($file);
        if ($view_data)
            $this->view->set($view_data);
        $this->append_dynamic_properties($this->view);
        $this->set_view_filename();
        return $this->response->body($this->view->render());
    }

    /**
     * renders nothing
     */
    public function render_nothing()
    {
        $this->render_partial('core/empty');
    }

    /**
     * redirects to url without rendering anything
     * @param $url
     * @param int $code
     */
    public function redirect($url, $code = 302)
    {
        $this->check_auto_render();
        $this->auto_render = FALSE;
        $this->request->redirect(URL::site($url), $code);
    }

    /**
     * send response as json response
     * @param $data
     */
    public function render_json($data)
    {
        $this->check_auto_render();
        $this->auto_render = FALSE;
        $json = json_encode($data, JSON_HEX_TAG);
        $this->response->headers('Content-Type', 'application/json')
                ->send_headers()
                ->body($json);
    }

    /**
     * checks if auto_render already false,
     *
     * it means you tried to render several views in one action
     * @throws Kohana_Exception
     */
    protected function check_auto_render()
    {
        if (!$this->auto_render)
            throw new Kohana_Exception("You have to render something (or redirect) only once per action");
    }

    /**
     * after
     */
    public function after()
    {
        if (!$this->auto_render) {
            parent::after();
            return;
        }

        $this->set_view_filename();

        foreach ($this->bundles as $bundle) {
            Media::bundle($bundle);
        }

        //set favicon (name from config: site.favicon)
        $favicon = Kohana::$config->load('site.favicon', '');
        if ($favicon) {
            $this->set_favicon($favicon);
        }

        $this->media_by_default();
        $this->append_dynamic_properties($this->view);
        $this->template->content = $this->view->render();
        parent::after();
    }

    /**
     * append to view and tempalate dynamically append variable from $this
     * @param $view
     */
    private function append_dynamic_properties($view)
    {
        $reflection_object = new ReflectionClass($this);
        $properties = $reflection_object->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
        $system_variables = array('kohana_view_filename', 'kohana_view_data', 'filename');
        foreach ($properties as $property) {
            $system_variables[] = $property->getName();
        }

        foreach (get_object_vars($this) as $key => $value) {
            if (in_array($key, $system_variables))
                continue;
            $view->bind($key, $value);
        }
    }

    /**
     * finally set the view filename (not possible to change back)
     */
    protected function set_view_filename()
    {
        if (!$this->filename) {
            // i know it's bad, but we have to change $this->filename
            // in unified way

            $this->set_filename(
                    implode('/', $this->current_request_structure())
            );
        }
        $this->view->set_filename(implode('/', $this->filename));
    }

}
