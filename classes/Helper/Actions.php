<?php defined('SYSPATH') or die('No direct script access.');

class Helper_Actions {

    public static function action($model, $action, $attr = array())
    {

        switch($action){
            case 'destroy':
                return self::destroy($model, $attr);
                break;
            case 'new':
            case 'create':
                return self::create($model, $attr);
                break;
            case 'edit':
                return self::edit($model, $attr);
                break;
            case 'view':
            case 'details':
                return self::details($model, $attr);
                break;
            default:
                return HTML::anchor(Helper_Model::url($model, $action), $action, array('class' => 'action action_'.$action));
                break;
        }
    }

    public static function destroy($model, $attr = array())
    {
        $name = $model->representative_name();
        $attr = self::append_class($attr, 'action action_destroy');
        return HTML::anchor(
            Helper_Model::url($model, 'destroy'),
           '<i class="icon-trash"></i> '. tr('Delete') .'&nbsp;',
            array_merge(array(
                'data-title' => tr('Delete %s', array($name)),
                'data-toggle'=>'confirm',
                'title' => tr('Delete %s', array($name)),
                'rel' => 'tooltip'
            ), $attr)
        );
    }

    private static function append_class(array $attr, $class)
    {
        if (isset($attr['class']))
            $attr['class'] = $attr['class'].' '. $class;
        else
            $attr['class'] = $class;

        return $attr;
    }

    public static function create($model, $attr = array())
    {
        $name = $model->representative_name();
        $attr = self::append_class($attr, 'action action_new');
        return HTML::anchor(
            Helper_Model::url($model, 'new'),
           '<i class="icon-magic"></i> '. tr('Add new %s', array($name)) .'&nbsp;',
            array_merge(array(
                'title' => tr('Add new %s', array($name)),
                'rel' => 'tooltip'
            ), $attr)
        );
    }

    public static function edit($model, $attr = array())
    {
        $name = $model->representative_name();
        $attr = self::append_class($attr, 'action action_edit');
        return HTML::anchor(
            Helper_Model::url($model, 'edit'),
           '<i class="icon-pencil"></i> '. tr('Edit'),
            array_merge(array(
                'title' => tr('Edit %s', array($name)),
                'rel' => 'tooltip'
            ), $attr)
        );
    }

    public static function details($model, $attr = array())
    {
        $name = $model->representative_name();
        $attr = self::append_class($attr, 'action action_details');
        return HTML::anchor(
            Helper_Model::url($model, 'details'),
           '<i class="icon-info-sign"></i> '. tr('View') .'&nbsp;',
            array_merge(array(
                'title' => tr('View %s details', array($name)),
                'rel' => 'tooltip'
            ), $attr)
        );
    }
}
