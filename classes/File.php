<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Helper functions to work with files
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
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
    
    static public function download($url, $file)
    {
        $ch = curl_init();
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

    private static $mimetypes = NULL;

    static public function output_file($file_name, $force = FALSE)
    {
        self::$mimetypes = self::$mimetypes?: require_once DOCROOT.'config/mimes.php';

        $mime_type = Arr::path(
            self::$mimetypes,
            pathinfo($file_name, PATHINFO_EXTENSION).'.0',
            'image/jpeg'
        );

        if (ob_get_contents())
            throw new Exception('Some data has already been output');

        if ($force)
            header('Content-Description: File Transfer');
        else
            header('Content-Type: '.$mime_type);

        if (headers_sent())
            throw new Exception('Some data has already been output to browser');

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

}
