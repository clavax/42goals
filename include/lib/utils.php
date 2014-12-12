<?php
class utils {    
    const GOOGLE_MAGIC = 0xE6359A60;

    public static function google_pr($url) {
        import('lib.file');
        
        $pr = false;
        $str = '';
        $url = 'info:' . $url;
        for ($i = 0; $i < strlen($url); $i ++) {
            $str[$i] = ord($url[$i]);
        }
        $ch = self::google_checksum($str);
        $raw = file::get_remote('http://toolbarqueries.google.com/search?client=navclient-auto&features=Rank&ch=6' . $ch . '&q=' . $url);
        
        $pr = intval(substr(strrchr($raw, ':'), 1));
        return $pr;
    }

    private static function zero_fill($a, $b) {
         $z = hexdec(80000000);
         if ($z & $a) {
             $a >>= 1;
             $a &= ~$z;
             $a |= 0x40000000;
             $a >>= $b - 1;
         } else {
             $a >>= $b;
         }
         return $a;
    }


    private static function google_pr_mix(&$a, &$b, &$c) {
         $a -= $b; $a -= $c; $a ^= self::zero_fill($c, 13);
         $b -= $c; $b -= $a; $b ^= $a << 8;
         $c -= $a; $c -= $b; $c ^= self::zero_fill($b, 13);
         $a -= $b; $a -= $c; $a ^= self::zero_fill($c, 12);
         $b -= $c; $b -= $a; $b ^= $a << 16;
         $c -= $a; $c -= $b; $c ^= self::zero_fill($b, 5);
         $a -= $b; $a -= $c; $a ^= self::zero_fill($c, 3);
         $b -= $c; $b -= $a; $b ^= $a << 10;
         $c -= $a; $c -= $b; $c ^= self::zero_fill($b, 15);

         return array($a, $b, $c);
    }

    private static function google_checksum($url, $length = null, $init = self::GOOGLE_MAGIC) {
        if (is_null($length)) {
            $length = sizeof($url);
        }
        $a = $b = 0x9E3779B9;
        $c = $init;
        $k = 0;
        $len = $length;
        while ($len >= 12) {
            $a += $url[$k + 0] + ($url[$k + 1] << 8) + ($url[$k + 2] << 16) +($url[$k + 3] << 24);
            $b += $url[$k + 4] + ($url[$k + 5] << 8) + ($url[$k + 6] << 16) +($url[$k + 7] << 24);
            $c += $url[$k + 8] + ($url[$k + 9] << 8) + ($url[$k + 10] << 16) + ($url[$k + 11] << 24);
            self::google_pr_mix($a, $b, $c);
            $k += 12;
            $len -= 12;
        }

        $c += $length;
        /* all the case statements fall through */
        switch($len) {
            case 11: $c += $url[$k + 10] << 24;
            case 10: $c += $url[$k + 9] << 16;
            case 9 : $c += $url[$k + 8] << 8;
            /* the first byte of c is reserved for the length */
            case 8 : $b += $url[$k + 7] << 24;
            case 7 : $b += $url[$k + 6] << 16;
            case 6 : $b += $url[$k + 5] << 8;
            case 5 : $b += $url[$k + 4];
            case 4 : $a += $url[$k + 3] << 24;
            case 3 : $a += $url[$k + 2] << 16;
            case 2 : $a += $url[$k + 1] << 8;
            case 1 : $a += $url[$k + 0];
            /* case 0: nothing left to add */
        }
        self::google_pr_mix($a, $b, $c);
        return $c;
    }
    
    public static function yandex_cy($url) {
        import('lib.file');
        import('lib.url');
        
        $cy = false;
        
        if ($xml = file::get_remote('http://bar-navig.yandex.ru/u?ver=2&show=32&url=' . url::add_protocol($url))) {
            $dom = new DOMDocument;
            $dom->loadXML($xml);
            $node = $dom->getElementsByTagName('tcy')->item(0);
            if ($node) {
                $cy = intval($node->getAttribute('value'));
            }
            return $cy;
        } else {
            return false;
        }
    }
}    
?>