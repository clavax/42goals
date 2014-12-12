<?php
class output
{
    const CUT_TAG = '<hr\s+class="cut"\s*\/?>';
    
    public static function one_paragraph($text)
    {
        $paragraphs = explode("\n", $text);
        return $paragraphs[0];
    }
    
    public static function one_sentence($text)
    {
        $sentences = preg_split('/[\.\?\!]/', $text, PREG_SPLIT_DELIM_CAPTURE);
        return $sentences[0];
    }
    
    public static function cutted($text, $tag = self::CUT_TAG)
    {
        if (preg_match("/^(.+?)$tag(.+)$/smi", $text, $m)) {
            $text = $m[1];
        }
        return $text;
    }
    
    public static function is_cutted ($text, $tag = self::CUT_TAG) {
        return preg_match("/^(.+?)$tag(.+)$/smi", $text);
    }
    
    public static function with_cut($text, $tag = self::CUT_TAG)
    {
//        return preg_replace("/^(.+?)($tag)(.+)$/smi", '$1<span id="more">$2</span>$3', $text);
        return preg_replace("/^(.+?)($tag)(.+)$/smi", '$1<div id="more"></div>$3', $text);
    }
    
    public static function bb_code($text)
    {
        //escape HTML formating
        $text = htmlspecialchars($text);
    
        //convert URLs into clickable links
        $protocol = '(?:http|https|ftp):\/\/)';
        $domain = '(?:[a-z0-9_-]+\.)*(?:[a-z]{2,6})';
        $port = '(?::\d{1,6})';
        $path = '(?:\/[^<>\s]*)';
        $query = '(?:\?[^<>\s]*)';
        $link_pattern = "(?:(?:{$protocol}(?#|www\.)){$domain}{$port}?(?:{$query}|{$path})?";
        $email_pattern = "(?:[a-z0-9_-]+\.)*(?:[a-z0-9_-]+)@{$domain}";
        $text = preg_replace("/(?<!\[(?:url|img)=)($link_pattern)(?!\])/i", '<a href="$1">$0</a>', $text);
    
    
        //simple tags
        $simple = array('b' => 'strong', 'i' => 'em', 'u' => 'u', 's' => 'del');
        foreach ($simple as $code => $tag) {
            $text = preg_replace("/\[$code\](.+)\[\/$code\]/imsU", "<$tag>$1</$tag>", $text);
        }
    
        //code
        $text = preg_replace("/\[code\](.+)\[\/code\]/imsU", "<pre>$1</pre>", $text);
    
        //links and images
        $text = preg_replace("/\[email=($email_pattern)\](.+)\[\/email\]/imsU", '<a href="mailto:$1">$2</a>', $text);
        $text = preg_replace("/\[url=($link_pattern)\](.+)\[\/url\]/imsU", '<a href="$1">$2</a>', $text);
        $text = preg_replace("/\[img=($link_pattern)\](.*)\[\/img\]/imsU", '<img src="$1" alt="$2" />', $text);
    
        $quotes = '|\[q\](.*)\[/q\]|imsU';
        while (preg_match($quotes, $text)) {
            $text = preg_replace($quotes, '<blockquote>$1</blockquote>', $text);
        }
    
        $text = preg_replace('/(\r\n|\n|\r)/m', '<br />', $text);
        return $text;
    }
    
    public static function js_html($str)
    {
        $str = str_replace('"', '\\"', $str);
        $str = str_replace('/', '\\/', $str);
        $str = preg_replace('/([\n\r]+)/', '"$1 + "\\n', $str);
        $str = '"' . $str . '"';
        return $str;
    }

    public static function text_preview($text, $maxlen = 100)
    {
        $preview = '';
        $dom = new DOMDocument();
        $dom->loadHTML($text);
        $body = $dom->getElementsByTagName('body')->item(0);
        foreach ($body->childNodes as $child) {
            $preview .= ($preview && $child->nodeValue ? '<br />' : '') . utf8_decode($child->nodeValue);
            if (str::len($preview) > $maxlen) {
                break;
            }
        }
        
        if (str::len($preview) > $maxlen) {
            $text = str::sub($preview, 0, $maxlen);
            $t = 0;
            for ($i = $maxlen; $i < str::len($preview); $i ++) {
                $ch = str::sub($preview, $i, 1);
                if (ctype_space($ch) || ctype_punct($ch)) {
                    break;
                }
                $text .= $ch;
                
                $t ++;
                if ($t > 10) {
                    break;
                }
            }
            $text .= '&hellip;';
            $preview = $text;
        }
        
        return $preview;
    }    
    
    public static function first_img($text, $maxlen = 100)
    {
        $image = null;
        
        $dom = new DOMDocument();
        $dom->loadHTML($text);
        
        $imgs = $dom->getElementsByTagName('img');
        if ($imgs->length > 0) {
            $img = $imgs->item(0);
            $image = $img->getAttribute('src');
        }
        
        return $image;
    }
}
?>