<?php defined('DOCROOT') or die('No direct script access.');
/**
 * Class to add some extra functionality to Kohana's class Text
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

class Text extends Kohana_Text {

    /**
     * general function cleans from xss injections
     * @static
     * @param $data
     * @return string
     */
    public static function xss_clean($data)
    {
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        // we are done...
        return self::remove_event_attributes($data);
    }

    /**
     * general function to delete html tags and html tag attributes
     * @static
     * @param $text string
     * @param $allowed_tags string
     * @return string
     */
    public function strip_tags($text, $allowed_tags = '<b><p><strong><br>')
    {
        // remove all html tags except <b><p><strong><br>
        $text = strip_tags($text, $allowed_tags);
        // remove all html tags attributes for allowed tags
        // (prevent js code injection by inserting onclick attribute)
        $text = preg_replace(
            "/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",
            '<$1$2>',
            $text
        );
        return $text;
    }

    /**
     * strip ANSI escape color codes.
     * @static
     * @param $string string
     */
    public static function strip_ansi_color($string)
    {
      return preg_replace('/\e\[[;?0-9]*[0-9A-Za-z]/', ' ', $string);
    }

    ///http://stackoverflow.com/questions/9462104/remove-on-js-event-attributes-from-html-tags

    public static $tag_on_defs = '(?(DEFINE)
        (?<tagname> [a-z][^\s>/]*+    )
        (?<attname> [^\s>/][^\s=>/]*+    )  # first char can be pretty much anything, including =
        (?<attval>  (?>
                        "[^"]*+" |
                        \'[^\']*+\' |
                        [^\s>]*+            # unquoted values can contain quotes, = and /
                    )
        )
        (?<attrib>  (?&attname)
                    (?: \s*+
                        = \s*+
                        (?&attval)
                    )?+
        )
        (?<crap>    [^\s>]    )             # most crap inside tag is ignored, will eat the last / in self closing tags
        (?<tag>     <(?&tagname)
                    (?: \s*+                # spaces between attributes not required: <b/foo=">"style=color:red>bold red text</b>
                        (?>
                            (?&attrib) |    # order matters
                            (?&crap)        # if not an attribute, eat the crap
                        )
                    )*+
                    \s*+ /?+
                    \s*+ >
        )
    )';


    // removes onanything attributes from all matched HTML tags
    public static function remove_event_attributes($html)
    {
        $re = '(?&tag)' . self::$tag_on_defs;
        return preg_replace("~$re~xie", 'Text::remove_event_attributes_from_tag("$0")', $html);
    }

    // removes onanything attributes from a single opening tag
    function remove_event_attributes_from_tag($tag)
    {
        $re = '( ^ <(?&tagname) ) | \G \s*+ (?> ((?&attrib)) | ((?&crap)) )' . self::$tag_on_defs;
        return preg_replace("~$re~xie", '"$1$3"? "$0": (preg_match("/^on/i", "$2")? " ": "$0")', $tag);
    }
}