<?php
namespace Wunderman\Utils;

/**
 * TextHelper.
 *
 * @package symfony
 * @subpackage helper
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author David Heinemeier Hansson
 * @version SVN: $Id: TextHelper.php 33022 2011-09-15 05:27:12Z fabien $
 */
class HelperText
{

    /**
     * Truncates +text+ to the length of +length+ and replaces the last three characters with the +truncate_string+
     * if the +text+ is longer than +length+.
     */
    public static function truncate_text($text, $length = 30, $truncate_string = '...', $truncate_lastspace = false)
    {
        if ($text == '') {
            return '';
        }
        $mbstring = extension_loaded('mbstring');
        if ($mbstring) {
            $old_encoding = mb_internal_encoding();
            @mb_internal_encoding(mb_detect_encoding($text));
        }
        $strlen = ($mbstring) ? 'mb_strlen' : 'strlen';
        $substr = ($mbstring) ? 'mb_substr' : 'substr';
        if ($strlen($text) > $length) {
            $truncate_text = $substr($text, 0, $length - $strlen($truncate_string));
            if ($truncate_lastspace) {
                $truncate_text = preg_replace('/\s+?(\S+)?$/', '', $truncate_text);
            }
            $text = $truncate_text . $truncate_string;
        }
        if ($mbstring) {
            @mb_internal_encoding($old_encoding);
        }

        return $text;
    }

    /**
     * Highlights the +phrase+ where it is found in the +text+ by surrounding it like
     * <strong class="highlight">I'm a highlight phrase</strong>. The highlighter can be specialized by
     * passing +highlighter+ as single-quoted string with \1 where the phrase is supposed to be inserted.
     * N.B.: The +phrase+ is sanitized to include only letters, digits, and spaces before use.
     *
     * @param string $text subject input to preg_replace.
     * @param string $phrase string or array of words to highlight
     * @param string $highlighter regex replacement input to preg_replace.
     *
     * @return string
     */
    public static function highlight_text($text, $phrase, $highlighter = '<strong class="highlight">\\1</strong>')
    {
        if (empty($text)) {
            return '';
        }
        if (empty($phrase)) {
            return $text;
        }
        if (is_array($phrase)) {
            foreach ($phrase as $word) {
                $pattern[]     = '/(' . preg_quote($word, '/') . ')/i';
                $replacement[] = $highlighter;
            }
        } else {
            $pattern     = '/(' . preg_quote($phrase, '/') . ')/i';
            $replacement = $highlighter;
        }

        return preg_replace($pattern, $replacement, $text);
    }

    /**
     * Extracts an excerpt from the +text+ surrounding the +phrase+ with a number of characters on each side determined
     * by +radius+. If the phrase isn't found, nil is returned. Ex:
     * excerpt("hello my world", "my", 3) => "...lo my wo..."
     * If +excerpt_space+ is true the text will only be truncated on whitespace, never inbetween words.
     * This might return a smaller radius than specified.
     * excerpt("hello my world", "my", 3, "...", true) => "... my ..."
     */
    public static function excerpt_text($text, $phrase, $radius = 100, $excerpt_string = '...', $excerpt_space = false)
    {
        if ($text == '' || $phrase == '') {
            return '';
        }
        $mbstring = extension_loaded('mbstring');
        if ($mbstring) {
            $old_encoding = mb_internal_encoding();
            @mb_internal_encoding(mb_detect_encoding($text));
        }
        $strlen        = ($mbstring) ? 'mb_strlen' : 'strlen';
        $strpos        = ($mbstring) ? 'mb_strpos' : 'strpos';
        $strtolower    = ($mbstring) ? 'mb_strtolower' : 'strtolower';
        $substr        = ($mbstring) ? 'mb_substr' : 'substr';
        $found_pos     = $strpos($strtolower($text), $strtolower($phrase));
        $return_string = '';
        if ($found_pos !== false) {
            $start_pos = max($found_pos - $radius, 0);
            $end_pos   = min($found_pos + $strlen($phrase) + $radius, $strlen($text));
            $excerpt   = $substr($text, $start_pos, $end_pos - $start_pos);
            $prefix    = ($start_pos > 0) ? $excerpt_string : '';
            $postfix   = $end_pos < $strlen($text) ? $excerpt_string : '';
            if ($excerpt_space) {
                // only cut off at ends where $exceprt_string is added
                if ($prefix) {
                    $excerpt = preg_replace('/^(\S+)?\s+?/', ' ', $excerpt);
                }
                if ($postfix) {
                    $excerpt = preg_replace('/\s+?(\S+)?$/', ' ', $excerpt);
                }
            }
            $return_string = $prefix . $excerpt . $postfix;
        }
        if ($mbstring) {
            @mb_internal_encoding($old_encoding);
        }

        return $return_string;
    }

    /**
     * Word wrap long lines to line_width.
     */
    public static function wrap_text($text, $line_width = 80)
    {
        return preg_replace('/(.{1,' . $line_width . '})(\s+|$)/s', "\\1\n", preg_replace("/\n/", "\n\n", $text));
    }

    /**
     * Turns all links into words, like "<a href="something">else</a>" to "else".
     */
    public static function strip_links_text($text)
    {
        return preg_replace('/<a[^>]*>(.*?)<\/a>/s', '\\1', $text);
    }

    /**
     * Clean string,
     * minimize and remove space, accent and other
     *
     * @param string $string
     * @return string
     */
    public static function mb_strtoclean($string)
    {
        // Valeur a nettoyer (conversion)
        $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                                    'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                                    'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                                    'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                                    'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y',
                                    ' ' => '', '_' => '', '-' => '', '.'=> '', ',' => '', ';' => '');

        return mb_strtolower(strtr($string, $unwanted_array ));
    }

    /**
     * Echappe les caractères interdit en json
     *
     * @param $string
     * @return mixed
     */
    public static function escapeJsonString($string) 
    { # list from www.json.org: (\b backspace, \f formfeed)
        $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
        $result = str_replace($escapers, $replacements, $string);
        return $result;
    }

    /**
     * Clean string custom,
     * minimize and remove space, accent and other
     *
     * @param string $string
     * @return string
     */
    public static function mb_strtocleanCustom($string, $carReplace = array())
    {
        $unwanted_array = $carReplace;
        
        return strtr($string, $unwanted_array );
    }

    /**
     * Remplacement des retours chariots, tablutaion
     * @param  string $chaine  
     * @param  string $replace 
     * @return string
     */
    public static function replaceRetourChariotTabulation($chaine, $replace=" ")
    {
        $chaineReplace = preg_replace("#\n|\t|\r#", $replace, $chaine);

        return $chaineReplace;
    }
}