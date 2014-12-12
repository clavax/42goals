<?php
class headers {
    public static function location($url) {
        header('Location: ' . $url);
    }
    
    public static function content($type) {
        header('Content-Type:' . $type);
    }
    
    public static function download($name, $mime, $size = null) {
        header('Content-Disposition: attachment; filename="' . $name . '";');
        header('Content-Type: ' . $mime);
        if (isset($size)) {
            header('Content-Length: ' . $size);
        }
    }
    
    public static function not_found() {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not found', true);
    }
    
    public static function get($url) {
        $ch = curl_init();
        $timeout = 0;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        // Getting binary data
        $header = curl_exec($ch);
        return $header;
    }
    
    public static function parse($header) {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach( $fields as $field ) {
            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if( isset($retVal[$match[1]]) ) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    }
}
?>