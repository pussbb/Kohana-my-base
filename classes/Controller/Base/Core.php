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

class Controller_Base_Core extends Controller_Template {

    /**
     * if request is XMLHttpRequest and controller whants to render view
     * if this option enabled it will render only view. otherwise without manualy setting
     * render mode it will render with template files
     *
     *@var bool
     */
    protected $ajax_auto_partial = TRUE;

     /**
     * base template file
     *
     * but remember, in parent class it will become View object
     * @var string
     */
    public $template = 'template/core';

    /**
     * for children it's recommended to change this prefixes
     * if needed IN CONSTRUCTOR (to not damage changes in parent class)
     * @var array
     */

    protected $bundles = array('');

    /**
     * Kohana View object
     * @var null
     */
    public $view = NULL;

    /**
     * set view file name wich will be rendered in template section 'content'
     * @var null
     */
    private $filename = NULL;

    /**
     * set layout name
     * @var null
     */
    protected $layout = NULL;

    /**
     * array with settings from config 'site.php'
     * @var null
     */
    private $config = NULL;


    /**
     * init base template and this
     */
    public function before()
    {
        parent::before();
        $this->view = new View();
        if ($this->template)
            $this->template->set(array('content' => NULL, 'keywords'=> NULL, 'description'=> NULL, 'title'=> NULL));
    }

    /**
     * get config item
     * @param $key string see Arr::path $key param
     * @param $defualt mixed default value if $key not found
     * @return mixed
     */
    private function config_item($key, $defualt = NULL)
    {
        if ( ! $this->config)
            $this->config = Kohana::$config->load('site');
        return Arr::path($this->config, $key, $defualt);
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
        $default_title = $this->config_item('title');
        $delimiter = $title ? ' | ' : '';
        if ($default_title) {
            $title .= $delimiter . $default_title;
        }
        $this->template->title = URL::title($title);
    }

    /**
     * returns request in array(dir, controller, action)
     * @return array
     */
    public function request_structure()
    {
        return array_filter(array(
                    strtolower($this->request->directory()),
                    strtolower($this->request->controller()),
                    strtolower($this->request->action()),
                ));
    }

    /**
     * append css file
     * @param $name
     * @param string $media
     */
    public function append_css($name, $media = '')
    {
        Base_Media::instance()->append_style($name, $media);
    }

    /**
     * append javascript file
     * @param $name
     */
    public function append_js($name)
    {
        Base_Media::instance()->append_script($name);
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
    public function append_media($file_name, $media = NULL, $check_file = TRUE)
    {
        Base_Media::instance()->append(array('css', 'js'), $file_name, $media, $check_file);
    }

    /**
     * register css and js for current request structure
     *
     * tries to add default files
     * in format directory/controller.action.js
     * and directory/controller.action.css
     * @access private
     * @return void
     */
    private function media_by_default()
    {
        $structure = $this->request_structure();
        $directory = NULL;
        if (isset($structure[0])) {
            $directory = array_shift($structure) . '/';
        }
        $file_name = $directory . implode('.', $structure);
        Base_Media::instance()->append(array('css', 'js'), $file_name, NULL, TRUE);
    }

    /**
     * checks if request method is DELETE
     * @return bool
     * @access public
     */
    public function is_delete()
    {
        return $this->request->method() === HTTP_Request::DELETE;
    }

    /**
     * checks if request is ajax
     * @return mixed
     * @access public
     */
    public function is_ajax()
    {
        return $this->request->is_ajax();
    }

    /**
     * checks if request method is PUT
     * @return bool
     * @access public
     */
    public function is_put()
    {
        return $this->request->method() === HTTP_Request::PUT;
    }

    /**
     * checks if request method is POST
     * @return bool
     * @access public
     */
    public function is_post()
    {
        return $this->request->method() === HTTP_Request::POST;
    }

    /**
     * checks if request method is GET
     * @return bool
     * @access public
     */
    public function is_get()
    {
        return $this->request->method() === HTTP_Request::GET;
    }

    /**
     * renders only view file name without template
     * @param string $file
     * @param array $view_data
     * @return mixed
     */
    public function render_partial($file = '',array $view_data = array())
    {
        $this->set_filename($file);
        $this->view->set(array_merge($view_data, $this->dynamic_properties()));
        $this->set_view();
        $this->response->body($this->view->render());
        $this->_safety_render();
    }

    /**
     * renders nothing
     */
    public function render_nothing()
    {
       $this->response->body('');
       $this->_safety_render();
    }

    /**
     * redirects to url without rendering anything
     * @param $url
     * @param int $code
     */
    public static function redirect($url = '', $code = 302)
    {
        parent::redirect(URL::site($url), $code);
    }

    /**
     * send response as json response
     * @param $data
     */
    public function render_json($data, $status_code = 200)
    {

        $json = json_encode($data, JSON_HEX_TAG);
        $this->response
            ->headers('Content-Type', 'application/json')
            ->status($status_code)
            ->send_headers()
            ->body($json);
        exit(0);
        //$this->_safety_render();
    }

    /**
     * disable template render and call final method of parent class
     * @return void
     * @access private
     */
    private function _safety_render()
    {
        $this->auto_render = FALSE;
        parent::after();
    }

    /**
     * set layout name
     * @param string $name - relative path to the view file
     * @return void
     * @access public
     */
    public function set_layout($name)
    {
        $this->layout = $name;
    }

    /**
     * final function of the template
     * @return void
     * @access public
     */
    public function after()
    {

        if ( ! $this->auto_render) {
            parent::after();
            return;
        }

        if ($this->ajax_auto_partial
            && $this->request->is_ajax())
            return $this->render_partial();

        $this->set_view();

        foreach ($this->bundles as $bundle) {
            Base_Media::instance()->bundle($bundle);
        }

        $this->set_favicon($this->config_item('favicon'));
        $this->media_by_default();
        $controller_vars = $this->dynamic_properties();
        $this->view->set($controller_vars);
        if ($this->layout) {
            $content = View::factory('layout/'.$this->layout,
                array_merge(array('content' => $this->view->render()), $controller_vars));
        }
        else {
            $content = $this->view;
        }

        $this->template->content = $content->render();
        parent::after();
    }

    /**
     * append to view and tempalate dynamically append variable from $this
     * @param array $filter
     * @return array
     * @access protected
     */
    protected function dynamic_properties(array $filter = array())
    {

        $reflection_object = new ReflectionClass($this);
        $properties = $reflection_object->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
        $system_variables = array_merge(array(
            'kohana_view_filename',
            'kohana_view_data',
            'filename',
            'layout',
            'config'
        ), $filter);

        foreach ($properties as $property) {
            $system_variables[] = $property->getName();
        }

        $result = array();
        foreach (get_object_vars($this) as $key => $value) {
            if (in_array($key, $system_variables))
                continue;
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * finally set the view filename (not possible to change back)
     * @access protected
     * @return void
     */
    protected function set_view()
    {
        if (!$this->filename) {
            $this->set_filename(Text::reduce_slashes(implode('/', $this->request_structure())));
        }
        $this->view->set_filename(Text::reduce_slashes(implode('/', $this->filename)));
    }

}
