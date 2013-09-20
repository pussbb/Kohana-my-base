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

class Controller_Base_API extends Controller_Base_Core {

    /**
     * @var null
     */
    public $template = NULL;
    /**
     * @var null
     */
    protected $layout = NULL;
    /**
     * @var bool
     */
    public $auto_render = FALSE;
    /**
     * @var bool
     */
    protected $check_access = FALSE;
    /**
     * @var bool
     */
    protected $ajax_auto_partial = FALSE;

    /**
     * @var null
     */
    private $responce_type = NULL;

    /**
     * @throws HTTP_Exception_403
     */
    public function before()
    {
        parent::before();
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

    /**
     * @throws Kohana_Kohana_Exception
     */
    public function after()
    {
        $data = $this->dynamic_properties();
        if (count($data) == 1 && Arr::is_array(current($data)))
            $data = array_shift($data);
        switch (Request::accept_type()) {
            case 'application/json':
                $this->render_json($data);
                break;
            case 'application/xml':
                $this->render_xml($data);
                break;
            default:
                throw new HTTP_Exception_500('Unknown format');
                break;
        }
    }
}
