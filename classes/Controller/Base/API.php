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

    protected $allowed_methods = array(
        HTTP_Request::PUT,
        HTTP_Request::POST,
        HTTP_Request::GET
    );
    /**
     * @throws HTTP_Exception_403
     */
    public function before()
    {
        parent::before();
        $method = $this->request->method();
        $action = $this->request->action();

        if ( ! in_array($method, $this->allowed_methods)
            || (bool)preg_match('/update|destroy/', $action, $matches))
            throw new HTTP_Exception_405(tr('Method not allowed'));

        if ($action === 'model')
            return;

        $_action = $action;
        switch ($method) {
            case HTTP_Request::GET:
                break;
            case HTTP_Request::POST:
                $_action = $action === 'index' ? 'create' : "create_$action";
                break;
            case HTTP_Request::PUT:
                $_action = $action === 'index' ? 'update' : "update_$action";
                break;
            case HTTP_Request::DELETE:
                $_action = $action = $action === 'index' ? 'destroy' : "destroy_$action";
                break;
            default:
                //
                break;
        }
        $this->request->action($_action);

    }

    /**
     * @throws Kohana_Kohana_Exception
     */
    public function after()
    {
        $data = $this->dynamic_properties();
        if (count($data) == 1 && Arr::is_array(current($data)))
            $data = array_shift($data);
        $accept_types = Request::accept_type();

        if (array_key_exists('application/json', $accept_types)) {
            $this->render_json($data);
        } elseif (array_key_exists('application/xml', $accept_types)) {
            $this->render_xml($data);
        } else {
            throw new HTTP_Exception_500('Unknown format');
        }
        parent::after();
    }
}
