<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Core extends  Controller_Template{

    // base template file
    // but remember, in parent class it will become View object
    public $template = 'layout/template';
    // for children it's recommended to change this prefixes
    // if needed IN CONSTRUCTOR (to not damage changes in parent class)
    protected $resource_prefixes = array('');
    // check Acl access (true means do the check)
    protected $check_access = TRUE;

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
            throw new HTTP_Exception_403(__('access_deny'));

        // rememeber the url
        Cookie::set(
            'return_url',
            Url::base(TRUE, TRUE).$this->request->url()
        );

        if ( ! Auth::instance()->logged_in())
        {
            $this->redirect('users/login');
            return;
        }

        // ok, logged, in
        // then rejected
        throw new HTTP_Exception_403(__('access_deny'));
        return;
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
        Media::instance()->bundle($identifier);      
    }

    public function register_css_file($name, $media = '')
    {
        Media::instance()->append_style($name, $media);
    }

    public function register_js_file($name)
    {
        Media::instance()->append_script($name);
    }

    // tries to add default files
    // in format directory/controller.action.js
    // and directory/controller.action.css
    private function register_resources_by_default($request_struct = array())
    {
        $structure = $this->current_request_structure();
        $directory = NULL;
        if ( Arr::get($structure, 0))
        {
            $directory = array_shift($structure) . '/';
        }
        $file_name = $directory .  implode('.', $structure);
        Media::instance()->append(array('css','js'), $file_name, NULL, TRUE);
    }

    public function is_delete()
    {
        return $this->request->method() === 'DELETE';
    }

    public function is_ajax()
    {
        return $this->request->is_ajax();
    }

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

    public function render_nothing()
    {
        $this->render_partial('core/empty');
    }

    public function redirect($url, $code = 302)
    {
        $this->check_auto_render();
        $this->auto_render = FALSE;
        $this->request->redirect(URL::site($url), $code);
    }

    public function render_json($data)
    {
        $this->check_auto_render();
        $this->auto_render = FALSE;
        $json = json_encode($data, JSON_HEX_TAG);
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

        $this->register_resources_by_default();
        $this->append_dynamic_properties($this->view);
        $this->template->content = $this->view->render();
        parent::after();
    }

    private function append_dynamic_properties($view)
    {
        $reflection_object = new ReflectionClass($this);
        $properties = $reflection_object->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE );
        $system_variables = array('kohana_view_filename', 'kohana_view_data', 'filename');
        foreach($properties as $property) {
            $system_variables[] = $property->getName();
        }

        foreach (get_object_vars($this) as $key => $value) {
            if (in_array($key, $system_variables))
                continue;
            $view->bind($key, $value);
        }
    }

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
