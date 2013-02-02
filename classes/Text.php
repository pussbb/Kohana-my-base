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
    public static function strip_tags($text, $allowed_tags = '<b><p><strong><br>')
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
     * @return string
     */
    public static function strip_ansi_color($string)
    {
      return preg_replace('/\e\[[;?0-9]*[0-9A-Za-z]/', ' ', $string);
    }

    ///http://stackoverflow.com/questions/9462104/remove-on-js-event-attributes-from-html-tags

    /**
     * @var string
     */
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


    /**
     * removes all on-  attributes from all matched HTML tags
     *
     * @param $html
     * @return mixed
     */
    public static function remove_event_attributes($html)
    {
        $re = '(?&tag)' . self::$tag_on_defs;
        return preg_replace("~$re~xie", 'Text::remove_event_attributes_from_tag("$0")', $html);
    }

    /**
     * removes onanything attributes from a single opening tag
     *
     * @param $tag
     * @return mixed
     */
    public static function remove_event_attributes_from_tag($tag)
    {
        $re = '( ^ <(?&tagname) ) | \G \s*+ (?> ((?&attrib)) | ((?&crap)) )' . self::$tag_on_defs;
        return preg_replace("~$re~xie", '"$1$3"? "$0": (preg_match("/^on/i", "$2")? " ": "$0")', $tag);
    }

    /**
    * Truncates text.
    *
    * Cuts a string to the length of $length and replaces the last characters
    * with the ending if the text is longer than length.
    *
    * @param string  $text String to truncate.
    * @param integer $length Length of returned string, including ellipsis.
    * @param string  $ending Ending to be appended to the trimmed string.
    * @param boolean $exact If false, $text will not be cut mid-word
    * @param boolean $considerHtml If true, HTML tags would be handled correctly
    * @return string Trimmed string.
    */
    public static function truncate($text, $length = 500, $ending = '...', $exact = FALSE, $considerHtml = TRUE) {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }

            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';

            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                    // if tag is a closing tag (f.e. </b>)
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                    // if tag is an opening tag (f.e. <b>)
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }

                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length+$content_length> $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1]+1-$entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }

                // if the maximum length is reached, get off the loop
                if($total_length>= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }

        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }

        // add the defined ending to the text
        $truncate .= $ending;

        if($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }

        return $truncate;

    }

    /**
     * return translated human readable full name of month
     *
     *@param string|integer number of month
     *@static
     *@access public
     *@return string
     */
    public static function month_name($month_number)
    {
        $months = array(
            1 => tr('January'),
            2 => tr('February'),
            3 => tr('March'),
            4 => tr('April'),
            5 => tr('May'),
            6 => tr('June'),
            7 => tr('July'),
            8 => tr('August'),
            9 => tr('September'),
            10 => tr('October'),
            11 => tr('November'),
            12 => tr('December'),
        );
        return Arr::get($months, intval($month_number));
    }
}
