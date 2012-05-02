<?php defined('SYSPATH') or die('No direct script access.');

class Pretty_Form extends Singleton{

    private $errors = NULL;
    private $data = NULL;
    public $view_path = NULL;
    private $template = NULL;

    public function __construct()
    {
        $args = func_get_args();
        $view_path = strtolower(get_called_class()) . DIRECTORY_SEPARATOR;

        if ( ! $args)
            return;

        if ( func_num_args() == 1 && Arr::is_assoc($args[0]))
        {
            $this->data = $_REQUEST;
            $this->view_path = $view_path;
            foreach($args[0] as $key => $data) {
                $this->$key = $data;
            }

            return;
        }
        $this->data = Arr::get($args, 0, $_REQUEST);
        $this->errors = Arr::get($args, 1);
        $this->template = Arr::get($args, 2);
        $this->view_path = Arr::get($args, 3,$view_path);
    }

    public function __call($name, $arguments)
    {
        if ( method_exists($this, $name))
            return;//call_user_func_array($this, $name, $arguments);
        if ( method_exists('Form', $name))
            return  call_user_func_array('Form::'.$name,  $arguments);
    }

    public function set_template($name)
    {
        $this->template = $name;
    }

    public function open($action, $attr = NULL)
    {
        return Form::open($action, $attr) . '<fieldset>' ;
    }

    public function legend($text)
    {
        return '<legend>' . $text . '</legend>';
    }

    public function close()
    {
        return '</fieldset>'. Form::close();
    }

    public function input($params)
    {
        if ( $this->template) {
            $template = Arr::get($params, 'template', 'input');
            return $this->render_template($template, $params);
        }

        extract(Arr::extract($params, array('name','label','attr')));

        $label_ = NULL;
        $input_ = NULL;
        $value = $this->value($params);

        if ( $label)
        {
            $label_ = $this->label($name, $label);
            $input_ = Form::input($name, $value, $this->for_label($name, $attr));
        }
        else
        {
            $input_ = Form::input($name, $value, $attr);
        }
        return $label_ .  PHP_EOL . $input_;
    }

    private function for_label($name, $attr)
    {
        $attr['id'] = Arr::get($attr, 'id') . ' ' . $name;
        return $attr;
    }

    public function form_action($params)
    {
        if ( $this->template) {
            $template = Arr::get($params, 'template', 'form_actions');
            return $this->render_template($template, $params);
        }
        $result = '';
        foreach($params['buttons'] as $button)
        {
            $result .= Form::button(
                Arr::get($button, 0),
                Arr::get($button, 1),
                Arr::get($button, 2)
            );
        }
        return $result;
    }

    private function value($params)
    {
        $value = Arr::get($params, 'value');
        if ( $value)
            return $value;
        return Arr::get($this->data,  Arr::get($params, 'name'));
    }

    private function error($params)
    {
        if ( Arr::is_array($params))
        {
            $name = Arr::get($params, 'name');
        }
        else
        {
            $name = $params;
        }
        return Arr::get($this->errors,  $name);
    }

    private function render_template($name, $params)
    {
        $file = $this->view_path . $this->template . DIRECTORY_SEPARATOR . $name;
        $data = Arr::extract($params, array('name','label','attr', 'info', 'buttons'));
        $data['value'] = $this->value($params);
        $data['error'] = $this->error($params);
        return View::factory($file, $data)->render();
    }
}