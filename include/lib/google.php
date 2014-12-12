<?php
class google {
    public static function translate($query, $from, $to) {
        // This example request includes an optional API key which you will need to
        // remove or replace with your own key.
        // Read more about why it's useful to have an API key.
        // The request also includes the userip parameter which provides the end
        // user's IP address. Doing so will help distinguish this legitimate
        // server-side traffic from traffic which doesn't come from an end-user.
        
        import('lib.json');
        $Conf = Framework::get('Conf');
        $key = $Conf->CNF->google->translate_key_en;
        $ip  = array_get($_SERVER, 'REMOTE_ADDR');
        $query = urlencode($query);
        $url = "http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q={$query}&langpair={$from}|{$to}&key={$key}&userip={$ip}";
        
        // sendRequest
        // note how referer is set manually
        $body = file::get_remote($url, $Conf->CNF->site->host);
        
        // now, process the JSON string
        $json = json_decode($body);
        // now have some fun with the results...
        if (!isset($json->responseData->translatedText)) {
            Framework::get('Error')->log($body);
            $translation = $query;
        } else {
            $translation = $json->responseData->translatedText;
        }
        
        return $translation;
    }
}