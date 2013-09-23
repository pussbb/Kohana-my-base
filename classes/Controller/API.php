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

class Controller_API extends Controller_Base_API {

    public function action_model()
    {
        $klass = Helper_Model::class_name($this->request->param('model_name'));
        $id = $this->request->param('id');

        $params = $_REQUEST;
        $limit = 25;
        if (isset($params['limit'])) {
            $limit = $params['limit'];
            unset($params['limit']);
        }

        $offset = NULL;
        if (isset($params['offset'])) {
            $offset = $params['offset'];
            unset($params['offset']);
        }

        $filter = array();
        if (isset($params['filter'])) {
            $filter = $params['filter'];
            unset($params['filter']);
        }

        $data = array();
        switch ($this->request->method()) {
            case HTTP_Request::GET:
                if ($id) {
                    $data = $klass::find($id);
                }
                else {
                    $data = $klass::find_all($filter, $limit, $offset);
                }
                break;
            case HTTP_Request::POST:
                $obj = new $klass($params);
                $data = $obj->save() ? $obj->as_array() : $obj->all_errors();
                break;
            case HTTP_Request::PUT:
                if ( ! $id )
                    throw new HTTP_Exception_405(tr('Method not allowed'));
                $obj = $klass::find($id);
                $obj->update_params($params);
                $data = $obj->save() ? $obj->as_array() : $obj->all_errors();
                break;
            case HTTP_Request::DELETE:
                if ( ! $id )
                    throw new HTTP_Exception_405(tr('Method not allowed'));

                if ( ! $klass::destroy($id) )
                    throw new HTTP_Exception_500('colud not delete');
                break;
            default:
                throw new HTTP_Exception_405(tr('Method not allowed'));
                break;
        }

        foreach($data as $key => $value) {
            if (is_object($value)) {
                $value = $value instanceof Base_Model
                    ? $value->as_deep_array()
                    : Object::properties($value);
            }
            $this->$key = $value;
        }
    }

}
