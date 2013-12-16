<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Core template wich extends functionality of Controller_Template
 *
 *
 * @package Kohana-my-base
 * @copyright 2013 pussbb@gmail.com
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
    protected $view = NULL;

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
        $this->config = Kohana::$config->load('site')->as_array();
        $this->view = new View();

        if ($this->template)
            $this->template->set(array('content' => NULL, 'meta'=> array(), 'title'=> NULL));

    }

    /**
     * get config item
     * @param $key string see Arr::path $key param
     * @param $defualt mixed default value if $key not found
     * @return mixed
     */
    protected function config_item($key, $defualt = NULL)
    {
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

    public function add_meta($key, $attr)
    {
        $this->template->meta[$key] = $attr;
    }

    /**
     * set keywords for page
     * @param $keywords
     */
    public function set_keywords($keywords)
    {
        $this->add_meta('keywords', array(
            'name' => 'keywords',
            'content' => $keywords,
        ));
    }

    /**
     * sets description for current page
     * @param $description
     */
    public function set_description($description)
    {
        $this->add_meta('description', array(
            'name' => 'description',
            'content' => $description,
        ));
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
        parent::redirect(Valid::url($url)?$url:URL::site($url), $code);
    }

    /**
     *
     * parse data and convert to appropriate format (especially objects to array)
     *
     * @param $data mixed
     * @access private
     */
    private function prepare_json_data($data)
    {
        $result = array();
        switch(gettype($data)) {
            case 'array':
                foreach($data as $key => $value) {
                    $result[$key] = $this->prepare_json_data($value);
                }
                break;
            case 'object':
                if ($data instanceof IteratorAggregate ) {
                    foreach($data as $key => $value) {
                        $result[$key] = $this->prepare_json_data($value);
                    }
                } else {
                    $result = Object::properties($data);
                }
                break;
            default:
                $result = $data;
                break;
        }
        return $result;
    }

    /**
     * send response as json response
     * @param $data mixed
     */
    public function render_json($data, $status_code = 200)
    {

        $json = json_encode($this->prepare_json_data($data), JSON_HEX_TAG);
        $this->response
            ->headers('Content-Type', 'application/json')
            ->status($status_code)
            ->send_headers()
            ->body($json);
        $this->_safety_render();
    }

    /**
     *
     * Insert SimpleXMLElement node into parent SimpleXMLElement node
     *
     * @access private
     * @param $node SimpleXMLElement
     * @param $sub_node SimpleXMLElement
     * @return void
     */
    private function append_xml_node(SimpleXMLElement $node, SimpleXMLElement $sub_node)
    {
        $toDom = dom_import_simplexml($node);
        $fromDom = dom_import_simplexml($sub_node);
        $toDom->appendChild(
            $toDom->ownerDocument->importNode($fromDom, true)
        );
    }

     /**
     * convert array into xml elements
     * @param $data array
     * @access private
     * @return SimpleXMLElement object
     */
    private function array2xml(array $data, $xml)
    {
        foreach($data as $key => $value)
        {
            $key = is_numeric($key) ? 'item' : $key;
            switch (gettype($value)) {
                case 'array':
                    if (Arr::is_assoc($value)) {
                        $sub_node = $this->array2xml($value, new SimpleXMLElement("<$key/>"));
                    }
                    else {
                        $sub_node = new SimpleXMLElement("<$key/>");
                        foreach($value as $val) {
                            $this->array2xml(array('item'=>$val), $sub_node);
                        }
                    }
                    $this->append_xml_node($xml, $sub_node);
                    break;
                case 'object':
                    $sub_node = new SimpleXMLElement("<$key/>");
                    if ($value instanceof IteratorAggregate) {
                        foreach($value as $key => $val) {
                            $this->array2xml(array($key => $val), $sub_node);
                        }
                    }
                    else {
                        $this->array2xml(Object::properties($value), $sub_node);
                    }
                    $this->append_xml_node($xml, $sub_node);
                    break;
                case 'string':
                    if ($value != strip_tags($value))
                    {
                        $node = dom_import_simplexml($xml);
                        $fromDom = dom_import_simplexml(new SimpleXMLElement("<$key/>"));
                        $fromDom->appendChild(
                            $fromDom->ownerDocument->importNode($fromDom->ownerDocument->createCDATASection($value),true)
                        );
                        $node->appendChild(
                            $node->ownerDocument->importNode($fromDom,true)
                        );
                    }
                    else {
                        $xml->addChild($key, $value);
                    }
                    break;
                default:
                    $xml->addChild($key, $value);
                    break;
            }
        }
        return $xml;
    }

    /**
     * send response as xml response
     * @param $data
     * @access public
     * @return void
     */
    public function render_xml($data, $status_code = 200)
    {
      $xml = new SimpleXMLElement('<response/>');
      $data = is_array($data) ? $data : array($data);
      $xml = $this->array2xml($data, $xml);

      $this->response
          ->headers('Content-Type', 'application/xml')//
          ->status($status_code)
          ->body($xml->asXML());
      $this->_safety_render();
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

        if ( ! $this->auto_render ) {
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
            $controller_vars['content'] = $this->view->render();
            $content = View::factory('layout/'.$this->layout, $controller_vars);
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
        $system_variables = array_merge(array(
            'auto_render',
            'template',
            'request',
            'response',
        ), $filter);
        $system_variables = array_combine($system_variables, $system_variables);
        return array_diff_key(Object::properties($this), $system_variables);
    }

    /**
     * finally set the view filename (not possible to change back)
     * @access protected
     * @return void
     */
    protected function set_view()
    {
        $file_name = $this->filename;
        if (!$this->filename) {
            $file_name = $this->request_structure();
        }
        $this->view->set_filename(Text::reduce_slashes(implode('/', $file_name)));
    }

}
