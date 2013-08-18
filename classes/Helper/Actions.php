<?php defined('SYSPATH') or die('No direct script access.');

class Helper_Actions {

    public static function action($model, $action, $attr = array(), $id = NULL)
    {

        switch($action){
            case 'destroy':
                return self::destroy($model, $attr, $id);
                break;
            case 'new':
            case 'create':
                return self::create($model, $attr, $id);
                break;
            case 'edit':
                return self::edit($model, $attr, $id);
                break;
            case 'view':
            case 'details':
                return self::details($model, $attr, $id);
                break;
            default:
                return HTML::anchor(Helper_Model::url($model, $action), $action, array('class' => 'action action_'.$action));
                break;
        }
    }

    public static function destroy($model, $attr = array(), $id = NULL)
    {
        $name = $model->representative_name();
        $attr = self::append_class($attr, 'action action_destroy');
        return HTML::anchor(
            Helper_Model::url($model, 'destroy', $id?:$model->id),
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

    public static function create($model, $attr = array(), $id = NULL)
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

    public static function edit($model, $attr = array(), $id = NULL)
    {
        $name = $model->representative_name();
        $attr = self::append_class($attr, 'action action_edit');
        return HTML::anchor(
            Helper_Model::url($model, 'edit', $id?:$model->id),
           '<i class="icon-pencil"></i> '. tr('Edit'),
            array_merge(array(
                'title' => tr('Edit %s', array($name)),
                'rel' => 'tooltip'
            ), $attr)
        );
    }

    public static function details($model, $attr = array(), $id = NULL)
    {
        $name = $model->representative_name();
        $attr = self::append_class($attr, 'action action_details');
        return HTML::anchor(
            Helper_Model::url($model, 'details', $id?:$model->id),
           '<i class="icon-info-sign"></i> '. tr('View') .'&nbsp;',
            array_merge(array(
                'title' => tr('View %s details', array($name)),
                'rel' => 'tooltip'
            ), $attr)
        );
    }
}
