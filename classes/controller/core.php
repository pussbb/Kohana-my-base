<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Core extends  Controller_Template{

    // base template file
    // but remember, in parent class it will become View object
    public $template = 'layout/template';
    // for children it's recommended to change this prefixes
    // if needed IN CONSTRUCTOR (to not damage changes in parent class)
    protected $resource_prefixes = array('default');
    // check Acl access (true means do the check)
    protected $check_access = true;
    
    public $view = null;
    // contains null or array with [directory/]controller/action parts
    // gathered in string in after() method
    private $filename = null;

    protected function set_language()
    {
//        $language = Language::get()->name;
        // we have ru/en/de languages
        // but kohana expects ru-ru/en-en ...
        $language = "EN";
        I18n::lang($language.'-'.$language);
    }

    public function before()
    {
        parent::before();
        $this->set_language();
        $this->check_access();

        $this->view = new View();
        $this->view->bind('controller', $this);

        $this->template->styles = array();
        $this->template->scripts = array();
        foreach(array('content', 'keywords', 'description', 'title') as $property)
        {
            $this->template->set($property, '');
        }
    }

    public function set_filename($filename)
    {
        if( ! $filename)
            return;
        $this->filename = explode('/', $filename);
    }

    protected function check_access()
    {
        if ( ! $this->request->is_initial() ||
             ! $this->check_access ||
             Acl::instance()->allowed($this))
            return;

        // we are here because access is denied
        // redirect to not_logged_in if needed
        if ( $this->request->is_ajax())
            throw new HTTP_Exception_403();

        // rememeber the url
        Cookie::set(
            'return_url',
            Url::path('root').$this->request->url()
        );

        if ( ! Auth::instance()->logged_in())
        {
            $this->redirect(array('user_session/not_logged_in'));
            exit;
        }

        // ok, logged, in
        // then rejected
        $this->redirect(array('user_session/not_authorized'), 403);
        exit;
    }


    public function set_keywords($keywords)
    {
        $this->template->keywords = $keywords;
    }

    public function set_description($description)
    {
        $this->template->description = $description;
    }

    public function set_favicon($icon_name)
    {
        $this->template->favicon = $icon_name;
    }

    public function set_title($title)
    {
        if ( $this->template->title && !$title)
            return;
        $default_title = Kohana::$config->load('site.title');
        $delimiter = $title ? ' | ' : '';
        if ($default_title)
        {
            $title .= $delimiter.$default_title;
        }
        $this->template->title = $title;
    }

    public function current_request_structure()
    {
        return array_filter(array(
            $this->request->directory(),
            $this->request->controller(),
            $this->request->action(),
        ));
    }

    // registeres the needed resources from config file
    public function register_resources($identifier)
    {
        $media = Kohana::$config->load('media.'.$identifier);
        // reverse because we're not appending, but prepending
        $files = array_reverse(
            Arr::get($media, 'css', array())
        );
        foreach($files as $file => $media_type)
        {
            $this->register_css_file($file, $media_type, false, true);
        }

        // reverse because we're not appending, but prepending
        $files = array_reverse(
            Arr::get($media, 'js', array())
        );

        foreach ($files as $file)
        {
            $this->register_js_file($file, false, true);
        }
    }

    public function register_css_file($name, $media = '', $check_file = false, $insert_from_beginning = false)
    {
        $file_name = 'media/css/'.$name.'.css';
        if ($check_file && ! file_exists(DOCROOT.$file_name))
            return;
        if (array_key_exists($file_name, $this->template->styles))
            return;
        if ( ! $insert_from_beginning)
        {
            $this->template->styles[URL::base(TRUE, TRUE).$file_name] = $media;
        }
        else
        {
            Arr::unshift(
                $this->template->styles,
                URL::base(TRUE, FALSE).$file_name,
                $media
            );
        }
    }

    public function register_js_file($name, $check_file = false, $insert_from_beginning = false)
    {

        CoffeeScript::build_if_needed($name);
        $file_name = 'media/js/'.$name.'.js';

        if ($check_file && ! file_exists(DOCROOT.$file_name))
            return;
        $resource_name = URL::base(TRUE, TRUE).$file_name;

        if ($insert_from_beginning)
        {
            array_unshift($this->template->scripts, $resource_name);
        }
        else
        {
            $this->template->scripts []= $resource_name;
        }
        $this->template->scripts = array_unique($this->template->scripts);
    }

    // tries to add default files
    // in format directory/controller.action.js
    // and directory/controller.action.css
    private function register_resources_by_default($request_struct = array())
    {
        $structure = $this->current_request_structure();
        $directory = NULL;
        count($structure) < 3 ?: $directory = array_shift($structure);
        $file_name = (! $directory? '': $directory .DIRECTORY_SEPARATOR ) .  implode('.', $structure);
        $this->register_css_file($file_name, '', TRUE);
        $this->register_js_file($file_name, TRUE);
    }

    public function is_delete()
    {
        return $this->request->method() == 'DELETE';
    }

    public function is_ajax()
    {
        return $this->request->is_ajax();
    }

    public function render_partial($file = '', $locals=array())
    {
        $this->check_auto_render();
        $this->auto_render = false;
        $this->set_filename($file);
        foreach ($locals as $key => $value)
        {
            $this->view->set($key, $value);
        }
        $this->set_view_filename();
        return $this->response->body($this->view->render());
    }

    public function render_nothing()
    {
        $this->render_partial('core/empty');
    }

    public function redirect($url, $code = 302)
    {
        $this->check_auto_render();
        $this->auto_render = FALSE;
        $this->request->redirect($url, $code);
    }

    public function render_json($data)
    {
        $this->check_auto_render();
        $this->auto_render = false;
        $json = json_encode($data);
        $this->response->headers('Content-Type', 'application/json')
            ->send_headers()
            ->body($json);
    }

    // checks if auto_render already false, it means
    // you tried to render several views in one action
    protected function check_auto_render()
    {
        if ( ! $this->auto_render)
            throw new Kohana_Exception("You have to render something (or redirect) only once per action");
    }

    public function after()
    {
        if ( ! $this->auto_render)
        {
            parent::after();
            return;
        }

        $this->set_view_filename();

        foreach ($this->resource_prefixes as $prefix)
        {
            $this->register_resources($prefix);
        }

        //set favicon (name from config: site.favicon)
        $favicon = Kohana::$config->load('site.favicon', '');
        if ($favicon)
        {
            $this->set_favicon($favicon);
        }

        // sets title, meta keywords/description
//        $this->setup_meta_data();

        $this->register_resources_by_default();
        $this->template->content = $this->view->render();
        parent::after();
    }

//    // sets title, meta keywords/description
//    protected  function setup_meta_data()
//    {
//        Model_Page_Parameter::fill($this);
//    }

    // finally set the view filename (not possible to change back)
    protected function set_view_filename()
    {
        if ( ! $this->filename)
        {
            // i know it's bad, but we have to change $this->filename
            // in unified way

            $this->set_filename(
                implode('/', $this->current_request_structure())
            );
        }
        $this->view->set_filename(implode('/', $this->filename));
    }

}
