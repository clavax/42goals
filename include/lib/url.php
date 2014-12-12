<?php
class url {
    public static function shorten($url, $maxlen = 50, $filling = '&hellip;') {
        $l = round($maxlen / 2);
        return strlen($url) < $maxlen ? $url : substr($url, 0, $l) . $filling . substr($url, - $l);
    }
    
    public static function linkify($url, $host = '') {
        if (!strlen($host)) {
            $host = $GLOBALS['URL']['host'] . $GLOBALS['URL']['main'];
        }
        $protocol = 'http|https|ftp';
        if (!preg_match("#{$protocol}://|www\.#", $url)) {
            $url = $host . $url;
        }
        return $url;
    }
    
    public static function add_protocol($url) {
        $protocol = 'http|https|ftp';
        if (!preg_match("#{$protocol}://#", $url)) {
            $url = 'http://' . $url;
        }
        return $url;
    }
}
?>