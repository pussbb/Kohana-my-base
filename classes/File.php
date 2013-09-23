<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Helper functions to work with files
 * @package Kohana-my-base
 * @copyright 2013 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

class File extends Kohana_File {

    /**
     * function to raplace something in files
     *
     * @static
     * @param $file - (string) - full path to the file
     * @param $pattern - (string) php reg expr pattern.
     * @param $replacement
     * @internal param $replacment - (string)
     * @access public
     * @return void
     */

    public static function sed($file, $pattern, $replacement)
    {
        if ( ! file_exists($file) || ! $pattern)
            return;

        $file_content = file_get_contents($file);
        $file_content = preg_replace($pattern, $replacement, $file_content);
        file_put_contents($file, $file_content);
    }

    /**
     * function to delete file
     *
     * @static
     * @param $absolute_file_name - (string) - full path to the file
     * @access public
     * @return void
     */
    public static function delete($absolute_file_name)
    {
        if (file_exists($absolute_file_name))
        {
            try {
                return unlink($absolute_file_name);
            } catch(Exception $e) {
                return FALSE;
            }
        }
        return FALSE;
    }

    /**
     * download file
     *
     * @static
     * @param $url - (string) - file url which need to download
     * @param $file - (string) - filename of downloaded file with full path.
     * @access public
     * @return void
     */
    static public function download($url, $file)
    {
        $ch = curl_init();

        Dir::create_if_need(self::dirname($file));

        $fp = fopen($file, 'w');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, FALSE);
        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($ch, CURLOPT_FILE, $fp);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_exec($ch);
        if (curl_errno($ch))
            throw new Exception('Error downloading', curl_error($ch));

        curl_close($ch);
        fclose($fp);
    }

    /**
     * get file extentions
     *
     * @static
     * @param $file - (string) - filename.
     * @access public
     * @return string
     */
    static public function extention($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * get absoulute file directory name
     *
     * @static
     * @param $file - (string) - filename.
     * @access public
     * @return string
     */
    static public function dirname($file)
    {
        return realpath(pathinfo($file, PATHINFO_DIRNAME)).DIRECTORY_SEPARATOR;
    }

    /**
     * send's file data to the browser
     *
     * @static
     * @param $file_name - (string) - filename with full path.
     * @param $force - (bool) - if true file will be downloaded othewise browser could open it some extention for e.g pdf in browser.
     * @access public
     * @return void
     */
    static public function output_file($file_name, $force = FALSE)
    {
        if ( ! file_exists($file_name) || is_readable($file_name))
            throw new Exception('File not found');

        $ext = self::extention($file_name);
        $mime_type = Kohana::$config->load("mimes.$ext");

        if ($mime_type)
            $mime_type = $mime_type[0];
        else
            $mime_type = 'application/octet-stream';

        if (ob_get_contents())
            throw new Exception('Some data has already been flushed');

        if ($force)
            header('Content-Description: File Transfer');
        else
            header('Content-Type: '.$mime_type);

        if (headers_sent())
            throw new Exception('Some data has already been outputed to the browser');

        if (ob_get_level())
            ob_end_clean();

        @apache_setenv('no-gzip', 1);
        header('Cache-Control: public, must-revalidate, max-age=0, post-check=0, pre-check=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        if ($force) {
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream', FALSE);
            header('Content-Type: application/download', FALSE);
            header('Content-Type: '.$mime_type, FALSE);
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename="'.$file_name.'";');
            header('Content-Length: ' . filesize($file_name));
        }
        else {
            header('Content-Length: ' . filesize($file_name));
            header('Content-Disposition: inline; filename="'.$file_name.'";');
        }
        readfile($file_name);
    }

    /**
     * creates image preview
     * You must consider that if something went wrong original
     * file will return.(very safety function)
     * param preview can set as string '50х50', '50' or as array(50, 50), array(50)
     *
     * @static
     * @param $absolute_file_path - (string) - filename with full path.
     * @param $preview - (mixed) - size of image preview.
     * @param $lifetime - (int) - time in microseconds to store cached image preview
     * @param $cache_dir - (string) - full path where to store images preview. Default APPLICATION::chache_dir/preview
     * @access public
     * @return string
     */
    static public function image_preview($absolute_file_path, $preview, $lifetime = 600000, $cache_dir = NULL)
    {
        if ( ! class_exists('Image') )
            return $absolute_file_path;

        $preview_parts = is_array($preview)
            ? $preview
            : explode('x', strtolower((string)$preview));

        $preview = array_map(
            function($val){ return intval($val); },
            $preview_parts
        );
        $preview = array_filter($preview);

        if ( ! $preview )
            return $absolute_file_path;

        if ( ! $cache_dir )
            $cache_dir = Kohana::$cache_dir.DIRECTORY_SEPARATOR.'preview';

        Dir::create_if_need($cache_dir);
        if ( ! is_writable($cache_dir) )
        {
            chmod($cache_dir, 0755);
        }

        $file = $cache_dir
            .DIRECTORY_SEPARATOR
            .pathinfo($absolute_file_path, PATHINFO_FILENAME)
            .implode('_',$preview)
            .'.'
            .self::extention($absolute_file_path);

        if (file_exists($file))
        {
            if ((time() - filemtime($file)) < $lifetime)
                return $file;
            self::delete($file);
        }

        try
        {
            $image_preview = $image = Image::factory($absolute_file_path);
            if (count($preview) === 1)
            {
                $result = $image->resize($preview[0]);
            }
            else
            {
                $result = $image->resize($preview[0], $preview[1]);
            }
            $result->save($file);
            return $file;
        } catch(Kohana_Exception $e) {
            return $absolute_file_path;
        }
    }

}
