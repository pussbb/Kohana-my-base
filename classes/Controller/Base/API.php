<?php defined('SYSPATH') or die('No direct script access.');

/**
 * API template
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
    private $model = NULL;

    /**
     * list of allowed http methods
     * @var array
     */
    protected $allowed_methods = array(
        HTTP_Request::PUT,
        HTTP_Request::POST,
        HTTP_Request::GET
    );

    /**
     * limit records
     * @var integer
     */
    protected $limit = 25;

    /**
     * start select records from index
     * @var integer
     */
    protected $offset = NULL;

    /**
     * searching fields, if using Base_Model also can be loaded with relation
     * @var array
     */
    protected $filter = array();

    /**
     * fields needed to insert or update record in database
     * @var array
     */
    protected $params = array();

    /**
     * set status code of response
     * @var integer
     */
    protected $status_code = 200;

    /**
     * @throws HTTP_Exception_405
     */
    public function before()
    {
        parent::before();

        $method = $this->request->method();
        $action = $this->request->action();
        $params = $_REQUEST;

        if (isset($params['limit'])) {
            $this->limit = $params['limit'];
            unset($params['limit']);
        }

        if (isset($params['offset'])) {
            $this->offset = $params['offset'];
            unset($params['offset']);
        }

        if (isset($params['filter'])) {
            $this->filter = $params['filter'];
            unset($params['filter']);
        }
        $this->params = $params;

        if ( ! in_array($method, $this->allowed_methods)
            || (bool)preg_match('/update|destroy/', $action, $matches))
            throw new HTTP_Exception_405(tr('Method not allowed'));

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
     *
     * Attach some data to controller which will ouptut in response
     *
     * @access protected
     * @param $data array
     * @return void
     */
    protected function attach_response_data(array $data)
    {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     *
     * get records or record
     *
     * @access public
     * @return void
     */
    public function action_index()
    {
        $klass = Helper_Model::class_name($this->model);
        $id = $this->request->param('id');
        $data = $id ? $klass::find($id) : $klass::find_all($this->filter, $this->limit, $this->limit);
        $this->attach_response_data($data);
    }

     /**
     *
     * create new record
     *
     * @access public
     * @return void
     */
    public function action_create()
    {
        $klass = Helper_Model::class_name($this->model);
        $obj = new $klass($params);
        if ($obj->save()) {
            $this->attach_response_data($obj->as_array());
        } else {
            $this->attach_response_data($obj->all_errors());
            $this->status_code = 400;
        }
    }

    /**
     *
     * update record
     *
     * @access public
     * @return void
     */
    public function action_update($id)
    {
        if ( ! $id )
            throw new HTTP_Exception_405(tr('Method not allowed'));

        $klass = Helper_Model::class_name($this->model);
        $obj = $klass::find($id);
        if ($obj->save()) {
            $this->attach_response_data($obj->as_array());
        } else {
            $this->attach_response_data($obj->all_errors());
            $this->status_code = 400;
        }
    }

    /**
     *
     * delete record
     *
     * @access public
     * @return void
     */
    public function action_destroy($id)
    {
        if ( ! $id )
            throw new HTTP_Exception_405(tr('Method not allowed'));

        $klass = Helper_Model::class_name($this->model);
        if ( ! $klass::destroy($id) )
            throw new HTTP_Exception_500('colud not delete');
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
            $this->render_json($data, $this->status_code);
        } elseif (array_key_exists('application/xml', $accept_types)) {
            $this->render_xml($data, $this->status_code);
        } else {
            throw new HTTP_Exception_500('Unknown format');
        }
        parent::after();
    }
}
