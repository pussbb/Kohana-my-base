<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Module
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

class Admin_Modules extends Singleton {

  private $_modules = array();

  public function register($name, $path)
  {
      $this->_modules[$name] = $path;
  }

  public function modules()
  {
      $result = array();
      foreach($this->_modules as $module_name => $path) {
          $klass = 'Helper_Admin_Modules_'.ucfirst($module_name);
          $result[] = array(
            'info' => $klass::info(),
            'menu' => $klass::menu_items(),
          );
      }
      return $result;
  }
}