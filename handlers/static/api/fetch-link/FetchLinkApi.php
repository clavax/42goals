<?php
import('base.controller.BaseApi');

class FetchLinkApi extends BaseApi
{
    public function handleGetDefault(array $request = array())
    {
        if (!Access::loggedIn()) {
            return $this->respondOk(array('error' => 'not authorized'));
        }
        
        import('3dparty.Readability');
        $url = array_get($request, 'url');
        if (!$url) {
            return $this->respondOk(array('error' => array('url' => 'empty')));
        }
        
        if (!preg_match('#^https?://#', $url)) {
            $url = 'http://' . $url;
        }
        
        /*
        // make a screenshot
        $arg_url = escapeshellarg($url);
        $name = uniqid($this->ENV->user->login) . '.png';
        $path = $this->PTH->screenshots . $name;
        
        // blank image
        copy($this->PTH->img . 'site/no-screenshot.png', $path);
        
        $cmd = "{$this->PTH->main}screenshot-opera.sh $arg_url $path > /dev/null 2>&1 &";
        $output = exec($cmd);
        */
        
        // while screenshot is generating fetch html content
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($c, CURLOPT_HEADER, TRUE);
        curl_setopt($c, CURLOPT_URL, $url);
        $response = curl_exec($c);
        curl_close($c);
        
        $offset = strpos($response, "\r\n\r\n");
        $header = substr($response, 0, $offset);
        $html = substr($response, $offset + 4);
        
        if (!$html) {
            return $this->respondOk(array('error' => array('url' => 'invalid')));
        }
        
        $is_utf8 = utf8_encode(utf8_decode($html)) == $html;
        if (!$is_utf8) {
            $encoding = 'auto';
            if (preg_match('/^Content-Type:\s+([^;]+)(?:;\s*charset=(.*))?/im', $header, $match)) {
                $encoding = trim($match[2]);
            }
            if ($encoding != 'utf-8') {
                $html = mb_convert_encoding($html, 'UTF-8', $encoding);
            }
        }
        
        $readability = new Readability($html, $url);
        $readability->init();
        
        $title = $readability->getTitle()->textContent;
        $body = $readability->getContent();
        
        $maxlen = 300;
        $preview = '';
        foreach ($body->childNodes as $child) {
            $preview .= ($preview && $child->nodeValue ? " " : '') . trim($child->nodeValue);
            if (str::len($preview) > $maxlen) {
                $preview = str::sub($preview, 0, $maxlen) . '...';
                break;
            }
        }
        
        $response = array(
            'title' => $title,
            'body' => $preview, 
            //'screenshot' => $this->URL->screenshots . $name
        );
        
        return $this->respondOk(array('item' => $response));
    }
}