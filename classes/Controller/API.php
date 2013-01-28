<?php defined('SYSPATH') or die('No direct script access.');

/**
 * API template
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

class Controller_API extends Controller_Base_Core {

    public $template = NULL;
    protected $layout = NULL;
    public $auto_render = FALSE;
    protected $check_access = FALSE;
    protected $ajax_auto_partial = FALSE;

    const JSON_FORMAT = 'json';
    const XML_FORMAT = 'xml';

    private $responce_type = NULL;

    public function before()
    {
        parent::before();
        $this->responce_type = Arr::get($_REQUEST, 'format', self::JSON_FORMAT);
        if ($this->is_put()) {
            $action = $this->request->action();
            $action = $action === 'index' ? 'update' : "update_$action";
            $this->request->action($action);
        }
        else if ($this->is_delete()) {
            $action = $this->request->action();
            $action = $action === 'index' ? 'destroy' : "destroy_$action";
            $this->request->action($action);
        } else {
            $action = $this->request->action();
            if ((bool)preg_match('/update|destroy/', $action, $matches))
                throw new HTTP_Exception_403(tr('Access deny'));
        }
    }

    public function after()
    {

        $data = $this->dynamic_properties(array('responce_type'));
        if (count($data) == 1 && Arr::is_array(current($data)))
            $data = array_shift($data);
        switch ($this->responce_type) {
            case self::JSON_FORMAT:
                $this->render_json($data);
                break;
            case self::XML_FORMAT:
                break;
            default:
                throw new Kohana_Kohana_Exception(500, 'Unknown format');
                break;
        }
    }
}
