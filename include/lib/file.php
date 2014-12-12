<?php
class file {
    public static $mime = array(
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'png'  => 'image/png',
        'zip'  => 'application/zip',
        'tar'  => 'application/tar',
        'tgz'  => 'application/tgz',
    );

    public static function get_name($file)
    {
        if (defined('PATHINFO_FILENAME')) {
            return pathinfo($file, PATHINFO_FILENAME);
        } else {
            return basename($file, ('.' . pathinfo($file, PATHINFO_EXTENSION)));
        }
    }
    
    public static function get_ext($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }
    
    public static function get_line($file, $line)
    {
        if ($f = fopen($file, 'rb')) {
            $n = 1;
            while (!feof($f) && $n < $line) {
                $n ++;
                fgets($f);
            }
            return fgets($f);
        } else {
            return false;
        }
    }
    
    public static function get_size($file) {
        $size = filesize($file);
        $i = 0;
        $iec = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
        while (($size / 1024) >= 1) {
            $size /= 1024;
            $i ++;
        }
        return round($size, 1) . ' ' . $iec[$i];
    }
    
    public static function get_mime($file)
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($file);
        } else {
            $ext = strtolower(file::get_ext($file));
            return array_get(file::$mime, $ext);
        }
    }
    
    public static function last_dir($file)
    {
        return substr(strrchr($file, '/'), 1);
    }

    public static function upload_path($dir, $name)
    {
        $filepath = $dir . file::translit($name);
        if (file_exists($filepath)) {
            $i = 2;
            $filepath_n = $dir . file::get_name($filepath) . '(' . $i . ').' . file::get_ext($filepath);
            while (file_exists($filepath_n)) {
                $i ++;
                $filepath_n = $dir . file::get_name($filepath) . '(' . $i . ').' . file::get_ext($filepath);
            }
            $filepath = $filepath_n;
        }
        return $filepath;
        
    }
    
    public static function translit($str) {
        $table = array(
            '0410' => 'A', '0411' => 'B', '0412' => 'V', '0413' => 'G', '0414' => 'D', '0415' => 'E', '0416' => 'Zh', '0417' => 'Z', '0418' => 'I', '0419' => 'J', '041A' => 'K', '041B' => 'L', '041C' => 'M', '041D' => 'N', '041E' => 'O', '041F' => 'P', '0420' => 'R', '0421' => 'S', '0422' => 'T', '0423' => 'U', '0424' => 'F', '0425' => 'H', '0426' => 'C', '0427' => 'Ch', '0428' => 'Sh', '0429' => 'Sch', '042A' => '`', '042B' => 'Y', '042C' => '`', '042D' => 'E', '042E' => 'Ju', '042F' => 'Ja', '0401' => 'Jo',
            '0430' => 'a', '0431' => 'b', '0432' => 'v', '0433' => 'g', '0434' => 'd', '0435' => 'e', '0436' => 'zh', '0437' => 'z', '0438' => 'i', '0439' => 'j', '043A' => 'k', '043B' => 'l', '043C' => 'm', '043D' => 'n', '043E' => 'o', '043F' => 'p', '0440' => 'r', '0441' => 's', '0442' => 't', '0443' => 'u', '0444' => 'f', '0445' => 'h', '0446' => 'c', '0447' => 'ch', '0448' => 'sh', '0449' => 'sch', '044A' => '`', '044B' => 'y', '044C' => '`', '044D' => 'e', '044E' => 'ju', '044F' => 'ja', '0451' => 'jo',
        );
        
        foreach ($table as $code => $letter) {
            $str = preg_replace('/\x{' . $code . '}/u', $letter, $str);
        }
        
        $str = preg_replace('/[^a-zA-Z0-9\.`_-]/u', '-', $str);
        
        return $str;
    }
    
    public static function get_remote($url, $referer = '') {
        if (function_exists('curl_init')) {
            $c = curl_init();
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_URL, $url);
            if ($referer) {
                curl_setopt($c, CURLOPT_REFERER, $referer);
            }
            $contents = curl_exec($c);
            curl_close($c);
        } else {
            $url = str_replace(' ', '%20', $url);
            $contents = file_get_contents($url);
        }
        return $contents;
    }
    
    public static function download($file, $name = '') {
        if (!file_exists($file)) {
            return false;
        }
        $mime = file::get_mime($file);
        if (!$name) {
            $name = basename($file);
        }
        $size = filesize($file);
    
        $from = $to = 0; 
    
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = substr($_SERVER['HTTP_RANGE'], strpos($_SERVER['HTTP_RANGE'], '=') + 1);
            $from = strtok($range, '-');
            $to = strtok('/');

            if (!$to) {
                $to = $size;
            }
            $size = $to - $from;
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes ' . $from . '-' . $to . '/' . ($to + 1));
        } else {
            header('HTTP/1.1 200 Ok');
        }
    
        header('Accept-Ranges: bytes');
        header('Connection: close');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . $size);
        header('Last-Modified: ' . gmdate('r', filemtime($file)));
        header('Content-Disposition: attachment; filename="' . $name . '";');
        
        $f = fopen($file, 'r');
        fseek($f, $from);
        
        $downloaded = 0;
        while (!feof($f) and !connection_status() and ($downloaded < $size)) {
            echo fread($f, 512000);
            $downloaded += 512000;
            flush();
        }
        fclose($f);
    }
    
    public static function rename($oldname, $newname)
    {
        $Conf = Framework::get('Conf');
        
        if ($Conf->CNF->ftp->use) {
            import('lib.ftp');
            return ftp::rename($oldname, $newname);
        } else {
            return rename($oldname, $newname);
        }
    }

    public static function delete($file)
    {
        $Conf = Framework::get('Conf');
        
        if ($Conf->CNF->ftp->use) {
            import('lib.ftp');
            return ftp::delete($file);
        } else {
            return unlink($file);
        }
    }

    public static function rmdir($dir)
    {
        $Conf = Framework::get('Conf');
        
        if ($Conf->CNF->ftp->use) {
            import('lib.ftp');
            return ftp::rmdir($dir);
        } else {
            $files = glob($dir . '*', GLOB_MARK);
            foreach ($files as $file){
                if (is_dir($file)) {
                    self::rmdir($file);
                } else {
                    self::delete($file);
                }
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
            return rmdir($dir);
        }
    }

    public static function mkdir($dir, $mode = null)
    {
        $Conf = Framework::get('Conf');
        
        if ($Conf->CNF->ftp->use) {
            import('lib.ftp');
            $created = ftp::mkdir($dir);
            if ($created && isset($mode)) {
                ftp::chmod($dir, $mode);
            }
            return $created;
        } else {
            return mkdir($dir, $mode);
        }
    }
    
    public static function put_contents($file, $content)
    {
        $Conf = Framework::get('Conf');
        
        if ($Conf->CNF->ftp->use) {
            import('lib.ftp');
            $temp = fopen('php://temp', 'r+');
            fwrite($temp, $content);
            rewind($temp);
            $result = ftp::fput($file, $temp);
            fclose($temp);
            return $result;
        } else {
            return file_put_contents($file, $content);
        }
    }
    
    public static function get_contents($file)
    {
        return file_get_contents($file);
    } 

    public static function chmod($file, $mode)
    {
        $Conf = Framework::get('Conf');
        
        if ($Conf->CNF->ftp->use) {
            import('lib.ftp');
            return ftp::chmod($file, $mode);
        } else {
            return chmod($file, $mode);
        }
    }
    
    public static function move_uploaded_file($source, $destination)
    {
        $Conf = Framework::get('Conf');
        
        if ($Conf->CNF->ftp->use) {
            import('lib.ftp');
            $temp = fopen($source, 'r+');
            rewind($temp);
            $result = ftp::fput($destination, $temp);
            fclose($temp);
            return $result;
        } else {
            return move_uploaded_file($source, $destination);
        }
    }
}
?>