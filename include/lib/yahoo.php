<?php
class yahoo {
    public static function placefinder($address, $return_raw = false) {
        $url = 'http://where.yahooapis.com/geocode?';
        $arg = http_build_query(array(
            'q' => $address,
            'flags' => 'J'
        ));
        $content = file::get_remote($url . $arg);
        if (!$content) {
            return false;
        }
        
        if ($return_raw) {
            return $content;
        } else {
            $data = json::decode($content);
            return $data;
        }
    }
}