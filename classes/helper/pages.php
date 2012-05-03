<?php defined('SYSPATH') or die('No direct script access.');

class Helper_Pages {

    public static function get_directories($directory = '')
    {
        $hidden = array(
            'ajax',
            'forum',
            'marketplace',
            '.svn',
            'template',
        );
        $path = DOCROOT . 'application/classes/controller/' . $directory;
        $result = array();
        if(is_dir($path))
        {
           $dh = opendir($path);
           while (false !== ($dir = readdir($dh)))
           {
                if (is_dir($path . $dir) && $dir !== '.' && $dir !== '..' && !in_array($dir, $hidden))
                {
                    $result[] = $dir;
                } else {
                    continue;
                }
           }
           closedir($dh);
           asort($result);
           return $result;
        }
        else
        {
           return FALSE;
        }
    }

    public static function get_controllers($directory = '')
    {
        $hidden = array(
            'api.php'
        );
        $dir = DOCROOT . 'application/classes/controller/' . $directory . '/';
        $file_list = '';
        $result = array();
        $dh = opendir($dir);
        if ($dh)
        {
            while (($file = readdir($dh)) !== false)
            {
                if ($file !== '.' AND $file !== '..')
                {
                    $current_file = $dir . '/' . $file;
                    if (is_file($current_file) && !in_array($file,$hidden))
                    {
                        $result[] = substr($file, 0, strpos($file, '.php'));
                    }
                }
            }
        }
        else
        {
            return FALSE;
        }
        asort($result);
        return $result;
    }

    public static function get_actions($directory, $controller)
    {
        $hidden = array(
            'postcomment'
        );
        $controller_file = DOCROOT . 'application/classes/controller/' . $directory . '/' . $controller . '.php';
        $views_dir = DOCROOT . 'application/views/';
        $fh = fopen($controller_file, "r");

        if ($fh)
        {
            $result = array();
            while (!feof($fh))
            {
                $line = fgets($fh, 4096);
                $action = array();
                preg_match('/function[\s]+action_(\S+)\([\s\S]*\)/i', $line, $action);

                if (isset($action[1]) && !in_array($action[1], $hidden)
                    && is_file($views_dir . $directory . '/' . $controller . '/' . $action[1] . '.php'))
                {
                    $result[] = $action[1];
                }
            }
            fclose($fh);
        }
        else
        {
            return FALSE;
        }
        asort($result);
        return $result;
    }

}
