<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Helper class to render forms
 *
 * extends Kohana_Form but
 * adds availability to create much more complicated forms
 * using template system
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com(alexnevpryaga@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category html
 * @subpackage html
 */

class Pretty_Form extends Singleton
{

    /**
     * array with errors
     * @var null
     */
    private $errors = NULL;
    /**
     * array with values
     * @var array|null
     */
    private $data = NULL;
    /**
     * defines where need to find views
     * @var null|string
     */
    public $view_path = NULL;
    /**
     * sets template name
     * @var null
     */
    private $template = NULL;

    /**
     * init settings
     */
    public function __construct()
    {
        $args = func_get_args();
        $view_path = strtolower(get_called_class()) . DIRECTORY_SEPARATOR;

        if (!$args)
            return;

        if (func_num_args() == 1 && Arr::is_assoc($args[0])) {
            $this->data = $_REQUEST;
            $this->view_path = $view_path;
            foreach ($args[0] as $key => $data) {
                $this->$key = $data;
            }

            return;
        }
        $this->data = Arr::get($args, 0, $_REQUEST);
        $this->errors = Arr::get($args, 1);
        $this->template = Arr::get($args, 2);
        $this->view_path = Arr::get($args, 3, $view_path);
    }

    /**
     * gives availability to call methods from this jbject and Kohana_Form
     * @param $name
     * @param $arguments
     * @throws Exception
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name))
            return call_user_func_array(array($this, $name), $arguments);;
        //call_user_func_array($this, $name, $arguments);
        if (method_exists('Form', $name))
            return call_user_func_array('Form::' . $name, $arguments);
        throw new Exception("Unknown method $name");
    }

    /**
     * sets the tempalte name
     * @param $name
     */
    public function set_template($name)
    {
        $this->template = $name;
    }

    /**
     * opens html form
     * @param $action
     * @param null $attr
     * @return string
     */
    public function open($action, $attr = NULL)
    {
        return Form::open($action, $attr) . '<fieldset>';
    }

    /**
     * form legend
     * @param $text
     * @return string
     */
    public function legend($text)
    {
        return '<legend>' . $text . '</legend>';
    }

    /**
     * close form
     * @return string
     */
    public function close()
    {
        return '</fieldset>' . Form::close();
    }

    /**
     * html input
     * @param $params
     * @return string
     */
    public function input($params)
    {
        if ($this->template) {
            $template = Arr::get($params, 'template', 'input');
            return $this->render_template($template, $params);
        }

        extract(Arr::extract($params, array('name', 'label', 'attr')));

        $label_ = NULL;
        $input_ = NULL;
        $value = $this->value($params);

        if ($label) {
            $label_ = $this->label($name, $label);
            $input_ = Form::input($name, $value, $this->for_label($name, $attr));
        } else {
            $input_ = Form::input($name, $value, $attr);
        }
        return $label_ . PHP_EOL . $input_;
    }

    /**
     * html input
     * @param $params
     * @return string
     */
    public function textarea($params)
    {
        if ($this->template) {
            $template = Arr::get($params, 'template', 'textarea');
            return $this->render_template($template, $params);
        }

        extract(Arr::extract($params, array('name', 'label', 'attr')));

        $label_ = NULL;
        $input_ = NULL;
        $value = $this->value($params);

        if ($label) {
            $label_ = $this->label($name, $label);
            $input_ = Form::textarea($name, $value, $this->for_label($name, $attr));
        } else {
            $input_ = Form::textarea($name, $value, $attr);
        }
        return $label_ . PHP_EOL . $input_;
    }
    /**
     * inserts id to form element if label was defined
     * @param $name
     * @param $attr
     * @return array
     */
    private function for_label($name, $attr)
    {
        $attr['id'] = Arr::get($attr, 'id') . ' ' . $name;
        return $attr;
    }

    /**
     * returns action buttons (Submit, Reset ...)
     * @param $params
     * @return string
     */
    public function form_actions($params)
    {
        if ($this->template) {
            $template = Arr::get($params, 'template', 'form_actions');
            return $this->render_template($template, $params);
        }
        $result = '';
        foreach ($params['buttons'] as $button) {
            $result .= Form::button(
                Arr::get($button, 0),
                Arr::get($button, 1),
                Arr::get($button, 2)
            );
        }
        return $result;
    }

    /**
     * html select
     * @param $params
     * @return string
     */
    public function select($params)
    {
        if ($this->template) {
            $template = Arr::get($params, 'template', 'select');
            return $this->render_template($template, $params);
        }
        $label_ = NULL;
        $input_ = NULL;

        $value = $this->value($params);
        extract(Arr::extract($params, array('name', 'label', 'attr', 'buttons')));
        if ($label) {
            $label_ = $this->label($name, $label);
            $input_ = Form::select($name, $buttons, $value, $attr);
        } else {
            $input_ = Form::select($name, $buttons, $value, $attr);
        }
        return $label_ . PHP_EOL . $input_;
    }

    /**
     * html input type password
     * @param $params
     * @return string
     */
    public function password($params)
    {
        $attr = Arr::get($params, 'attr', array());
        $attr['type'] = 'password';
        $params['attr'] = $attr;
        return $this->input($params);
    }

    /**
     * html form checkbox
     * @param $params
     * @return string
     */
    public function checkbox($params)
    {
        if ($this->template) {
            $template = Arr::get($params, 'template', 'checkbox');
            return $this->render_template($template, $params);
        }
        $label_ = NULL;
        $input_ = NULL;

        $value = $this->value($params);
        extract(Arr::extract($params, array('name', 'label', 'attr')));
        if ($label) {
            $label_ = $this->label($name, $label);
            $input_ = Form::checkbox($name, $value, !is_null($value), $this->for_label($name, $attr));
        } else {
            $input_ = Form::checkbox($name, $value, empty($value), $attr);
        }
        return $label_ . PHP_EOL . $input_;
    }

    /**
     * get value for form element if exists
     * @param $params
     * @return mixed
     */
    private function value($params)
    {
        $value = Arr::get($params, 'value');
        if ($value)
            return $value;
        return Arr::get($this->data, Arr::get($params, 'name'));
    }

    /**
     * returns error if exists
     * @param $params string
     * @return mixed
     */
    private function error($params)
    {
        if (Arr::is_array($params))
            $name = Arr::get($params, 'name');
        else
            $name = $params;

        return Arr::get($this->errors, $name);
    }

    /**
     * returns general error msg if exists
     *
     * @return mixed
     */
    public function general_error()
    {
        if ( ! $this->error('general'))
            return NULL;
        return $this->render_template('general', array('name' => 'general'));
    }

    /**
     * renders template
     * @param $name
     * @param $params
     * @return mixed
     */
    private function render_template($name, $params)
    {
        $file = $this->view_path . $this->template . DIRECTORY_SEPARATOR . $name;
        $data = Arr::merge(array('name' => NULL, 'label'=>NULL, 'attr'=>NULL, 'info'=>NULL, 'buttons'=>NULL), $params);
        $data['value'] = $this->value($params);
        if ( ! isset($data['error']))
            $data['error'] = $this->error($params);
        return View::factory($file, $data)->render();
    }
}
