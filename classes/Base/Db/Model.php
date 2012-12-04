<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class to work with db queries easily
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com(alexnevpryaga@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category database
 * @subpackage database
 */

class Base_Db_Model extends Kohana_Model  implements Serializable, ArrayAccess,  IteratorAggregate {

    protected $_table_fields = NULL;


    /**
     *
     */
    protected $_table_columns = array();
     /**
     * contain dynamically append variables
     *
     * or fields and value for the row
     * @var array|null
     * @access private
     */
    protected  $data = array();

    /**
     * Serialize data only for that table everything else ignored
     *
     * @access public
     * @return string
     */
    public function serialize()
    {
        $data =array(
            'data' => Arr::extract($this->data, $this->_table_fields),
            '_table_fields' => $this->_table_fields,
            '_table_columns' => $this->_table_columns
        );

        return (string)serialize($data);
    }

    /**
     * Unserialize data
     *
     * @param string $data
     * @access public
     * @return string
     */
    public function unserialize($data)
    {
        $data = unserialize($data);
        if ( ! $data)
            $data = array();
        $this->data = Arr::get($data, 'data');
        $this->_table_columns = Arr::get($data, '_table_columns');
        $this->_table_fields = Arr::get($data, '_table_fields');

    }

/**
     * Check if the given item exists
     *
     * @param string $key
     * @return boolean
     */
    public function offsetExists($key) {
        return isset($this->data[$key]);
    }

    /**
     * Get the given item
     *
     * @param string $key
     * @return string
     */
    public function offsetGet($key) {
        return isset($this->data[$key]) ? $this->data[$key] : NULL;
    }

    /**
     * Set the given header
     *
     * @param string $key
     * @param string $value
     */
    public function offsetSet($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Unset the given item
     *
     * @param string $key
     */
    public function offsetUnset($key) {
        unset($this->data[$key]);
    }

    /**
     * Get an interator for the data
     *
     * @return ArrayIterator
     */
    public function getIterator() {
        return new ArrayIterator($this->data);
    }
}
